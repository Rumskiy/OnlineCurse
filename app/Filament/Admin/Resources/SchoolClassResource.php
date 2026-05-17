<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SchoolClassResource\Pages;
use App\Filament\Admin\Resources\SchoolClassResource\RelationManagers;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Параметри класу')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва класу')
                            ->placeholder('напр. 10-А, 7-Б')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('academic_year')
                            ->label('Навчальний рік')
                            ->placeholder('напр. 2025/2026')
                            ->default('2025/2026')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('homeroom_teacher_id')
                            ->label('Класний керівник')
                            ->relationship(
                                name: 'homeroomTeacher',
                                titleAttribute: 'firstName',
                                modifyQueryUsing: fn (Builder $query) => $query->where('role', 'teacher')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->firstName} {$record->lastName}")
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Клас')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Навчальний рік')
                    ->sortable(),
                Tables\Columns\TextColumn::make('homeroomTeacher')
                    ->label('Класний керівник')
                    ->formatStateUsing(fn ($record) => $record->homeroomTeacher ? "{$record->homeroomTeacher->firstName} {$record->homeroomTeacher->lastName}" : 'Не призначено')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('homeroomTeacher', function ($q) use ($search) {
                            $q->where('firstName', 'like', "%{$search}%")
                              ->orWhere('lastName', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Кількість учнів')
                    ->counts('students')
                    ->sortable(),
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
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}
