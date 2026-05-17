<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        TextInput::make('firstName')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('lastName')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                'admin' => 'Адміністратор',
                                'teacher' => 'Вчитель',
                                'student' => 'Учень',
                            ])
                            ->required()
                            ->live(), // Make it live so class field responds to role changes
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'banned' => 'Banned',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),
                        Select::make('school_class_id')
                            ->label('Шкільний клас')
                            ->relationship('schoolClass', 'name')
                            ->placeholder('Оберіть клас (тільки для учнів)')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('role') === 'student')
                            ->nullable(),
                    ])->columns(2),
                Forms\Components\Section::make('Avatar')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->collection('default')
                            ->avatar()
                            ->imageEditor(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('default')
                    ->circular(),
                Tables\Columns\TextColumn::make('firstName')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lastName')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin', '2' => 'danger',
                        'teacher', '3' => 'warning',
                        'student', '1' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Клас')
                    ->placeholder('Не призначено')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'banned' => 'danger',
                        'inactive' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
