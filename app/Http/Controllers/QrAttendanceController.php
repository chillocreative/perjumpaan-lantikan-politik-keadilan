<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\CategoryType;
use App\Models\Meeting;
use App\Rules\MalaysianIc;
use App\Services\AttendanceService;
use App\Services\BruteForceProtection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly BruteForceProtection $bruteForce,
    ) {}

    public function showQr(): Response
    {
        $url = config('app.url');

        $qrCode = (string) QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($url);

        return Inertia::render('QrAttendance/Show', [
            'qrCode' => $qrCode,
            'url' => $url,
        ]);
    }

    public function publicForm(Request $request, string $category): Response
    {
        $categoryEnum = CategoryType::fromSlug($category);
        $meeting = Meeting::latest('date')->first();

        return Inertia::render('QrAttendance/PublicForm', [
            'category' => $categoryEnum->value,
            'categoryLabel' => $categoryEnum->label(),
            'meeting' => $meeting,
            'attendances' => $meeting ? $this->attendanceService->getByMeeting($meeting->id) : [],
            'verifyUrl' => route('qr.'.$categoryEnum->slug().'.verify'),
            'formToken' => Crypt::encryptString((string) now()->timestamp),
        ]);
    }

    public function publicVerify(Request $request, string $category): JsonResponse|RedirectResponse
    {
        $categoryEnum = CategoryType::fromSlug($category);
        $meeting = Meeting::latest('date')->firstOrFail();

        return $this->processVerify($request, $categoryEnum, $meeting);
    }

    public function hadir(Request $request, string $category, Meeting $meeting): Response
    {
        $categoryEnum = CategoryType::fromSlug($category);

        $attendances = $this->attendanceService->getByMeeting($meeting->id);

        return Inertia::render('QrAttendance/Hadir', [
            'category' => $categoryEnum->value,
            'categoryLabel' => $categoryEnum->label(),
            'meeting' => $meeting,
            'attendances' => $attendances,
            'verifyUrl' => $request->fullUrl(),
            'formToken' => Crypt::encryptString((string) now()->timestamp),
        ]);
    }

    public function verify(Request $request, string $category, Meeting $meeting): JsonResponse|RedirectResponse
    {
        $categoryEnum = CategoryType::fromSlug($category);

        return $this->processVerify($request, $categoryEnum, $meeting);
    }

    private function processVerify(Request $request, CategoryType $categoryEnum, Meeting $meeting): JsonResponse|RedirectResponse
    {
        $ip = $request->ip();

        if ($this->bruteForce->isBlocked($ip)) {
            $seconds = $this->bruteForce->remainingSeconds($ip);
            $minutes = (int) ceil($seconds / 60);

            return $this->errorResponse($request, "Terlalu banyak percubaan gagal. Sila cuba selepas {$minutes} minit.", 429);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ic_number' => ['required', 'string', 'max:20', new MalaysianIc],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'mpkk_name' => [$categoryEnum === CategoryType::Mpkk ? 'required' : 'nullable', 'string', 'max:255'],
            'position_type' => ['required', 'string', Rule::in($categoryEnum->positions())],
            'status' => ['required', 'string', 'in:hadir,tidak_hadir'],
            'absence_reason' => ['nullable', 'string', 'max:500', 'required_if:status,tidak_hadir'],
        ]);

        $icNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);
        $status = $validated['status'] === 'hadir' ? AttendanceStatus::Present : AttendanceStatus::Absent;

        try {
            $attendance = $this->attendanceService->publicVerifyByIc(
                $meeting->id,
                $icNumber,
                $categoryEnum,
                [
                    'name' => $validated['name'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'position_type' => $validated['position_type'] ?? null,
                    'position_name' => $validated['mpkk_name'] ?? null,
                ],
                $status,
                $validated['absence_reason'] ?? null,
            );

            $this->bruteForce->clearAttempts($ip);

            $memberName = $attendance->member->name ?? 'Ahli';

            return $this->successResponse($request, "Kehadiran {$memberName} berjaya disahkan.");
        } catch (\DomainException $e) {
            $this->bruteForce->recordFailedAttempt($ip);

            return $this->errorResponse($request, $e->getMessage(), 422);
        }
    }

    private function successResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function errorResponse(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}
