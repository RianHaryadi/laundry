<?php

namespace App\Filament\Resources\OutletPriceResource\Pages;

use App\Filament\Resources\OutletPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutletPrices extends ListRecords
{
    protected static string $resource = OutletPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
