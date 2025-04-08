<?php

namespace App\Filament\Owner\Resources\BusinessResource\Pages;

use App\Filament\Owner\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
