<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerResource\Pages;
use App\Models\Seller;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SellerResource extends Resource
{
    protected static ?string $model = Seller::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Marketplace';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'email')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('store_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->maxLength(255)->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('bio')->columnSpanFull(),
            Forms\Components\Select::make('status')->options(['active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending'])->required(),
            Forms\Components\TextInput::make('location')->maxLength(255),
            Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
            Forms\Components\TextInput::make('bank_name')->maxLength(255),
            Forms\Components\TextInput::make('bank_account_name')->maxLength(255),
            Forms\Components\TextInput::make('bank_account_number')->maxLength(255),
            Forms\Components\DateTimePicker::make('verified_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('location')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('rating_average')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products')->sortable(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('status')->options(['active' => 'Active', 'suspended' => 'Suspended', 'pending' => 'Pending']), Tables\Filters\TrashedFilter::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellers::route('/'),
            'create' => Pages\CreateSeller::route('/create'),
            'edit' => Pages\EditSeller::route('/{record}/edit'),
        ];
    }
}
