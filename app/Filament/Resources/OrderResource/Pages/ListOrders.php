<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->resetTable();
                    
                    Notification::make()
                        ->title('Table Refreshed')
                        ->body('Data reloaded from database')
                        ->success()
                        ->send();
                }),
                
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('New Order'),
        ];
    }

    /**
     * CRITICAL: Override query untuk SELALU ambil data FRESH
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with([
                'customer', 
                'outlet', 
                'service',
                'courier',
                'orderItems.service',
            ])
            ->withCount('orderItems');
    }

    /**
     * Disable caching untuk memastikan data real-time
     */
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}