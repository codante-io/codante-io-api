<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Filament\Resources\ChallengeResource\RelationManagers;
use App\Models\Challenge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationGroup = 'Conteúdo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'published' => 'Publicado',
                                'soon' => 'Em Breve',
                                'archived' => 'Arquivado',
                                'unlisted' => 'Não Listado',
                            ])
                            ->required(),
                        Forms\Components\Select::make('category')
                            ->label('Categoria')
                            ->options([
                                'frontend' => 'Frontend',
                                'backend' => 'Backend',
                                'fullstack' => 'Fullstack',
                                'mobile' => 'Mobile',
                                'devops' => 'DevOps',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Descrição e Conteúdo')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->label('Descrição Curta')
                            ->rows(3),
                        Forms\Components\MarkdownEditor::make('description')
                            ->label('Descrição Completa'),
                    ]),

                Forms\Components\Section::make('Mídia e Links')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Imagem de Capa')
                            ->image()
                            ->disk('s3')
                            ->directory('challenges/cover-images'),
                        Forms\Components\TextInput::make('video_url')
                            ->label('URL do Vídeo')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('repository_name')
                            ->label('Nome do Repositório')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Configurações')
                    ->schema([
                        Forms\Components\Select::make('difficulty')
                            ->label('Dificuldade')
                            ->options([
                                1 => 'Muito Fácil',
                                2 => 'Fácil',
                                3 => 'Médio',
                                4 => 'Difícil',
                                5 => 'Muito Difícil',
                            ]),
                        Forms\Components\TextInput::make('duration_in_minutes')
                            ->label('Duração (minutos)')
                            ->numeric(),
                        Forms\Components\Select::make('estimated_effort')
                            ->label('Esforço Estimado')
                            ->options([
                                'low' => 'Baixo',
                                'medium' => 'Médio',
                                'high' => 'Alto',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('position')
                            ->label('Posição')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Configurações Avançadas')
                    ->schema([
                        Forms\Components\Toggle::make('is_premium')
                            ->label('É Premium'),
                        Forms\Components\Select::make('main_technology_id')
                            ->label('Tecnologia Principal')
                            ->relationship('mainTechnology', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('instructor_id')
                            ->label('Instrutor')
                            ->relationship('instructor', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('featured')
                            ->label('Destaque')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Datas Especiais')
                    ->schema([
                        Forms\Components\DateTimePicker::make('weekly_featured_start_date')
                            ->label('Início do Destaque Semanal'),
                        Forms\Components\DateTimePicker::make('solution_publish_date')
                            ->label('Data de Publicação da Solução'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_url'),
                Tables\Columns\TextColumn::make('video_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('difficulty'),
                Tables\Columns\TextColumn::make('duration_in_minutes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('repository_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('featured')
                    ->searchable(),
                Tables\Columns\TextColumn::make('main_technology_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean(),
                Tables\Columns\TextColumn::make('instructor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('estimated_effort'),
                Tables\Columns\TextColumn::make('position')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weekly_featured_start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('solution_publish_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}
