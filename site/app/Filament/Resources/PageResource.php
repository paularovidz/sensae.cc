<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Resources\Resource;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document';
    protected static ?string $navigationLabel = 'Pages';
    protected static ?string $modelLabel = 'Page';
    protected static ?int $navigationSort = 5;

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
                Select::make('template')
                    ->label('Template')
                    ->options([
                        'default' => 'Par défaut',
                        'location' => 'Ville / Localisation',
                        'legal' => 'Mentions légales',
                    ])
                    ->default('default'),
                TextInput::make('h1')
                    ->label('H1'),
                TextInput::make('big_title')
                    ->label('Sur-titre'),
                MediaPicker::make('image')
                    ->label('Image'),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->label('Meta title'),
                TextInput::make('meta_description')
                    ->label('Meta description')
                    ->maxLength(500),
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
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('template')->label('Template')->badge(),
                IconColumn::make('is_published')->label('Publié')->boolean(),
            ])
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
