<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;

interface MemberRepositoryInterface extends RepositoryInterface
{
    public function search(string $query, int $perPage = 15, ?string $category = null, ?string $mpkk = null): LengthAwarePaginator;

    public function paginateByCategory(string $category, int $perPage = 15, ?string $mpkk = null): LengthAwarePaginator;

    public function getMpkkList(): array;

    public function findByIdForUpdate(int $id): Member;

    public function findByIcHashForUpdate(string $icHash): Member;

    public function findByIcHashForUpdateOrNull(string $icHash): ?Member;

    public function findByIcHashAndCategoryOrNull(string $icHash, string $category): ?Member;
}
