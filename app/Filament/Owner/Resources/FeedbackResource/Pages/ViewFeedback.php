<?php

namespace App\Filament\Owner\Resources\FeedbackResource\Pages;

use App\Filament\Owner\Resources\FeedbackResource;
use Filament\Actions;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;

class ViewFeedback extends ViewRecord
{
    protected static string $resource = FeedbackResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Orqaga')
                ->icon('heroicon-m-arrow-left')
                ->url(static::getResource()::getUrl()) // bu `index` page linki
                ->color('danger'),
        ];
    }
}
