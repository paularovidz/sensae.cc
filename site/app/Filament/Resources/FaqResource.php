<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
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

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'FAQ';
    protected static ?string $modelLabel = 'FAQ';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->label('Question')
                    ->required(),
                Textarea::make('answer')
                    ->label('Réponse')
                    ->required()
                    ->rows(4),
                TextInput::make('category')
                    ->label('Catégorie'),
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
                TextColumn::make('question')->label('Question')->searchable()->limit(60),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
