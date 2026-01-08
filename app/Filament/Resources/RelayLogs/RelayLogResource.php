<?php

namespace App\Filament\Resources\RelayLogs;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Infolists;
use App\Models\RelayLog;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Enums\RecordActionsPosition;

class RelayLogResource extends Resource
{
    protected static ?string $model = RelayLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('relay_id')
                    ->relationship('relay', 'name')
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('payload')
                    ->json()
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('relay.name')
                    ->label('Relay'),
                Infolists\Components\TextEntry::make('payload')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                Infolists\Components\TextEntry::make('created_at')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('relay.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payload')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->limit(100)
                    ->wrap()
                    ->searchable()
                    ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\DeleteAction::make(),
                ]),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeCells)
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRelayLogs::route('/'),
        ];
    }
}
