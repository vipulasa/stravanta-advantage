<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('Article')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    // The slug is part of a published URL, so it
                                    // is only auto-filled while drafting. On an
                                    // existing post the author edits it
                                    // deliberately or not at all.
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug((string) $state));
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Appears in the article URL: /blog/your-slug'),

                                Textarea::make('excerpt')
                                    ->rows(3)
                                    ->maxLength(300)
                                    ->helperText('Shown on the blog listing and used as the fallback meta description.'),

                                RichEditor::make('body')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Publishing')
                                    ->schema([
                                        Select::make('status')
                                            ->options(PostStatus::class)
                                            ->default(PostStatus::Draft)
                                            ->selectablePlaceholder(false)
                                            ->required()
                                            ->live(),

                                        DateTimePicker::make('published_at')
                                            ->label('Publish at')
                                            ->seconds(false)
                                            ->default(now())
                                            // A future date schedules the post:
                                            // Post::scopePublished() will not
                                            // surface it until the time passes.
                                            ->required(fn (Get $get): bool => self::isPublishing($get('status'))),

                                        Select::make('category_id')
                                            ->label('Category')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload(),

                                        Select::make('user_id')
                                            ->label('Author')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(auth()->id()),
                                    ]),

                                Section::make('Featured image')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('featured_image')
                                            ->hiddenLabel()
                                            ->collection('featured_image')
                                            ->image()
                                            ->imageEditor()
                                            ->conversion('card')
                                            ->helperText('Used on the blog listing and at the top of the article.'),
                                    ]),

                                Section::make('Search engines')
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->maxLength(255)
                                            ->placeholder('Defaults to the article title'),

                                        Textarea::make('meta_description')
                                            ->rows(3)
                                            ->maxLength(255)
                                            ->placeholder('Defaults to the excerpt'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * Determine whether the form is currently set to publish.
     *
     * `Get` resolves the status through the model's cast, so it returns a
     * PostStatus instance rather than the raw string the select submits.
     * Comparing against the string alone silently never matched, which left the
     * publish date optional and produced posts that could never go live.
     */
    private static function isPublishing(mixed $status): bool
    {
        if ($status instanceof PostStatus) {
            return $status === PostStatus::Published;
        }

        return is_string($status) && PostStatus::tryFrom($status) === PostStatus::Published;
    }
}
