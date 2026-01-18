<?php

namespace App\Filament\Resources\TradeInRequests\Schemas;

use Filament\Schemas\Schema;

class TradeInRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Request Details')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('variant_id')
                            ->label('Variant ID')
                            ->readOnly()
                            ->disabled()
                            ->hidden(),
                        \Filament\Forms\Components\Placeholder::make('variant_name')
                            ->label('Variant Name')
                            ->content(function ($record) {
                                if (!$record || !$record->variant_id) {
                                    return '-';
                                }
                                try {
                                    $token = app(\App\Services\ApiTokenService::class)->getToken();
                                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                                        'Authorization' => 'Bearer ' . $token,
                                        'Accept' => 'application/json',
                                    ])->timeout(5)->get('https://bestrepairegypt.com/v1/variants/' . $record->variant_id);

                                    if ($response->successful()) {
                                        $data = $response->json();
                                        return $data['name'] ?? 'Unknown';
                                    }
                                } catch (\Exception $e) {
                                    return 'Error loading name';
                                }
                                return 'Not Found';
                            }),
                        \Filament\Forms\Components\TextInput::make('trade_in_quote')
                            ->label('Quote')
                            ->prefix('EGP')
                            ->readOnly()
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->readOnly()
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->readOnly()
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('customer_phone')
                            ->label('Phone')
                            ->tel()
                            ->readOnly()
                            ->disabled(),
                        \Filament\Forms\Components\DateTimePicker::make('created_at')
                            ->label('Requested At')
                            ->readOnly()
                            ->disabled(),
                    ]),
                \Filament\Schemas\Components\Section::make('Admin Action')
                    ->columns(1)
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('admin_comment')
                            ->label('Admin Comment')
                            ->rows(3),
                    ]),
            ]);
    }
}

