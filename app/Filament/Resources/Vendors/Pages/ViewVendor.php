<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewVendor extends ViewRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateEmbedKey')
                ->label(fn ($record): string => $record->embed_key ? 'Rotate embed key' : 'Generate embed key')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Generating a new key immediately invalidates the current key.')
                ->form([
                    TextInput::make('allowed_origins')
                        ->label('Allowed origins')
                        ->placeholder('store.example.com, www.store.example.com')
                        ->helperText('Optional. Enter hostnames separated by commas. Leave empty to allow any origin.'),
                ])
                ->action(function ($record, array $data): void {
                    $key = 'pk_live_'.Str::random(32);
                    $origins = collect(explode(',', (string) ($data['allowed_origins'] ?? '')))
                        ->map(fn (string $origin): string => trim($origin))
                        ->filter()
                        ->values()
                        ->all();

                    $record->forceFill([
                        'embed_key' => $key,
                        'allowed_origins' => $origins ?: null,
                    ])->save();

                    $apiBase = config('app.url').'/api/v1/vendor/public';
                    $snippet = sprintf(
                        '<trade-in-calculator api-base="%s" client-id="%s"></trade-in-calculator>\n<script src="%s/vendor/trade-in-widget.js" defer></script>',
                        $apiBase,
                        $key,
                        config('app.url')
                    );

                    Notification::make()
                        ->title('Embed key generated')
                        ->body("Copy this key now; it will not be shown again.\n\n{$key}\n\n{$snippet}")
                        ->success()
                        ->persistent()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
