<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryType: string
{
    case Matc = 'matc';
    case Amk = 'amk';
    case Wanita = 'wanita';
    case Mpkk = 'mpkk';

    public function label(): string
    {
        return match ($this) {
            self::Matc => 'Cabang',
            self::Amk => 'AMK',
            self::Wanita => 'Wanita Cabang',
            self::Mpkk => 'MPKK',
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::Matc => 'matc',
            self::Amk => 'amk',
            self::Wanita => 'wanita',
            self::Mpkk => 'mpkk',
        };
    }

    public static function fromSlug(string $slug): self
    {
        return match ($slug) {
            'matc' => self::Matc,
            'amk' => self::Amk,
            'wanita' => self::Wanita,
            'mpkk' => self::Mpkk,
            default => throw new \ValueError("Invalid category slug: {$slug}"),
        };
    }

    /**
     * @return array<string>
     */
    public function positions(): array
    {
        return match ($this) {
            self::Matc => [
                'Ketua Cabang',
                'Timbalan Ketua Cabang',
                'Naib Ketua Cabang',
                'Setiausaha',
                'Setiausaha Pengelola',
                'Bendahari',
                'Ketua Penerangan / Komunikasi',
                'AJK',
            ],
            self::Amk => [
                'Ketua AMK',
                'Timbalan Ketua AMK',
                'Naib Ketua AMK',
                'Setiausaha',
                'Bendahari',
                'Ketua Penerangan',
                'AJK',
            ],
            self::Wanita => [
                'Ketua Wanita',
                'Timbalan Ketua Wanita',
                'Naib Ketua Wanita',
                'Setiausaha',
                'Bendahari',
                'Ketua Penerangan',
                'AJK',
            ],
            self::Mpkk => [
                'Pengerusi',
                'Timbalan Pengerusi',
                'Setiausaha',
                'Bendahari',
                'AJK',
            ],
        };
    }

    /**
     * @return array<string>
     */
    public static function allPositions(): array
    {
        return array_values(array_unique(array_merge(
            self::Matc->positions(),
            self::Amk->positions(),
            self::Wanita->positions(),
            self::Mpkk->positions(),
        )));
    }
}
