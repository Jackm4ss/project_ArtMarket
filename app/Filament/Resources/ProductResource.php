<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product')
                ->schema([
                    Forms\Components\Select::make('seller_id')->relationship('seller', 'store_name')->required()->searchable()->preload(),
                    Forms\Components\Select::make('category_id')->relationship('category', 'name')->searchable()->preload(),
                    Forms\Components\TextInput::make('sku')->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('title')->required()->maxLength(255),
                    Forms\Components\TextInput::make('slug')->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('excerpt')->rows(2)->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')->columnSpanFull(),
                ])
                ->columns(2),
            Forms\Components\Section::make('Commerce')
                ->schema([
                    Forms\Components\TextInput::make('price')->required()->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('compare_at_price')->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('stock')->required()->numeric()->minValue(0),
                    Forms\Components\Select::make('status')
                        ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all())
                        ->required(),
                    Forms\Components\Select::make('product_type')->options(['ready' => 'Ready', 'preorder' => 'Preorder'])->required(),
                    Forms\Components\Toggle::make('is_featured'),
                ])
                ->columns(3),
            Forms\Components\Section::make('Details')
                ->schema([
                    Forms\Components\TextInput::make('material')->maxLength(255),
                    Forms\Components\TextInput::make('dimensions')->maxLength(255),
                    Forms\Components\TextInput::make('weight_gram')->numeric()->suffix('gram'),
                    Forms\Components\TextInput::make('location')->maxLength(255),
                    Forms\Components\TextInput::make('preorder_days')->numeric()->suffix('days'),
                    Forms\Components\DateTimePicker::make('published_at'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('seller.store_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('sold_count')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all()),
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name')->searchable()->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('unpublish')
                    ->requiresConfirmation()
                    ->visible(fn (Product $record): bool => $record->status === ProductStatus::Published)
                    ->action(fn (Product $record) => $record->update(['status' => ProductStatus::Unpublished])),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
