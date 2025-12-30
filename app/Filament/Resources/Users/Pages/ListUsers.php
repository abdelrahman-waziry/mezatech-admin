<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Customer;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected ?Collection $customerCache = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return null;
    }

    public function getTableRecords(): Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        return $this->customerRecords();
    }

    protected function customerRecords(): Collection
    {
        if ($this->customerCache) {
            return $this->customerCache;
        }

        return $this->customerCache = Customer::all();
    }
}
