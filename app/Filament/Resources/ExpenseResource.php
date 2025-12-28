<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Financial Reports';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Information')
                    ->schema([
                        Forms\Components\DatePicker::make('expense_date')
                            ->label('Date')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Select::make('expense_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->placeholder('e.g., Deterjen Attack 5kg'),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0'),

                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'Cash' => 'Cash',
                                'Transfer' => 'Transfer',
                                'E-wallet' => 'E-wallet',
                            ])
                            ->required()
                            ->default('Cash'),

                        Forms\Components\Select::make('outlet_id')
                            ->relationship('outlet', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\FileUpload::make('receipt_file')
                            ->label('Receipt/Proof')
                            ->image()
                            ->directory('expenses/receipts')
                            ->nullable()
                            ->maxSize(2048),

                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->placeholder('Optional notes'),

                        Forms\Components\Hidden::make('created_by')
                            ->default(Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cash' => 'success',
                        'Transfer' => 'info',
                        'E-wallet' => 'warning',
                    }),

                Tables\Columns\ImageColumn::make('receipt_file')
                    ->label('Receipt')
                    ->circular(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'Cash' => 'Cash',
                        'Transfer' => 'Transfer',
                        'E-wallet' => 'E-wallet',
                    ]),

                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('expense_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('expense_date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Expense $record): bool => 
                        Auth::user()->hasRole('owner') || 
                        (Auth::id() === $record->created_by && $record->expense_date->isToday())
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Expense $record): bool => 
                        Auth::user()->hasRole('owner') || 
                        (Auth::id() === $record->created_by && $record->expense_date->isToday())
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
            'view' => Pages\ViewExpense::route('/{record}'),
        ];
    }
}