<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomepageSettings extends Page implements HasForms {
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Homepage';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.homepage-settings';

    public ?array $data = [];

    private const SETTINGS_KEYS = [
        // Hero
        'hero_video_mp4',
        'hero_video_webm',
        'hero_headline',
        'hero_subheadline',
        'hero_headline_size',
        'hero_subheadline_size',
        'hero_height',
        'hero_overlay_opacity',
        // Impact Stats
        'stats_enabled',
        'stats_heading',
        'stats_bg_image',
        'stats_donate_label',
        'stat_1_value',
        'stat_1_text',
        'stat_2_value',
        'stat_2_text',
        'stat_3_value',
        'stat_3_text',
        // Articles
        'articles_enabled',
        'articles_limit',
        // Callout
        'callout_enabled',
        'callout_heading',
        'callout_text',
        'callout_button_label',
        'callout_button_url',
        // Visualisation
        'visualisation_enabled',
        // Quotes
        'quotes_enabled',
        // Gallery
        'gallery_enabled',
    ];

    public function mount(): void {
        $settings = [];
        foreach (self::SETTINGS_KEYS as $key) {
            $settings[$key] = SiteSetting::get($key);
        }

        // Defaults
        $settings['hero_headline'] ??= 'Justice';
        $settings['hero_subheadline'] ??= 'No matter what';
        $settings['hero_headline_size'] ??= '8';
        $settings['hero_subheadline_size'] ??= '5';
        $settings['hero_height'] ??= '100';
        $settings['hero_overlay_opacity'] ??= '30';
        $settings['stats_enabled'] ??= '1';
        $settings['stats_heading'] ??= 'Fighting for Justice';
        $settings['stats_donate_label'] ??= 'Donate';
        $settings['stat_1_value'] ??= '150+';
        $settings['stat_1_text'] ??= 'Political prisoners documented across the United States';
        $settings['stat_2_value'] ??= '40+';
        $settings['stat_2_text'] ??= 'Years served by the longest-held political prisoner';
        $settings['stat_3_value'] ??= '26';
        $settings['stat_3_text'] ??= 'States with documented cases of political imprisonment';
        $settings['articles_enabled'] ??= '1';
        $settings['articles_limit'] ??= '5';
        $settings['callout_enabled'] ??= '1';
        $settings['callout_heading'] ??= 'Support the National Political Prisoner Coalition';
        $settings['callout_text'] ??= "Your contributions directly help us provide vital support to political prisoners across the United States. With your donation, we can continue to fight for justice, provide legal aid, and assist families in need.\n\nJoin us in making a difference. Every donation, no matter the size, brings us closer to achieving our mission.";
        $settings['callout_button_label'] ??= 'Donate now';
        $settings['callout_button_url'] ??= '/donate';
        $settings['visualisation_enabled'] ??= '1';
        $settings['quotes_enabled'] ??= '1';
        $settings['gallery_enabled'] ??= '1';

        $this->form->fill($settings);
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Homepage')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Hero Section')
                            ->icon('heroicon-o-play')
                            ->schema([
                                Forms\Components\Section::make('Background Video')
                                    ->description('The full-screen video that plays in the hero section. Upload MP4 and/or WebM formats.')
                                    ->schema([
                                        Forms\Components\FileUpload::make('hero_video_mp4')
                                            ->label('Video (MP4)')
                                            ->disk('public')
                                            ->directory('videos/home')
                                            ->acceptedFileTypes(['video/mp4'])
                                            ->helperText('Primary video format. Leave empty to keep the current video.'),
                                        Forms\Components\FileUpload::make('hero_video_webm')
                                            ->label('Video (WebM)')
                                            ->disk('public')
                                            ->directory('videos/home')
                                            ->acceptedFileTypes(['video/webm'])
                                            ->helperText('Fallback format for browsers that don\'t support MP4.'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Hero Text')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_headline')
                                            ->label('Main Headline')
                                            ->maxLength(100)
                                            ->helperText('Large text at bottom-left of the hero (e.g. "Justice")'),
                                        Forms\Components\TextInput::make('hero_subheadline')
                                            ->label('Sub-headline')
                                            ->maxLength(200)
                                            ->helperText('Smaller text below the headline (e.g. "No matter what")'),
                                        Forms\Components\TextInput::make('hero_headline_size')
                                            ->label('Headline Size (rem)')
                                            ->numeric()
                                            ->step(0.5)
                                            ->minValue(2)
                                            ->maxValue(20)
                                            ->suffix('rem')
                                            ->default(8)
                                            ->helperText('Font size for the main headline. Default is 8rem.'),
                                        Forms\Components\TextInput::make('hero_subheadline_size')
                                            ->label('Sub-headline Size (rem)')
                                            ->numeric()
                                            ->step(0.5)
                                            ->minValue(1)
                                            ->maxValue(15)
                                            ->suffix('rem')
                                            ->default(5)
                                            ->helperText('Font size for the sub-headline. Default is 5rem.'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Hero Appearance')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_height')
                                            ->label('Hero Height (vh)')
                                            ->numeric()
                                            ->minValue(20)
                                            ->maxValue(100)
                                            ->suffix('vh')
                                            ->default(100)
                                            ->helperText('Percentage of screen height. 100 = full screen, 50 = half screen. Try 60-70 for a shorter hero.')
                                            ->live()
                                            ->afterStateUpdated(fn () => null),
                                        Forms\Components\Select::make('hero_overlay_opacity')
                                            ->label('Dark Overlay Opacity')
                                            ->options([
                                                '0'  => 'None',
                                                '10' => '10%',
                                                '20' => '20%',
                                                '30' => '30% (default)',
                                                '40' => '40%',
                                                '50' => '50%',
                                                '60' => '60%',
                                                '70' => '70%',
                                            ])
                                            ->default('30')
                                            ->helperText('Controls how dark the overlay on the video appears'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Impact Stats')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Toggle::make('stats_enabled')
                                    ->label('Show Impact Stats section')
                                    ->default(true),

                                Forms\Components\TextInput::make('stats_heading')
                                    ->label('Section Heading')
                                    ->maxLength(100),

                                Forms\Components\FileUpload::make('stats_bg_image')
                                    ->label('Background Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('images')
                                    ->helperText('Background image for the impact stats section'),

                                Forms\Components\TextInput::make('stats_donate_label')
                                    ->label('Donate Button Text')
                                    ->maxLength(50),

                                Forms\Components\Section::make('Stat #1')
                                    ->schema([
                                        Forms\Components\TextInput::make('stat_1_value')
                                            ->label('Value')
                                            ->maxLength(20)
                                            ->helperText('e.g. "150+"'),
                                        Forms\Components\TextInput::make('stat_1_text')
                                            ->label('Description')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Stat #2')
                                    ->schema([
                                        Forms\Components\TextInput::make('stat_2_value')
                                            ->label('Value')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('stat_2_text')
                                            ->label('Description')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Stat #3')
                                    ->schema([
                                        Forms\Components\TextInput::make('stat_3_value')
                                            ->label('Value')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('stat_3_text')
                                            ->label('Description')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Articles')
                            ->icon('heroicon-o-newspaper')
                            ->schema([
                                Forms\Components\Toggle::make('articles_enabled')
                                    ->label('Show Articles section')
                                    ->default(true),
                                Forms\Components\TextInput::make('articles_limit')
                                    ->label('Number of articles to display')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(5),
                            ]),

                        Forms\Components\Tabs\Tab::make('Donation Callout')
                            ->icon('heroicon-o-heart')
                            ->schema([
                                Forms\Components\Toggle::make('callout_enabled')
                                    ->label('Show Donation Callout section')
                                    ->default(true),
                                Forms\Components\TextInput::make('callout_heading')
                                    ->label('Heading')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('callout_text')
                                    ->label('Body Text')
                                    ->rows(4)
                                    ->helperText('Use a blank line to separate paragraphs'),
                                Forms\Components\TextInput::make('callout_button_label')
                                    ->label('Button Text')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('callout_button_url')
                                    ->label('Button URL')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Tabs\Tab::make('Other Sections')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Forms\Components\Toggle::make('visualisation_enabled')
                                    ->label('Show Prisoner Statistics Visualisation')
                                    ->helperText('The interactive charts showing prisoner demographics by race, gender, era, etc.')
                                    ->default(true),
                                Forms\Components\Toggle::make('quotes_enabled')
                                    ->label('Show Quotes Carousel')
                                    ->helperText('Rotating testimonials from the Quotes database')
                                    ->default(true),
                                Forms\Components\Toggle::make('gallery_enabled')
                                    ->label('Show Prisoner Photo Gallery')
                                    ->helperText('Scrolling photo carousel at the bottom of the page')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            SiteSetting::set($key, $value);
        }

        Notification::make()
            ->title('Homepage settings saved')
            ->success()
            ->send();
    }
}
