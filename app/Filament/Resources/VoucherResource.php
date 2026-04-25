<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Growth';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Voucher')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(fn (?string $state): string => str($state ?? '')->upper()->trim()->toString()),
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->options(['fixed' => 'Fixed Amount', 'percent' => 'Percent'])
                        ->required()
                        ->default('fixed'),
                    Forms\Components\TextInput::make('value')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp / %'),
                    Forms\Components\TextInput::make('minimum_order_amount')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp'),
                    Forms\Components\TextInput::make('max_discount_amount')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->helperText('Opsional. Berguna untuk voucher persentase, misalnya 10% maksimal Rp 100.000.'),
                ])
                ->columns(2),
            Forms\Components\Section::make('Limits and schedule')
                ->schema([
                    Forms\Components\TextInput::make('usage_limit')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Kosongkan jika tidak ada batas global.'),
                    Forms\Components\TextInput::make('per_user_limit')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Kosongkan jika user/guest boleh pakai berkali-kali.'),
                    Forms\Components\TextInput::make('used_count')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\DateTimePicker::make('starts_at'),
                    Forms\Components\DateTimePicker::make('ends_at'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('value')->sortable(),
                Tables\Columns\TextColumn::make('max_discount_amount')->money('IDR')->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('used_count')->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('per_user_limit')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('redemptions_count')->label('Redemptions')->counts('redemptions')->sortable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active'), Tables\Filters\TrashedFilter::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(), Tables\Actions\RestoreAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
