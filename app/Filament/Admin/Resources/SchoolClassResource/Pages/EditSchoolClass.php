<?php

namespace App\Filament\Admin\Resources\SchoolClassResource\Pages;

use App\Filament\Admin\Resources\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolClass extends EditRecord
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
