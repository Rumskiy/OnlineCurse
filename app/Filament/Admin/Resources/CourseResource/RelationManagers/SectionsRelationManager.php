<?php

namespace App\Filament\Admin\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Навчальний матеріал та Тести (Розділи)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Section Manage')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Матеріал розділу')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Назва розділу')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Короткий опис розділу')
                                    ->rows(2)
                                    ->maxLength(500),
                                Forms\Components\RichEditor::make('contentSection')
                                    ->label('Текстовий лекційний матеріал')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Тести до розділу')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Forms\Components\Repeater::make('tests')
                                    ->relationship('tests')
                                    ->label('Тести')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                    ->defaultItems(0)
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Назва тесту')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('total_time_limit')
                                                    ->label('Загальний ліміт часу (сек)')
                                                    ->numeric()
                                                    ->default(600)
                                                    ->required(),
                                                Forms\Components\TextInput::make('time_per_question')
                                                    ->label('Час на одне питання (сек)')
                                                    ->numeric()
                                                    ->default(60)
                                                    ->required(),
                                            ]),
                                        
                                        Forms\Components\Repeater::make('questions')
                                            ->relationship('questions')
                                            ->label('Питання тесту')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['text'] ?? null)
                                            ->defaultItems(0)
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Тип питання')
                                                    ->options([
                                                        'single_choice' => 'Один варіант (single choice)',
                                                        'multiple_choice' => 'Кілька варіантів (multiple choice)',
                                                        'match' => 'Встановлення відповідностей (match)',
                                                    ])
                                                    ->required()
                                                    ->live(),
                                                
                                                Forms\Components\Textarea::make('text')
                                                    ->label('Текст питання')
                                                    ->required()
                                                    ->rows(2),
                                                
                                                Forms\Components\TextInput::make('points')
                                                    ->label('Бали за питання')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required(),
                                                
                                                // Options (For single or multiple choice questions)
                                                Forms\Components\Repeater::make('options')
                                                    ->relationship('options')
                                                    ->label('Варіанти відповідей')
                                                    ->visible(fn (callable $get) => in_array($get('type'), ['single_choice', 'multiple_choice']))
                                                    ->defaultItems(0)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('text')
                                                            ->label('Текст варіанту')
                                                            ->required(),
                                                        Forms\Components\Toggle::make('is_correct')
                                                            ->label('Правильний відповідь'),
                                                    ])
                                                    ->columns(2),
                                                
                                                // Match Pairs (For matching questions)
                                                Forms\Components\Repeater::make('matchPairs')
                                                    ->relationship('matchPairs')
                                                    ->label('Пари відповідностей')
                                                    ->visible(fn (callable $get) => $get('type') === 'match')
                                                    ->defaultItems(0)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('left_text')
                                                            ->label('Лівий елемент')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('right_text')
                                                            ->label('Правий елемент (відповідність)')
                                                            ->required(),
                                                    ])
                                                    ->columns(2),
                                            ])
                                            ->orderColumn('order')
                                            ->grid(1)
                                    ])
                                    ->grid(1)
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Назва розділу')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Опис')
                    ->limit(50),
                Tables\Columns\TextColumn::make('tests_count')
                    ->counts('tests')
                    ->label('Кількість тестів')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати розділ'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редагувати'),
                Tables\Actions\DeleteAction::make()
                    ->label('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
