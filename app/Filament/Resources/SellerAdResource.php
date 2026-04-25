<?php

namespace App\Filament\Resources;

use App\Enums\AdsPlacement;
use App\Enums\AdsStatus;
use App\Filament\Resources\SellerAdResource\Pages;
use App\Models\SellerAd;
use App\Services\Ads\SellerAdModerationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SellerAdResource extends Resource
{
    protected static ?string $model = SellerAd::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Growth';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('seller_id')->relationship('seller', 'store_name')->required()->searchable()->preload(),
            Forms\Components\Select::make('product_id')->relationship('product', 'title')->searchable()->preload(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Select::make('placement')
                ->options(AdsPlacement::options())
                ->required(),
            Forms\Components\Select::make('status')
                ->options(AdsStatus::options())
                ->required(),
            Forms\Components\TextInput::make('budget')->numeric()->prefix('Rp')->default(0),
            Forms\Components\DateTimePicker::make('starts_at'),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\Textarea::make('admin_note')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('seller.store_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.title')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('placement')
                    ->badge()
                    ->formatStateUsing(fn (?AdsPlacement $state): string => $state?->label() ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?AdsStatus $state): string => $state?->label() ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(AdsStatus::options()),
                Tables\Filters\SelectFilter::make('placement')->options(AdsPlacement::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SellerAd $record): bool => ! $record->trashed() && $record->status !== AdsStatus::Active)
                    ->action(function (SellerAd $record): void {
                        app(SellerAdModerationService::class)->activate($record);
                        Notification::make()->title('Iklan diaktifkan')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SellerAd $record): bool => ! $record->trashed() && $record->status === AdsStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('admin_note')->maxLength(1000),
                    ])
                    ->action(function (SellerAd $record, array $data): void {
                        app(SellerAdModerationService::class)->reject($record, $data['admin_note'] ?? null);
                        Notification::make()->title('Iklan ditolak')->success()->send();
                    }),
                Tables\Actions\Action::make('expire')
                    ->label('Expire')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->visible(fn (SellerAd $record): bool => ! $record->trashed() && $record->status === AdsStatus::Active)
                    ->action(function (SellerAd $record): void {
                        app(SellerAdModerationService::class)->expire($record);
                        Notification::make()->title('Iklan di-expire')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()->with(['seller', 'product']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellerAds::route('/'),
            'create' => Pages\CreateSellerAd::route('/create'),
            'edit' => Pages\EditSellerAd::route('/{record}/edit'),
        ];
    }
}
