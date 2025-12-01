<?php

namespace App\Filament\Resources\OutletPriceResource\Pages;

use App\Filament\Resources\OutletPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutletPrice extends EditRecord
{
    protected static string $resource = OutletPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
