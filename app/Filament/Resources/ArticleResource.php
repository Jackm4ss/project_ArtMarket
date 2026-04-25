<?php

namespace App\Filament\Resources;

use App\Enums\ArticleStatus;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Editorial')
                ->schema([
                    Forms\Components\Select::make('author_id')
                        ->relationship('author', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('status')
                        ->options(ArticleStatus::options())
                        ->required()
                        ->default(ArticleStatus::Draft->value),
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),
                    Forms\Components\TextInput::make('slug')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Kosongkan agar slug dibuat otomatis dari judul.'),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->seconds(false)
                        ->helperText('Jika status Published dan tanggal kosong, sistem akan mengisi waktu sekarang.'),
                    Forms\Components\Textarea::make('excerpt')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('body')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'link',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'h2',
                            'h3',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('author.name')->label('Author')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ArticleStatus $state): string => $state->label())
                    ->color(fn (ArticleStatus $state): string => match ($state) {
                        ArticleStatus::Published => 'success',
                        ArticleStatus::Archived => 'gray',
                        ArticleStatus::Draft => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ArticleStatus::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Article $record): bool => $record->status !== ArticleStatus::Published)
                    ->action(function (Article $record): void {
                        $record->update([
                            'status' => ArticleStatus::Published,
                            'published_at' => $record->published_at ?? now(),
                        ]);

                        Notification::make()->title('Artikel dipublish')->success()->send();
                    }),
                Tables\Actions\Action::make('unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Article $record): bool => $record->status === ArticleStatus::Published)
                    ->action(function (Article $record): void {
                        $record->update(['status' => ArticleStatus::Draft]);

                        Notification::make()->title('Artikel kembali ke draft')->success()->send();
                    }),
                Tables\Actions\Action::make('archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Article $record): bool => $record->status !== ArticleStatus::Archived)
                    ->action(function (Article $record): void {
                        $record->update(['status' => ArticleStatus::Archived]);

                        Notification::make()->title('Artikel diarsipkan')->success()->send();
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with('author:id,name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
