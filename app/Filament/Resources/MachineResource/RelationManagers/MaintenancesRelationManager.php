<?php

namespace App\Filament\Resources\MachineResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';

    protected static ?string $title = 'Maintenance History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'preventive',
                        'warning' => 'corrective',
                        'danger' => 'emergency',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'scheduled',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('technician.name')
                    ->placeholder('Not assigned'),
                Tables\Columns\TextColumn::make('cost')
                    ->money('IDR')
                    ->placeholder('No cost'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}