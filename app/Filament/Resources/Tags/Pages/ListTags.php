<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Support\Facades\Log;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTableRecords(): Paginator
    {
        try {
            $tags = Tag::all();
            $page = max(1, (int) request()->query('page', 1));
            $perPage = 15;

            return new BasePaginator(
                $tags->forPage($page, $perPage)->values()->all(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ],
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch tags: ' . $e->getMessage());
            return new BasePaginator([], 15, 1);
        }
    }
}
