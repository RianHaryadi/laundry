<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Outlets';

    protected static ?string $modelLabel = 'Outlet';

    protected static ?string $pluralModelLabel = 'Outlets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Outlet Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter outlet name')
                            ->helperText('The name of the outlet/branch')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(4)
                            ->placeholder('Enter complete address...')
                            ->helperText('Full address of the outlet')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('+62 812-3456-7890')
                            ->helperText('Contact phone number for this outlet')
                            ->prefixIcon('heroicon-m-phone')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-m-building-storefront')
                    ->description(fn (Outlet $record): ?string => 
                        $record->address ? \Illuminate\Support\Str::limit($record->address, 60) : null
                    )
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->tooltip('Click to copy')
                    ->placeholder('No phone')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'N/A'),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Staff')
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-user-group')
                    ->description('Total staff'),
                
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-shopping-bag')
                    ->description('Total orders'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Created from: ' . \Carbon\Carbon::parse($data['created_from'])->format('d M Y');
                        }
                        
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Created until: ' . \Carbon\Carbon::parse($data['created_until'])->format('d M Y');
                        }
                        
                        return $indicators;
                    }),
                
                Tables\Filters\Filter::make('has_phone')
                    ->label('Has Phone Number')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('phone'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('call')
                    ->label('Call')
                    ->icon('heroicon-m-phone')
                    ->color('success')
                    ->url(fn (Outlet $record): ?string => 
                        $record->phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $record->phone) : null
                    )
                    ->visible(fn (Outlet $record): bool => (bool) $record->phone),
                
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Outlet $record): ?string => 
                        $record->phone 
                            ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->phone)
                            : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn (Outlet $record): bool => (bool) $record->phone),
                
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->action(function (Outlet $record) {
                        $newOutlet = $record->replicate();
                        $newOutlet->name = $record->name . ' (Copy)';
                        $newOutlet->save();
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Outlet duplicated successfully'),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export to CSV')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            $filename = 'outlets_' . now()->format('Y-m-d_His') . '.csv';
                            $headers = [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                            ];

                            $callback = function() use ($records) {
                                $file = fopen('php://output', 'w');
                                fputcsv($file, ['ID', 'Name', 'Address', 'Phone', 'Created At']);

                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->id,
                                        $record->name,
                                        $record->address,
                                        $record->phone,
                                        $record->created_at->format('Y-m-d H:i:s'),
                                    ]);
                                }

                                fclose($file);
                            };

                            return response()->stream($callback, 200, $headers);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Create Outlet'),
            ])
            ->emptyStateHeading('No outlets yet')
            ->emptyStateDescription('Create your first outlet to get started.')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::count();
        
        return match (true) {
            $count === 0 => 'danger',
            $count < 3 => 'warning',
            default => 'success',
        };
    }
}