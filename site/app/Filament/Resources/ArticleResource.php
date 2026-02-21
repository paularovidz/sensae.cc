<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Resources\Resource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Articles';
    protected static ?string $modelLabel = 'Article';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                MediaPicker::make('image')
                    ->label('Image'),
                Textarea::make('excerpt')
                    ->label('Extrait')
                    ->rows(3),
                TextInput::make('author')
                    ->label('Auteur'),
                TagsInput::make('categories')
                    ->label('Catégories'),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('Date de publication'),
                Toggle::make('is_published')
                    ->label('Publié')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titre')->searchable()->sortable(),
                TextColumn::make('author')->label('Auteur'),
                IconColumn::make('is_published')->label('Publié')->boolean(),
                TextColumn::make('published_at')->label('Publié le')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
