<?php

namespace App\Filament\Resources;

use App\Enums\WithdrawStatus;
use App\Filament\Resources\WithdrawResource\Pages;
use App\Models\Withdraw;
use App\Services\Withdraws\WithdrawService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WithdrawResource extends Resource
{
    protected static ?string $model = Withdraw::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Commerce';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('seller_id')->relationship('seller', 'store_name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('amount')->required()->numeric()->prefix('Rp'),
            Forms\Components\TextInput::make('fee')->numeric()->prefix('Rp')->default(0),
            Forms\Components\Select::make('status')
                ->options(WithdrawStatus::options())
                ->required(),
            Forms\Components\TextInput::make('bank_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('bank_account_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('bank_account_number')->required()->maxLength(255),
            Forms\Components\Textarea::make('admin_note')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('requested_at'),
            Forms\Components\DateTimePicker::make('processed_at'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seller.store_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('fee')->money('IDR')->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?WithdrawStatus $state): string => $state?->label() ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(WithdrawStatus::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->requiresConfirmation()
                    ->visible(fn (Withdraw $record): bool => $record->status === WithdrawStatus::Pending)
                    ->action(fn (Withdraw $record) => app(WithdrawService::class)->approve($record)),
                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('admin_note')->label('Catatan admin'),
                    ])
                    ->visible(fn (Withdraw $record): bool => in_array($record->status, [WithdrawStatus::Pending, WithdrawStatus::Approved], true))
                    ->action(fn (Withdraw $record, array $data) => app(WithdrawService::class)->reject($record, $data['admin_note'] ?? null)),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->requiresConfirmation()
                    ->visible(fn (Withdraw $record): bool => $record->status === WithdrawStatus::Approved)
                    ->action(fn (Withdraw $record) => app(WithdrawService::class)->markPaid($record)),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdraws::route('/'),
            'create' => Pages\CreateWithdraw::route('/create'),
            'edit' => Pages\EditWithdraw::route('/{record}/edit'),
        ];
    }
}
