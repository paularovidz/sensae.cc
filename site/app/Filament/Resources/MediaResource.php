<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use App\Services\ImageService;
use Filament\Resources\Resource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Médias';
    protected static ?string $modelLabel = 'Média';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('upload')
                    ->label('Image')
                    ->image()
                    ->disk('local')
                    ->directory('temp-uploads')
                    ->visibility('private')
                    ->hiddenOn('edit'),
                TextInput::make('slug')
                    ->label('Slug (identifiant)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Identifiant utilisé dans les templates Blade : <x-image slug="..." />'),
                TextInput::make('alt')
                    ->label('Texte alternatif'),
                TextInput::make('folder')
                    ->label('Dossier')
                    ->helperText('Organiser les médias par dossier (ex: blog, sens, pages)'),
                TextInput::make('url')
                    ->label('URL personnalisée')
                    ->url()
                    ->helperText('Laisser vide pour utiliser le fichier local. Renseigner pour pointer vers un CDN ou URL externe.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Image')
                    ->disk('public')
                    ->size(60),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('folder')
                    ->label('Dossier')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('original_name')
                    ->label('Fichier original')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('width')
                    ->label('Dimensions')
                    ->formatStateUsing(fn ($record) => $record->width && $record->height ? "{$record->width}x{$record->height}" : '-'),
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
            'index' => Pages\ListMedia::route('/'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
