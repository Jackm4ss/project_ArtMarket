<?php

namespace App\Filament\Resources\SellerAdResource\Pages;

use App\Filament\Resources\SellerAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSellerAd extends EditRecord
{
    protected static string $resource = SellerAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
