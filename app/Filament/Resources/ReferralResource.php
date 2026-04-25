<?php

namespace App\Filament\Resources;

use App\Enums\ReferralStatus;
use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
use App\Services\Referrals\ReferralService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Growth';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('referrer_id')
                ->relationship('referrer', 'email')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('referred_id')
                ->relationship('referred', 'email')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('referral_code')->maxLength(255),
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('status')
                ->options(ReferralStatus::options())
                ->required(),
            Forms\Components\TextInput::make('reward_amount')->numeric()->prefix('Rp')->default(0),
            Forms\Components\DateTimePicker::make('qualified_at'),
            Forms\Components\DateTimePicker::make('rewarded_at'),
            Forms\Components\DateTimePicker::make('rejected_at'),
            Forms\Components\Textarea::make('admin_note')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referrer.email')->label('Referrer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('referred.email')->label('Referred')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('referral_code')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('code')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?ReferralStatus $state): string => $state?->label() ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reward_amount')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('qualified_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('rewarded_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ReferralStatus::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('qualify')
                    ->label('Qualify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Referral $record): bool => $record->status === ReferralStatus::Pending)
                    ->form([
                        Forms\Components\TextInput::make('reward_amount')->numeric()->prefix('Rp'),
                    ])
                    ->action(function (Referral $record, array $data): void {
                        app(ReferralService::class)->qualify(
                            $record,
                            filled($data['reward_amount'] ?? null) ? (float) $data['reward_amount'] : null,
                        );
                        Notification::make()->title('Referral di-qualify')->success()->send();
                    }),
                Tables\Actions\Action::make('reward')
                    ->label('Reward')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Referral $record): bool => in_array($record->status, [ReferralStatus::Pending, ReferralStatus::Qualified], true))
                    ->form([
                        Forms\Components\TextInput::make('reward_amount')->numeric()->prefix('Rp'),
                    ])
                    ->action(function (Referral $record, array $data): void {
                        app(ReferralService::class)->reward(
                            $record,
                            filled($data['reward_amount'] ?? null) ? (float) $data['reward_amount'] : null,
                        );
                        Notification::make()->title('Referral reward dicatat ke wallet seller')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Referral $record): bool => in_array($record->status, [ReferralStatus::Pending, ReferralStatus::Qualified], true))
                    ->form([
                        Forms\Components\Textarea::make('admin_note')->label('Catatan admin')->maxLength(1000),
                    ])
                    ->action(function (Referral $record, array $data): void {
                        app(ReferralService::class)->reject($record, $data['admin_note'] ?? null);
                        Notification::make()->title('Referral ditolak')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['referrer', 'referred']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
        ];
    }
}
