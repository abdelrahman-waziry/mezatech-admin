<?php

namespace App\Jobs;

use App\Traits\HasFixtechImport;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;

class FixtechSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasFixtechImport;

    public $timeout = 600; // 10 minutes

    public function __construct(
        protected string $filePath,
        protected int $userId,
        protected string $type = 'all' // 'repairs' or 'accessories'
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        $fullPath = Storage::disk('local')->path($this->filePath);

        $this->setSyncProgress(0, "Initializing Sync...");

        try {
            $log = DB::transaction(function () use ($fullPath) {
                $spreadsheet = IOFactory::load($fullPath);

                // Truncate based on type
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                if ($this->type === 'repairs' || $this->type === 'all') {
                    DB::table('repair_prices')->truncate();
                    DB::table('repair_subcategories')->truncate();
                }
                if ($this->type === 'accessories' || $this->type === 'all') {
                    DB::table('accessories')->truncate();
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                $sheetLog = [];
                if ($this->type === 'repairs' || $this->type === 'all') {
                    $sheetLog = array_merge($sheetLog, $this->seedRepairSheets($spreadsheet));
                }
                if ($this->type === 'accessories' || $this->type === 'all') {
                    $sheetLog = array_merge($sheetLog, $this->seedAccessorySheets($spreadsheet));
                }

                return $sheetLog;
            });

            $this->setSyncProgress(100, "Sync Complete!");

            if ($user) {
                Notification::make()
                    ->success()
                    ->persistent()
                    ->title('FixTech Sync Complete')
                    ->body(implode("\n", $log))
                    ->sendToDatabase($user);
            }

        } catch (\Throwable $e) {
            $this->setSyncProgress(0, "Sync Failed!");
            if ($user) {
                Notification::make()
                    ->danger()
                    ->title('FixTech Sync Failed')
                    ->body($e->getMessage())
                    ->sendToDatabase($user);
            }
        } finally {
            Storage::disk('local')->delete($this->filePath);
            \Illuminate\Support\Facades\Cache::forget('fixtech_sync_progress');
        }
    }
}
