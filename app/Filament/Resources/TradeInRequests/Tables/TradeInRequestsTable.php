<?php

namespace App\Filament\Resources\TradeInRequests\Tables;

use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                        'confirmed' => 'info',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'confirmed' => 'Confirmed',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\Action::make('view_comment')
                        ->label('View Comment')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('admin_comment')
                                ->label('Admin Comment')
                                ->default(fn ($record) => $record->admin_comment)
                                ->readOnly()
                                ->rows(5),
                        ])
                        ->modalHeading('Admin Comment')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn ($action) => $action->label('Close')),
                    \Filament\Actions\Action::make('download_report')
                        ->label('Report')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($record) {
                            return response()->streamDownload(function () use ($record) {
                                $report = \App\Filament\Resources\TradeInRequests\Schemas\TradeInRequestForm::getReportContent($record);
                                $csv = fopen('php://output', 'w');
                                
                                // BOM for Excel compatibility
                                fputs($csv, "\xEF\xBB\xBF");

                                // Headers
                                fputcsv($csv, ['Category', 'Item', 'Value', 'Status/Impact']);

                                // Customer Info
                                fputcsv($csv, ['Customer Info', 'Name', $record->customer_name, '']);
                                fputcsv($csv, ['Customer Info', 'Email', $record->customer_email, '']);
                                fputcsv($csv, ['Customer Info', 'Phone', $record->customer_phone, '']);
                                fputcsv($csv, ['Customer Info', 'Quote', $record->trade_in_quote, '']);
                                fputcsv($csv, ['', '', '', '']);

                                // Simplified Report
                                if (!empty($report['simplified_report'])) {
                                    foreach ($report['simplified_report'] as $item) {
                                        fputcsv($csv, [
                                            'Questionnaire',
                                            $item['question'],
                                            $item['answer'],
                                            $item['is_flagged'] ? 'Flagged' : 'OK'
                                        ]);
                                    }
                                    fputcsv($csv, ['', '', '', '']);
                                }

                                // Parts Report
                                if (!empty($report['parts_report'])) {
                                    foreach ($report['parts_report'] as $item) {
                                        fputcsv($csv, [
                                            'Part Check',
                                            $item['part_name'],
                                            $item['condition_name'],
                                            $item['input_price_impact']
                                        ]);
                                    }
                                }
                                
                                fclose($csv);
                            }, 'trade-in-report-' . $record->id . '.csv');
                        }),
                ]),
            ])
            ->recordAction('view_comment')
            ->toolbarActions([
                BulkAction::make('bulk_delete')
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Selected Trade-In Requests')
                    ->modalDescription('Are you sure you want to delete all selected trade-in requests? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete them')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        $successCount = 0;
                        $failedCount = 0;
                        $errors = [];

                        foreach ($records as $record) {
                            try {
                                $record->delete();
                                $successCount++;
                            } catch (\Exception $e) {
                                $failedCount++;
                                $errors[] = "ID {$record->id}: {$e->getMessage()}";
                                \Illuminate\Support\Facades\Log::error("Bulk delete failed for trade-in request {$record->id}", [
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }

                        // Build result notification
                        if ($failedCount === 0) {
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Trade-In Requests Deleted')
                                ->body("{$successCount} trade-in request(s) deleted successfully.")
                                ->send();
                        } elseif ($successCount === 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Bulk Delete Failed')
                                ->body("Failed to delete all {$failedCount} trade-in request(s).\n" . implode("\n", array_slice($errors, 0, 5)))
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Partially Deleted')
                                ->body("{$successCount} deleted, {$failedCount} failed.\n" . implode("\n", array_slice($errors, 0, 5)))
                                ->send();
                        }

                        // Redirect to refresh the table
                        return redirect(request()->header('Referer'));
                    }),
            ]);
    }
}

