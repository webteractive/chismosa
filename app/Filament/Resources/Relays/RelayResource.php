<?php

namespace App\Filament\Resources\Relays;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use App\Models\Relay;
use Filament\Actions;
use Filament\Infolists;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Enums\RecordActionsPosition;

class RelayResource extends Resource
{
    protected static ?string $model = Relay::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options(config('chismosa.services', []))
                    ->native(false),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('webhook_type')
                    ->required()
                    ->options([
                        'google_chat' => 'Google Chat',
                        'forge' => 'Laravel Forge',
                    ])
                    ->native(false),
                Forms\Components\TextInput::make('webhook_url')
                    ->required()
                    ->url()
                    ->maxLength(65535),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->native(false),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->default(auth()->id())
                    ->native(false),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('type'),
                Infolists\Components\TextEntry::make('description'),
                Infolists\Components\TextEntry::make('webhook_type')
                    ->label('Webhook Type'),
                Infolists\Components\TextEntry::make('webhook_url')
                    ->label('Webhook URL')
                    ->copyable(),
                Infolists\Components\TextEntry::make('endpoint')
                    ->label('Endpoint')
                    ->copyable()
                    ->copyMessage('Endpoint copied!')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Set relay key first')
                    ->placeholder('Set relay key first'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Active' : 'Inactive'),
                Infolists\Components\TextEntry::make('user.name')
                    ->label('User'),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('webhook_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->copyable(fn (?string $state): bool => $state !== null)
                    ->copyMessage('Endpoint copied!')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Set relay key first')
                    ->placeholder('Set relay key first'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? 'Active' : 'Inactive'),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    Actions\EditAction::make(),
                ]),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeCells)
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RelayLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRelays::route('/'),
        ];
    }
}
