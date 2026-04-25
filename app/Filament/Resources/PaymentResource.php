<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Commerce';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice')->disabled(),
            Forms\Components\TextInput::make('gateway')->disabled(),
            Forms\Components\TextInput::make('gateway_reference')->disabled(),
            Forms\Components\Select::make('status')
                ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all())
                ->required(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('Rp')->disabled(),
            Forms\Components\KeyValue::make('raw_payload')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('gateway')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all()),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
