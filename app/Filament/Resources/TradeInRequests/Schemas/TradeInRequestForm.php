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
                \Filament\Schemas\Components\Section::make('Evaluation Details')
                    ->columnSpan('full')
                    ->columns(1)
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('report')
                            ->label('Detailed Report')
                            ->content(function ($record) {
                                return view('filament.forms.components.trade-in-report', [
                                    'report' => self::getReportContent($record)
                                ]);
                            }),
                    ]),
                // \Filament\Schemas\Components\Section::make('Debug Data')
                //     ->collapsed()
                //     ->collapsible()
                //     ->schema([
                //         \Filament\Forms\Components\KeyValue::make('selected_options_display') // Changed name to avoid model binding
                //             ->label('Raw Part Options')
                //             ->disabled()
                //             ->dehydrated(false)
                //             ->afterStateHydrated(function ($component, $record) {
                //                 if (!$record) return;
                //                 $report = self::getReportContent($record);
                //                 $readable = [];
                //                 if (!empty($report['parts_report'])) {
                //                     foreach ($report['parts_report'] as $item) {
                //                         $readable[$item['part_name']] = $item['condition_name'];
                //                     }
                //                 }
                //                 $component->state($readable); // Always set state, even if empty
                //             }),
                //         \Filament\Forms\Components\KeyValue::make('customer_answers_display') // Changed name to avoid model binding
                //             ->label('Raw Questionnaire')
                //             ->disabled()
                //             ->dehydrated(false)
                //             ->afterStateHydrated(function ($component, $record) {
                //                 if (!$record) return;
                //                 $report = self::getReportContent($record);
                //                 $readable = [];
                //                 if (!empty($report['simplified_report'])) {
                //                     foreach ($report['simplified_report'] as $item) {
                //                         $readable[$item['question']] = $item['answer'];
                //                     }
                //                 }
                //                 $component->state($readable); // Always set state
                //             }),
                //     ]),
            ]);
    }

    public static function getReportContent($record): array
    {
        if (!$record || (empty($record->selected_options) && empty($record->customer_answers)) || !$record->variant_id) {
            return [];
        }

       
        try {

            $productId = $record->product_id;

            if (!$productId) {
                return [];
            }

            // Set static context for Part model Sushi cache/fetch
            \App\Models\Part::$currentProductId = $productId;
            $parts = \App\Models\Part::all();
            $conditions = \App\Models\Condition::all();

            $report = [];
            // Fallback: If Part model fails to return parts (e.g. Sushi issue), try direct API call
            $partsMap = [];
            foreach ($parts as $p) {
                $partsMap[$p->id] = [
                    'name' => $p->name,
                    'price' => $p->price ?? 0,
                ];
            }

            // If we have selected options but no matching parts found, try fetching directly
            $missingParts = false;
            foreach (array_keys($record->selected_options) as $partId) {
                if (!isset($partsMap[$partId])) {
                    $missingParts = true;
                    break;
                }
            }

            if ($missingParts) {
                 try {
                    $partsResponse = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->get('https://bestrepairegypt.com/v1/parts', ['productId' => $productId]);
                    
                    if ($partsResponse->successful()) {
                        $partsData = $partsResponse->json();
                        // Handle different response structures
                        $rawParts = $partsData['parts'] ?? $partsData['data'] ?? $partsData['items'] ?? $partsData;
                        if (is_array($rawParts)) {
                            foreach ($rawParts as $rp) {
                                if (isset($rp['id']) && isset($rp['name'])) {
                                    $partsMap[$rp['id']] = [
                                        'name' => $rp['name'],
                                        'price' => $rp['price'] ?? 0,
                                    ];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently, use IDs
                }
            }

            $report = [];
            $index = 0;
            foreach ($record->selected_options as $partId => $conditionId) {
                $index++;
                $partData = $partsMap[$partId] ?? ['name' => "Part #$partId", 'price' => 0];
                $partName = $partData['name'];
                $partPrice = (float)$partData['price'];
                
                
                $condition = $conditions->firstWhere('id', $conditionId);

                $conditionName = $condition ? $condition->name : "Condition #$conditionId";

                // Specific Part Overrides using 1-based index
                if ($index == 2) {
                     $conditionName = (int)$conditionId === 1 ? 'Yes' : 'No';
                } elseif ($index == 4 || $index == 5) {
                    if ((int)$conditionId === 0) {
                        $conditionName = 'No Scratches';
                    } elseif ((int)$conditionId === 1) {
                        $conditionName = 'Scratched';
                    }
                } elseif ($conditionId == 1) {
                    $conditionName = 'Functioning';
                } elseif ($conditionId == 0) {
                    $conditionName = 'Not Functioning';
                } elseif (is_numeric($conditionId) && $conditionId >= 10 && $conditionId <= 100) {
                    $conditionName = $conditionId . '%';
                }

                $priceModifier = $condition ? (float) $condition->price_modifier : 1.0;
                $impactLabel = '-';
                
                // If part is reported as Not Functioning (0) and it's not one of the exclude parts (2, 4, 5)
                // Then the impact is deducting the full price of the part
                if ((int)$conditionId === 0 && !in_array($index, [2, 4, 5]) && $partPrice > 0) {
                     $impactLabel = '- ' . number_format($partPrice, 2) . ' EGP';
                     // Force modifier to 0 to separate it from percentage based modifiers in sorting if needed, 
                     // or keep as 1.0 if we don't want it to affect the sorting logic which relies on < 1.0
                     // For now, let's leave priceModifier as is or set to unique value if sorting matters.
                } elseif ($priceModifier < 1.0) {
                    $percentage = (1.0 - $priceModifier) * 100;
                    $impactLabel = '-' . round($percentage) . '%';
                }

                $report[] = [
                    'part_name' => $partName,
                    'condition_name' => $conditionName,
                    'price_modifier' => $priceModifier,
                    'input_price_impact' => $impactLabel,
                ];
            }
            
            
            // Sort by price modifier (ascending) so lower modifiers (price reductions) appear first
            usort($report, function ($a, $b) {
                if ($a['price_modifier'] == $b['price_modifier']) {
                    return 0;
                }
                return ($a['price_modifier'] < $b['price_modifier']) ? -1 : 1;
            });

            return [
                'parts_report' => $report,
                'simplified_report' => self::getSimplifiedReport($record),
            ];
        } catch (\Exception $e) {
            return [
                'parts_report' => [],
                'simplified_report' => [],
            ];
        }
    }

    protected static function getSimplifiedReport($record): array
    {
        if (!$record || empty($record->customer_answers)) {
            return [];
        }

        $questions = [
            1 => [
                'question' => "Is your device functional and able to turn on?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ],
            2 => [
                'question' => "Has your device been repaired before?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ],
            3 => [
                'question' => "What is your device's battery health percentage?",
                'type' => "battery",
                'options' => [98 => "Above 90%", 86 => "85-90%", 81 => "80-84%", 79 => "Below 80%"]
            ],
            4 => [
                'question' => "Are there any scratches on the phone?",
                'type' => "condition",
                'options' => [0 => "No scratches", 1 => "Slight scratches", 2 => "Heavy scratches"]
            ],
            5 => [
                'question' => "Are the Charging Port, Speakers, and Buttons Working?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ],
            6 => [
                'question' => "Are the Front and Back Cameras working?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ],
            7 => [
                'question' => "Does Network, Wifi, Bluetooth, NFC, and GPS working?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ],
            8 => [
                'question' => "Is Face ID or Finger Print working?",
                'type' => "yesno",
                'options' => [1 => "Yes", 0 => "No"]
            ]
        ];

        $report = [];
        foreach ($record->customer_answers as $qId => $answer) {
            $qIdInt = (int)$qId;
            $answerInt = (int)$answer;

            if (isset($questions[$qIdInt])) {
                $q = $questions[$qIdInt];
                $answerText = $q['options'][$answerInt] ?? $answer;
                
                $report[] = [
                    'question' => $q['question'],
                    'answer' => $answerText,
                    'is_flagged' => self::isAnswerFlagged($qIdInt, $answerInt),
                ];
            }
        }

        return $report;
    }

    protected static function isAnswerFlagged($qId, $answer): bool
    {
        // Define what constitutes a "flagged" or negative answer
        $flags = [
            1 => 0, // Not functional
            2 => 1, // Repaired before
            3 => [86, 81, 79], // Battery < 90%
            4 => [1, 2], // Scratches exist
            5 => 0, // Components not working
            6 => 0, // Cameras not working
            7 => 0, // Connectivity not working
            8 => 0, // Biometrics not working
        ];

        if (!isset($flags[$qId])) return false;

        $flagVal = $flags[$qId];
        if (is_array($flagVal)) {
            return in_array($answer, $flagVal);
        }
        return $answer == $flagVal;
    }

}
