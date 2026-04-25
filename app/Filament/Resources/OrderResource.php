<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Services\Orders\OrderResolutionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Commerce';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice')->disabled(),
            Forms\Components\Select::make('status')
                ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all())
                ->required(),
            Forms\Components\Select::make('payment_status')
                ->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all())
                ->required(),
            Forms\Components\TextInput::make('guest_name')->maxLength(255),
            Forms\Components\TextInput::make('guest_email')->email()->maxLength(255),
            Forms\Components\TextInput::make('guest_phone')->maxLength(255),
            Forms\Components\TextInput::make('grand_total')->numeric()->prefix('Rp')->disabled(),
            Forms\Components\KeyValue::make('shipping_snapshot')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('guest_name')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('grand_total')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all()),
                Tables\Filters\SelectFilter::make('payment_status')->options(collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => str($status->value)->headline()->toString()])->all()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('cancel_unpaid')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => $record->payment_status !== PaymentStatus::Paid && $record->status !== OrderStatus::Cancelled)
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Admin note')->maxLength(1000),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderResolutionService::class)->cancelUnpaid($record, adminNote: $data['note'] ?? null);
                        Notification::make()->title('Order dibatalkan')->success()->send();
                    }),
                Tables\Actions\Action::make('approve_refund')
                    ->label('Approve Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::RefundRequested)
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Admin note')->maxLength(1000),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderResolutionService::class)->approveRefund($record, $data['note'] ?? null);
                        Notification::make()->title('Refund diproses')->success()->send();
                    }),
                Tables\Actions\Action::make('reject_refund')
                    ->label('Reject Refund')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::RefundRequested)
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Admin note')->maxLength(1000),
                    ])
                    ->action(function (Order $record, array $data): void {
                        app(OrderResolutionService::class)->rejectRefund($record, $data['note'] ?? null);
                        Notification::make()->title('Refund ditolak')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()->with('items');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
