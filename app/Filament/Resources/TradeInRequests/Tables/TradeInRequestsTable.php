<?php

namespace App\Filament\Resources\TradeInRequests\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TradeInRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('variant_id')
                    ->label('Variant Name')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        return \Illuminate\Support\Facades\Cache::remember('variant_name_' . $state, 3600, function () use ($state) {
                            try {
                                $token = app(\App\Services\ApiTokenService::class)->getToken();
                                $response = \Illuminate\Support\Facades\Http::withHeaders([
                                    'Authorization' => 'Bearer ' . $token,
                                    'Accept' => 'application/json',
                                ])->timeout(2)->get('https://bestrepairegypt.com/v1/variants/' . $state);

                                if ($response->successful()) {
                                    return $response->json()['name'] ?? 'Unknown ' . $state;
                                }
                            } catch (\Exception $e) {
                                // Fallback
                            }
                            return 'ID: ' . $state;
                        });
                    }),
                TextColumn::make('trade_in_quote')
                    ->label('Quote')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('view_comment')
                    ->label('View Comment')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('admin_comment')
                            ->label('Admin Comment')
                            ->readOnly()
                            ->rows(5),
                    ])
                    ->modalHeading('Admin Comment')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('Close')),
            ])
            ->recordAction('view_comment')
            ->toolbarActions([]);
    }
}

