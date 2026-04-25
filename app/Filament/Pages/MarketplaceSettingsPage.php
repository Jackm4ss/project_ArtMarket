<?php

namespace App\Filament\Pages;

use App\Settings\MarketplaceSettings;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MarketplaceSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Marketplace Settings';

    protected static ?string $slug = 'marketplace-settings';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.marketplace-settings-page';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        /** @var MarketplaceSettings $settings */
        $settings = app(MarketplaceSettings::class);

        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Commerce rules')
                    ->description('Nilai ini menjadi source of truth operasional marketplace setelah aplikasi dimigrasikan ke hosting.')
                    ->schema([
                        Forms\Components\TextInput::make('currency')
                            ->required()
                            ->maxLength(3)
                            ->helperText('Contoh: IDR'),
                        Forms\Components\TextInput::make('commission_rate')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->helperText('Gunakan desimal. Contoh 0.10 untuk 10%.'),
                        Forms\Components\Select::make('shipping_mode')
                            ->options(['manual' => 'Manual'])
                            ->required(),
                        Forms\Components\Toggle::make('product_auto_publish')
                            ->label('Produk seller auto-publish')
                            ->inline(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Payout and growth')
                    ->schema([
                        Forms\Components\TextInput::make('withdraw_minimum')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('withdraw_fee')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('withdraw_schedule')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('referral_reward_amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        /** @var MarketplaceSettings $settings */
        $settings = app(MarketplaceSettings::class);

        $settings->fill($this->form->getState());
        $settings->save();

        Notification::make()
            ->title('Marketplace settings disimpan')
            ->success()
            ->send();
    }
}
