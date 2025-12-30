<?php

namespace App\Filament\Resources\Features\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Select the product this feature belongs to')
                    ->dehydrated()
                    ->native(false),

                TextInput::make('name')
                    ->label('Feature Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The name of the feature (e.g., "Color", "Size", "Storage")'),

                Repeater::make('values')
                    ->label('Possible Values')
                    ->hint('Add all possible values for this feature. Each value can have an optional image.')
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Red, Blue, Green')
                            ->helperText('The actual value option (e.g., "Red", "128GB", "Large")')
                            ->dehydrated(),

                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->directory('feature-values')
                            ->visibility('public')
                            ->helperText('Optional image for this value')
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->downloadable()
                            ->openable()
                            ->dehydrated(),
                    ])
                    ->defaultItems(1)
                    ->minItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['value'] ?? 'New Value')
                    ->addActionLabel('Add Value')
                    ->reorderable()
                    ->cloneable()
                    ->dehydrated(),
            ]);
    }
}
