<?php

namespace App\Filament\Owner\Resources;

use App\Filament\Owner\Resources\BusinessResource\Pages;
use App\Filament\Owner\Resources\BusinessResource\RelationManagers;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('owner_id')->default(auth()->user()->id),
                        FileUpload::make('logo')
                                ->label('Лого')
                                ->image()
                                ->disk('public') 
                                ->directory('logos')
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ])
                                ->columnSpan(12),
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                                ->columnSpan(12),
                        TextInput::make('token')
                            ->label('Токен')
                            ->nullable()
                            ->maxLength(255)
                                ->columnSpan(6),
                        TextInput::make('chat_id')
                            ->label('Чат Ид')
                            ->nullable()
                            ->maxLength(255)
                                ->columnSpan(6),
                    ])->columnSpan(6)->columns(12),
                    Section::make()
                        ->schema([
                            Repeater::make('reviewQuestions')
                                ->label('Вопросы')
                                ->relationship('reviewQuestions')
                                ->schema([
                                    Hidden::make('owner_id')->default(auth()->user()->id),
                                    Textarea::make('question')
                                        ->label('Вопрос')
                                        ->required()
                                        ->columnSpan(12)
                                ])->columnSpan(12)
                        ])->columnSpan(6)->columns(12)
            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Лого')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->sortable()    
                    ->searchable(),
                TextColumn::make('qr_code')
                    ->label('QR KOD')
                    ->html()
                    ->formatStateUsing(function ($state, $record) {
                        $url = "https://edisonnukus.uz/feedback/{$record->slug}"; // yoki $record->name
                        $qrPng = base64_encode(Qrcode::format('png')->size(200)->generate($url));
                        return "<img src='data:image/png;base64,{$qrPng}' />";
                    }),
                Tables\Columns\IconColumn::make('status')
                    ->label('Статус')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Время создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->actions([
                Action::make('download_qr')
                    ->label('QR yuklash')
                    ->icon('heroicon-m-arrow-down-tray')
                    // ->url(fn ($record) => $record->qr_code_path ? Storage::disk('public')->url($record->qr_code_path) : '#', true) // true – bu yuklab olish uchun
                    ->url(fn ($record) => route('download.qr', $record->id)) // routing orqali yuklab olish
                    ->color('primary')
                    ->tooltip('QR kodni yuklab olish')
                    ->visible(fn ($record) => filled($record->qr_code_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function canCreate(): bool
    {
        return Business::where('owner_id',auth()->user()->id)->count() < 11;
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Бизнесы'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Бизнес'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Бизнесы'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
