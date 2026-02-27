<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SensResource\Pages;
use App\Models\Sens;
use App\Filament\Forms\Components\MediaPicker;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SensResource extends Resource
{
    protected static ?string $model = Sens::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationLabel = 'Sens';
    protected static ?string $modelLabel = 'Sens';
    protected static ?string $pluralModelLabel = 'Sens';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', \Illuminate\Support\Str::cleanSlug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('category')
                    ->label('Catégorie')
                    ->options([
                        'Tactile' => 'Tactile',
                        'Visuel' => 'Visuel',
                        'Olfactif' => 'Olfactif',
                        'Gustatif' => 'Gustatif',
                        'Auditif' => 'Auditif',
                        'Proprioceptif' => 'Proprioceptif',
                    ]),
                MediaPicker::make('image')
                    ->label('Image'),
                Textarea::make('excerpt')
                    ->label('Extrait')
                    ->rows(3),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
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
                TextColumn::make('category')->label('Catégorie')->badge(),
                IconColumn::make('is_published')->label('Publié')->boolean(),
                TextColumn::make('order')->label('Ordre')->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
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
            'index' => Pages\ListSens::route('/'),
            'create' => Pages\CreateSens::route('/create'),
            'edit' => Pages\EditSens::route('/{record}/edit'),
        ];
    }
}
