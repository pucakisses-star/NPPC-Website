<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AboutSettings extends Page implements HasForms {
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'About Page';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.homepage-settings';

    public ?array $data = [];

    private const SETTINGS_KEYS = [
        'about_spotlight_image',
        'about_spotlight_brightness',
        'about_spotlight_enabled',
    ];

    public function mount(): void {
        $settings = [];
        foreach (self::SETTINGS_KEYS as $key) {
            $settings[$key] = SiteSetting::get($key);
        }

        $settings['about_spotlight_enabled'] ??= '1';
        $settings['about_spotlight_brightness'] ??= '60';

        $this->form->fill($settings);
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Searchlight Image')
                    ->description('A large image at the bottom of the about page with a searchlight hover effect. The image stays dark until the user hovers, revealing the image under the cursor like a spotlight.')
                    ->schema([
                        Forms\Components\Toggle::make('about_spotlight_enabled')
                            ->label('Enable Searchlight Section')
                            ->default(true),
                        Forms\Components\FileUpload::make('about_spotlight_image')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('about')
                            ->helperText('Upload a large, high-resolution image. It will appear dark until the user hovers.'),
                        Forms\Components\Select::make('about_spotlight_brightness')
                            ->label('Searchlight Brightness')
                            ->options([
                                '30' => 'Very Dim',
                                '40' => 'Dim',
                                '50' => 'Medium-Low',
                                '60' => 'Medium (default)',
                                '70' => 'Medium-High',
                                '80' => 'Bright',
                                '90' => 'Very Bright',
                                '100' => 'Full Brightness',
                            ])
                            ->default('60')
                            ->helperText('How bright the image appears under the searchlight cursor'),
                    ]),
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
            ->title('About page settings saved')
            ->success()
            ->send();
    }

    public function getTitle(): string {
        return 'About Page Settings';
    }
}
