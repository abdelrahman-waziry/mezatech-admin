<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use App\Models\Condition;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Support\Facades\Log;

class ListConditions extends ListRecords
{
    protected static string $resource = ConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTableRecords(): Paginator
    {
        try {
            $conditions = Condition::all();
            $page = max(1, (int) request()->query('page', 1));
            $perPage = 15;

            return new BasePaginator(
                $conditions->forPage($page, $perPage)->values()->all(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ],
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch conditions: ' . $e->getMessage());
            return new BasePaginator([], 15, 1);
        }
    }
}
