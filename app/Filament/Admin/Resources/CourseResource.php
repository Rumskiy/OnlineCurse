<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('author_id')
                            ->relationship('author', 'firstName') // Adjust based on your relationship
                            ->required()
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->firstName} {$record->lastName}"),
                        Textarea::make('description')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Visuals')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('title_img')
                            ->collection('title_img')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('title_img')
                    ->collection('title_img')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.firstName')
                    ->label('Author')
                    ->formatStateUsing(fn ($record) => "{$record->author->firstName} {$record->author->lastName}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
