<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;

class FixtechSyncProgress extends Widget
{
    protected static ?int $sort = -100;
    
    protected string $view = 'filament.widgets.fixtech-sync-progress';

    protected int | string | array $columnSpan = 'full';

    #[Computed]
    public function progress()
    {
        return Cache::get('fixtech_sync_progress');
    }
}
