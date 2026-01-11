<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopEndpointsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AnalyticsRequest::query()
                    ->select('endpoint', DB::raw('COUNT(*) as count'), DB::raw('AVG(duration_ms) as avg_duration'))
                    ->groupBy('endpoint')
                    ->orderByDesc('count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Endpoint'),
                Tables\Columns\TextColumn::make('count')
                    ->label('Requests')
                    ->numeric(),
                Tables\Columns\TextColumn::make('avg_duration')
                    ->label('Avg Duration (ms)')
                    ->numeric(decimalPlaces: 2),
            ])
            ->paginated(false)
            ->recordKey('endpoint');
    }
}
