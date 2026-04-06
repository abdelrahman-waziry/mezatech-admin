<?php

namespace App\Filament\Pages;

use App\Models\Accessory;
use App\Models\AccessoryCategory;
use App\Models\RepairCategory;
use App\Models\RepairPrice;
use App\Models\RepairSubcategory;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class PriceList extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected string $view = 'filament.pages.price-list';
    protected static string|\UnitEnum|null $navigationGroup = 'FixTech Pricing';
    protected static ?string $title = 'Price Navigator';
    protected static ?string $navigationLabel = 'Price Navigator';

    #[Url]
    public string $activeTab = 'repairs';

    #[Url]
    public string $search = '';

    // Repair State
    #[Url]
    public ?int $repairCategoryId = null;
    #[Url]
    public ?int $repairSubcategoryId = null;

    // Accessory State
    #[Url]
    public ?int $accessoryCategoryId = null;
    #[Url]
    public ?string $accessoryBrand = null;

    protected $queryString = [
        'activeTab' => ['except' => 'repairs'],
        'search' => ['except' => ''],
        'repairCategoryId' => ['except' => null],
        'repairSubcategoryId' => ['except' => null],
        'accessoryCategoryId' => ['except' => null],
        'accessoryBrand' => ['except' => null],
    ];

    public function mount(): void
    {
    }

    // -------------------------------------------------------------------------
    // COMPUTED DATA
    // -------------------------------------------------------------------------

    public function getRepairCategoriesProperty(): Collection
    {
        return RepairCategory::orderBy('name')->get();
    }

    public function getRepairSubcategoriesProperty(): Collection
    {
        if (!$this->repairCategoryId) return collect();
        return RepairSubcategory::where('repair_category_id', $this->repairCategoryId)
            ->orderBy('name')
            ->get();
    }

    public function getAccessoriesCategoriesProperty(): Collection
    {
        return AccessoryCategory::orderBy('name')->get();
    }

    public function getAccessoryBrandsProperty(): Collection
    {
        if (!$this->accessoryCategoryId) return collect();
        return Accessory::where('accessory_category_id', $this->accessoryCategoryId)
            ->whereNotNull('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
    }

    public function getFilteredItemsProperty(): Collection
    {
        if ($this->activeTab === 'repairs') {
            $query = RepairPrice::with(['subcategory.category'])->orderBy('model');

            if ($this->repairCategoryId) {
                $query->whereHas('subcategory', fn($q) => $q->where('repair_category_id', $this->repairCategoryId));
            }

            if ($this->repairSubcategoryId) {
                $query->where('repair_subcategory_id', $this->repairSubcategoryId);
            }

            if ($this->search) {
                $q = '%' . $this->search . '%';
                $query->where(fn($sub) => $sub->where('model', 'like', $q)
                    ->orWhere('product_number', 'like', $q)
                    ->orWhereHas('subcategory', fn($sq) => $sq->where('name', 'like', $q))
                );
            }

            return $query->get();
        } else {
            $query = Accessory::with('category')->orderBy('name');

            if ($this->accessoryCategoryId) {
                $query->where('accessory_category_id', $this->accessoryCategoryId);
            }

            if ($this->accessoryBrand) {
                $query->where('brand', $this->accessoryBrand);
            }

            if ($this->search) {
                $q = '%' . $this->search . '%';
                $query->where(fn($sub) => $sub->where('name', 'like', $q)
                    ->orWhere('brand', 'like', $q)
                    ->orWhere('item_code', 'like', $q)
                    ->orWhere('notes', 'like', $q)
                );
            }

            return $query->get();
        }
    }

    // -------------------------------------------------------------------------
    // ACTIONS
    // -------------------------------------------------------------------------

    public function selectRepairCategory(?int $id): void
    {
        $this->repairCategoryId = $id;
        $this->repairSubcategoryId = null;
    }

    public function selectRepairSubcategory(?int $id): void
    {
        $this->repairSubcategoryId = $id;
    }

    public function selectAccessoryCategory(?int $id): void
    {
        $this->accessoryCategoryId = $id;
        $this->accessoryBrand = null;
    }

    public function selectAccessoryBrand(?string $brand): void
    {
        $this->accessoryBrand = $brand;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->repairCategoryId = null;
        $this->repairSubcategoryId = null;
        $this->accessoryCategoryId = null;
        $this->accessoryBrand = null;
        $this->search = '';
    }

    public function updatedSearch(): void
    {
        // Reset sub-filters if searching globally? 
        // Better to keep them for faceted search.
    }
}
