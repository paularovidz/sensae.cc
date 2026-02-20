<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Resources\Resource;
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

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Témoignages';
    protected static ?string $modelLabel = 'Témoignage';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('role')
                    ->label('Rôle'),
                Textarea::make('content')
                    ->label('Témoignage')
                    ->required()
                    ->rows(4),
                TextInput::make('rating')
                    ->label('Note')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->default(5),
                TextInput::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->label('Publié')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')->label('Nom')->searchable(),
                TextColumn::make('role')->label('Rôle'),
                TextColumn::make('rating')->label('Note'),
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
