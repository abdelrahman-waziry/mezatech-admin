<?php

namespace App\Filament\Actions;

use App\Traits\HasFixtechImport;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportFixtechRepairs extends Action
{
    use HasFixtechImport;

    public static function getDefaultName(): ?string
    {
        return 'import_repairs';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                FileUpload::make('file')
                    ->label('Excel File')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->disk('local')
                    ->directory('temp-imports')
                    ->required(),
            ])
            ->action(function (array $data) {
                try {
                    \App\Jobs\FixtechSyncJob::dispatch(
                        $data['file'],
                        auth()->id(),
                        'all'
                    );

                    Notification::make()
                        ->info()
                        ->title('Sync started')
                        ->body('The background task is currently processing the Excel file. You will receive a dashboard notification when it finishes.')
                        ->send();

                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title('Import failed')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
