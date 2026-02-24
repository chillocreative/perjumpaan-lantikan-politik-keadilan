<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\CategoryType;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\Member;
use App\Services\MeetingService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly MeetingService $meetingService,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'matc' => $this->countByCategory(CategoryType::Matc),
                'amk' => $this->countByCategory(CategoryType::Amk),
                'wanita' => $this->countByCategory(CategoryType::Wanita),
                'mpkk' => $this->countByCategory(CategoryType::Mpkk),
            ],
            'mpkk_attendance' => $this->mpkkAttendance(),
            'upcoming_meetings' => $this->meetingService->upcoming(5),
        ]);
    }

    private function countByCategory(CategoryType $category): array
    {
        $base = Attendance::whereHas('member', fn ($q) => $q->where('category_type', $category->value));

        return [
            'hadir' => (clone $base)->where('status', AttendanceStatus::Present->value)->count(),
            'tidak_hadir' => (clone $base)->where('status', AttendanceStatus::Absent->value)->count(),
        ];
    }

    private function mpkkAttendance(): array
    {
        $meeting = Meeting::latest('date')->first();

        if (! $meeting) {
            return [];
        }

        $mpkkList = [
            'MPKK KG SELAMAT SELATAN',
            'MPKK KG SELAMAT RANGKAIAN',
            'MPKK KG BAHARU',
            'MPKK PINANG TUNGGAL',
            'MPKK PERMATANG LANGSAT',
            'MPKK PAYA KELADI',
            'MPKK PERMATANG TINGGI B',
            'MPKK BERTAM INDAH',
            'MPKK KG TOK BEDU',
            'MPKK KUBANG MENERONG',
            'MPKK KG DATUK',
            'MPKK PERMATANG SINTOK',
            'MPKK PERMATANG RAMBAI',
            'MPKK JALAN KEDAH',
            'MPKK PADANG BENGGALI',
            'MPKK DUN PENAGA',
        ];

        $totals = [
            'MPKK KG SELAMAT SELATAN' => 9,
            'MPKK KG SELAMAT RANGKAIAN' => 15,
            'MPKK KG BAHARU' => 15,
            'MPKK PINANG TUNGGAL' => 2,
            'MPKK PERMATANG LANGSAT' => 13,
            'MPKK PAYA KELADI' => 12,
            'MPKK PERMATANG TINGGI B' => 12,
            'MPKK BERTAM INDAH' => 12,
            'MPKK KG TOK BEDU' => 13,
            'MPKK KUBANG MENERONG' => 14,
            'MPKK KG DATUK' => 15,
            'MPKK PERMATANG SINTOK' => 11,
            'MPKK PERMATANG RAMBAI' => 15,
            'MPKK JALAN KEDAH' => 11,
            'MPKK PADANG BENGGALI' => 12,
            'MPKK DUN PENAGA' => 14,
        ];

        $counts = Attendance::where('attendances.meeting_id', $meeting->id)
            ->where('attendances.category_type', 'mpkk')
            ->where('attendances.status', AttendanceStatus::Present->value)
            ->join('members', 'attendances.member_id', '=', 'members.id')
            ->whereIn('members.position_name', $mpkkList)
            ->selectRaw('members.position_name, count(*) as total')
            ->groupBy('members.position_name')
            ->pluck('total', 'position_name')
            ->toArray();

        $result = [];

        foreach ($mpkkList as $name) {
            $result[] = [
                'name' => $name,
                'hadir' => $counts[$name] ?? 0,
                'total' => $totals[$name],
            ];
        }

        return $result;
    }
}
