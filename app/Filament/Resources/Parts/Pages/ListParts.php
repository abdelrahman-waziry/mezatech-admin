<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

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

        return Part::where('product_id', $productId)->paginate(15);
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
