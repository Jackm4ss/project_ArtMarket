<?php

namespace App\Filament\Resources;

use App\Enums\BannerPlacement;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Banner')
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255),
                    Forms\Components\Select::make('placement')
                        ->options(BannerPlacement::options())
                        ->required()
                        ->default(BannerPlacement::HomeHero->value),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Image')
                        ->disk('public')
                        ->directory('banners')
                        ->image()
                        ->imageEditor()
                        ->maxSize(4096),
                    Forms\Components\TextInput::make('link_url')
                        ->url()
                        ->maxLength(2048),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->inline(false),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\DateTimePicker::make('starts_at')->seconds(false),
                    Forms\Components\DateTimePicker::make('ends_at')->seconds(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->disk('public')->square()->toggleable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('placement')
                    ->badge()
                    ->formatStateUsing(fn (BannerPlacement $state): string => $state->label())
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('placement')->options(BannerPlacement::options()),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Banner $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Banner $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Banner $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Banner $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()->title('Status banner diperbarui')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
