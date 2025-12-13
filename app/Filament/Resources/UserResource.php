<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name')
                            ->placeholder('Enter full name'),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->autocomplete('email')
                            ->placeholder('user@example.com'),
                        
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->maxLength(255)
                            ->revealable()
                            ->placeholder('Enter password')
                            ->helperText('Leave blank to keep current password when editing'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Role & Access')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->required()
                            ->options(User::getRoles())
                            ->native(false)
                            ->searchable()
                            ->placeholder('Select role'),
                        
                        Forms\Components\Select::make('outlet_id')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Select outlet (optional)')
                            ->helperText('Assign user to specific outlet'),
                    ])
                    ->columns(2),
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
                    ->icon('heroicon-m-user')
                    ->copyable()
                    ->tooltip('Click to copy'),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->tooltip('Click to copy'),
                
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn($state) => User::getRoles()[$state] ?? ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        User::ROLE_OWNER => 'danger',
                        User::ROLE_ADMIN => 'warning',
                        User::ROLE_STAFF => 'info',
                        User::ROLE_COURIER => 'success',
                        default => 'secondary',
                    })
                    ->icon(fn($state) => match ($state) {
                        User::ROLE_OWNER => 'heroicon-o-shield-check',
                        User::ROLE_ADMIN => 'heroicon-o-cog',
                        User::ROLE_STAFF => 'heroicon-o-user',
                        User::ROLE_COURIER => 'heroicon-o-truck',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No outlet assigned')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn ($record): string => $record->created_at->diffForHumans()),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->filters([
               Tables\Filters\SelectFilter::make('role')
                    ->options(User::getRoles())
                    ->multiple()
                    ->label('Filter by Role'),

                Tables\Filters\SelectFilter::make('outlet')
                    ->relationship('outlet', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Filter by Outlet'),
                
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
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }
}