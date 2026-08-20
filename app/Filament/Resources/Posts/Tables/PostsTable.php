<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->label('')
                    ->collection('featured_image')
                    ->conversion('card'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->placeholder('Uncategorised'),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->placeholder('Unattributed')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('j M Y, H:i')
                    ->sortable()
                    ->placeholder('Not published'),

                TextColumn::make('updated_at')
                    ->dateTime('j M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PostStatus::class),

                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            // Newest first, and posts without a publish date sort to the top so
            // unfinished drafts stay in view.
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
