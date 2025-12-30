<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Support\Facades\Log;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTableRecords(): Paginator
    {
        try {
            // Fetch all brands from Sushi model
            $brands = \App\Models\Brand::all();

            Log::info('Fetched brands from Sushi', ['count' => count($brands)]);

            $page = max(1, (int) request()->query('page', 1));
            $perPage = 15;

            // Manually paginate the collection
            return new BasePaginator(
                $brands->forPage($page, $perPage)->values()->all(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch brands: ' . $e->getMessage());
            return new BasePaginator([], 15, 1);
        }
    }
}
