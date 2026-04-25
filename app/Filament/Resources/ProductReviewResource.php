<?php

namespace App\Filament\Resources;

use App\Enums\ProductReviewStatus;
use App\Filament\Resources\ProductReviewResource\Pages;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Review')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options(ProductReviewStatus::options())
                        ->required(),
                    Forms\Components\TextInput::make('rating')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5),
                    Forms\Components\TextInput::make('title')
                        ->maxLength(120),
                    Forms\Components\Textarea::make('body')
                        ->rows(5)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Forms\Components\Section::make('Context')
                ->schema([
                    Forms\Components\Placeholder::make('user')
                        ->content(fn (?ProductReview $record): string => $record?->user?->name ?? '-'),
                    Forms\Components\Placeholder::make('product')
                        ->content(fn (?ProductReview $record): string => $record?->product?->title ?? '-'),
                    Forms\Components\Placeholder::make('order_item_id')
                        ->label('Order item')
                        ->content(fn (?ProductReview $record): string => $record?->order_item_id ? '#'.$record->order_item_id : '-'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('rating')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ProductReviewStatus $state): string => $state->label())
                    ->color(fn (ProductReviewStatus $state): string => $state === ProductReviewStatus::Published ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ProductReviewStatus::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReviewStatus::Published)
                    ->action(function (ProductReview $record): void {
                        $record->update(['status' => ProductReviewStatus::Published]);

                        Notification::make()->title('Review dipublikasikan')->success()->send();
                    }),
                Tables\Actions\Action::make('hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ProductReview $record): bool => $record->status === ProductReviewStatus::Published)
                    ->action(function (ProductReview $record): void {
                        $record->update(['status' => ProductReviewStatus::Hidden]);

                        Notification::make()->title('Review disembunyikan')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['product:id,title', 'user:id,name']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
