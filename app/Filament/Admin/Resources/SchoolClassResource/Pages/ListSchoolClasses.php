<?php

namespace App\Filament\Admin\Resources\SchoolClassResource\Pages;

use App\Filament\Admin\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolClasses extends ListRecords
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
