<?php

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Who got in touch')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->copyable()
                            ->url(fn ($record): string => 'mailto:'.$record->email),
                        TextEntry::make('company')
                            ->placeholder('Not given'),
                        TextEntry::make('phone')
                            ->placeholder('Not given')
                            ->copyable(),
                    ]),
                Section::make('The enquiry')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('service_interest')
                            ->label('Interested in')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Received')
                            ->dateTime(),
                        TextEntry::make('message')
                            ->prose()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
