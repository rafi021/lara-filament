<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Order Details')->schema([
                        TextInput::make('number')
                            ->default('OR-' . random_int(1000000, 9999999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => OrderStatusEnum::Pending->value,
                                'completed' => OrderStatusEnum::Completed->value,
                                'declined' => OrderStatusEnum::Declined->value,
                                'processing' => OrderStatusEnum::Processing->value,
                            ])->columnSpanFull()
                            ->required(),
                        MarkdownEditor::make('notes')->columnSpanFull(),
                    ])->columns(2),
                    Step::make('Order Items')->schema([
                        Repeater::make('items')
                            ->relationship('')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product Name')
                                    ->options(Product::query()->pluck('name', 'id')),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->required(),
                            ])->columns(3)
                    ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable()
                    ->label('Order Number'),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Sum::make()->money()
                    ])
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
