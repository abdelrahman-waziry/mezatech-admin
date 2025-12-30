<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ListParts extends ListRecords
{
    protected static string $resource = PartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTableRecords(): Collection|Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        $productId = $this->resolveProductFilterId();

        if (!$productId) {
            return new Collection([]);
        }

        try {
            Part::$currentProductId = (int) $productId;
            
            // Get fresh data by calling getRows on a new instance
            $freshModel = new Part();
            $rows = $freshModel->getRows();
            
            Log::info('Fetched parts rows from getRows', ['count' => count($rows), 'productId' => $productId, 'first_row' => $rows[0] ?? null]);
            
            // Create model instances with proper attribute setup
            $parts = collect($rows)->map(function ($row) use ($productId) {
                Part::$currentProductId = $productId;
                
                $part = new Part();
                $part->setRawAttributes($row);
                $part->exists = true;
                $part->wasRecentlyCreated = false;
                
                return $part;
            });
            
            Log::info('Created parts collection', ['count' => $parts->count(), 'productId' => $productId]);
            
            $page = max(1, (int) request()->query('page', 1));
            $perPage = 15;
            $total = $parts->count();
            $items = $parts->forPage($page, $perPage)->values()->all();
            
            return new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ],
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch parts', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return new Collection([]);
        }
    }

    protected function resolveProductFilterId(): ?int
    {
        $filterState = $this->getTableFilterState('product_id') ?? [];

        $value = $filterState['value'] ?? null;

        if (! blank($value)) {
            return (int) $value;
        }

        $values = $filterState['values'] ?? null;

        if (is_array($values)) {
            foreach ($values as $candidate) {
                if (! blank($candidate)) {
                    return (int) $candidate;
                }
            }
        }

        return $this->resolveProductFilterIdFromRequest();
    }

    protected function resolveProductFilterIdFromRequest(): ?int
    {
        $productId = request()->query('tableFilters.product_id.value')
            ?? request()->query('filters.product_id')
            ?? request()->input('tableFilters.product_id.value')
            ?? null;

        if (! $productId) {
            $productId = data_get(request()->input('tableFilters', []), 'product_id.value');
        }

        if (! $productId) {
            $productId = request()->input('filters.product_id');
        }

        return $productId ? (int) $productId : null;
    }
}
