<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\FeedbackResource\Pages;
use App\Filament\Owner\Resources\FeedbackResource\RelationManagers;
use App\Models\Business;
use App\Models\Feedback;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section::make('feedback')
                //     ->schema([
                //         Hidden::make('owner_id')->default(auth()->user()->id),
                //         Select::make('business_id')
                //             ->relationship('business','name')
                //             ->label('Бизнесы')
                //             ->searchable(),
                //         Repeater::make('question')
                //             ->schema([
                //             Select::make('review_question_id')
                //                 ->required()
                //                 ->numeric(),
                //             Forms\Components\TextInput::make('rating')
                //                 ->required()
                //                 ->numeric(),
                //             ])
                //     ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Feedback::query()
                    ->where('owner_id',auth()->user()->id) // 🔥 `avg_rating` ni hisoblaymiz
            )
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Бизнес')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('reviewQuestion.question')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('feedback_details')
                //     ->label('Средний рейтинг')
                //     ->formatStateUsing(function ($state) {
                //         // Check if there's an average rating available
                //         return $state->avg('rating');  // 2 decimal places
                //     })
                //     ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментария')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id','desc')
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Бизнесы')
                    ->searchable()
                    ->options(fn () => Business::all()->pluck('name', 'id')->map(fn ($name) => $name))
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('created_from')
                                    ->label('Время начала')->columnSpan(1)
                                    ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                                Forms\Components\DatePicker::make('created_until')
                                    ->label('Время окончания')->columnSpan(span: 1)
                                    ->placeholder(fn ($state): string => now()->format('M d, Y')),
                            ])
                    ])->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Отзыв от ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Отзыв до  ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                
                // SelectFilter::make('rating')
                //     ->label('Рейтинг')
                //     ->searchable()
                //     ->options([
                //         '1'=>1,
                //         '2'=>2,
                //         '3'=>3,
                //         '4'=>4,
                //         '5'=>5,
                //     ])
                //     ->preload(),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
            ])
            ->groups([
                Tables\Grouping\Group::make('business.name')
                    ->label('Бизнес')
                    ->collapsible(),
                Tables\Grouping\Group::make('created_at')
                    ->label('Дата создание')
                    ->date()
                    ->collapsible(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function getNavigationLabel(): string
    {
        return 'Отзывы '; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Отзыв'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Отзывы '; // Rus tilidagi ko'plik shakli
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
