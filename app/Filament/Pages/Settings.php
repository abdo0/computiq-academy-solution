<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.admin.pages.settings';

    public function getTitle(): string|Htmlable
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    protected static ?int $navigationSort = 4;

    public Authenticatable $user;

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        // Fields that should be decoded as JSON arrays
        $jsonFields = [
            'app_locales',           // TagsInput - array
            'company_name',          // Translate - multilingual object
            'company_slogan',        // Translate - multilingual object
            'company_description',   // Translate - multilingual object
            'meta_title',            // Translate - multilingual object
            'meta_description',      // Translate - multilingual object
            'meta_keywords',         // Translate - multilingual object
        ];

        // Get all settings with selective JSON handling
        $data = [];
        foreach (Setting::all() as $setting) {
            $value = $setting->value;

            // Only decode JSON for fields that should be arrays/objects
            if (in_array($setting->key, $jsonFields) && is_string($value) && $this->isJson($value)) {
                $value = json_decode($value, true);
            }

            $data[$setting->key] = $value;
        }

        $this->callHook('beforeFill');

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    // Helper function to check if a string is valid JSON
    private function isJson($string): bool
    {
        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('General')->id('general')->label(__('General'))->icon('heroicon-o-cog-6-tooth')->schema($this->getGeneralSchema()),
                        Tab::make('Company')->id('company')->label(__('Company Info'))->icon('heroicon-o-building-office')->schema($this->getCompanySchema()),
                        Tab::make('Frontend')->id('frontend')->label(__('Frontend'))->icon('heroicon-o-globe-alt')->schema($this->getFrontendSchema()),

                        Tab::make('SEO')->id('seo-flat')->label(__('SEO'))->icon('heroicon-o-magnifying-glass')->schema($this->getSeoFlatSchema()),
                        Tab::make('Contact')->id('contact')->label(__('Contact'))->icon('heroicon-o-phone')->schema($this->getContactSchema()),
                        Tab::make('Location')->id('location')->label(__('Location'))->icon('heroicon-o-map-pin')->schema($this->getLocationSchema()),
                        Tab::make('Social')->id('social')->label(__('Social Media'))->icon('heroicon-o-share')->schema($this->getSocialSchema()),
                        Tab::make('Email')->id('email')->label(__('Email'))->icon('heroicon-o-envelope')->schema($this->getEmailSchema()),
                        Tab::make('WhatsApp')->id('whatsapp')->label(__('WhatsApp Integration'))->icon('heroicon-o-chat-bubble-left-right')->schema($this->getWhatsAppSettingsSchema()),
                    ])->persistTab(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    private function getGeneralSchema(): array
    {
        return [
            Section::make(__('Application Settings'))
                ->schema([
                    TextInput::make('app_name')
                        ->label(__('Application Name'))
                        ->required()
                        ->maxLength(255),

                    Textarea::make('app_description')
                        ->label(__('Description'))
                        ->rows(2)
                        ->maxLength(500),

                    \Filament\Forms\Components\TagsInput::make('app_locales')
                        ->label(__('Available Languages'))
                        ->helperText(__('Comma-separated: en,ar,ku'))
                        ->default(['en', 'ar', 'ku']),

                    Select::make('default_locale')
                        ->label(__('Default Language'))
                        ->options([
                            'en' => __('English'),
                            'ar' => __('Arabic').' (العربية)',
                            'ku' => __('Kurdish').' (کوردی)',
                        ])
                        ->default('en')
                        ->required(),

                    Select::make('timezone')
                        ->label(__('Timezone'))
                        ->options(collect(\timezone_identifiers_list())->mapWithKeys(function ($timezone) {
                            return [$timezone => $timezone];
                        })->toArray())
                        ->searchable()
                        ->default('Asia/Baghdad'),

                    \Filament\Forms\Components\Placeholder::make('currency')
                        ->label(__('Default Currency'))
                        ->content(fn () => \App\Models\Currency::getDefault()?->name.' ('.(\App\Models\Currency::getDefault()?->code ?? 'USD').')' ?? __('Not Set'))
                        ->helperText(__('Manage currencies and set the default currency from the Currencies resource in the Entities cluster.')),

                ])->columns(2),

            Section::make(__('Refund Settings'))
                ->schema([
                    TextInput::make('refund_max_days')
                        ->label(__('Maximum Days for Refund Request'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(365)
                        ->helperText(__('Number of days after payment completion during which a refund can be requested. Set to 0 to allow refunds at any time. After this period, refund requests will be automatically rejected.'))
                        ->suffix(__('days')),
                ])->columns(1),
        ];
    }

    private function getFrontendSchema(): array
    {
        return [
            Section::make(__('Site Identity'))
                ->description(__('Site name shown in the header, footer, and browser tab'))
                ->schema([
                    TextInput::make('site_name_ar')
                        ->label(__('Site Name (Arabic)'))
                        ->maxLength(255),
                    TextInput::make('site_name_en')
                        ->label(__('Site Name (English)'))
                        ->maxLength(255),
                    TextInput::make('site_name_ku')
                        ->label(__('Site Name (Kurdish)'))
                        ->maxLength(255),
                ])->columns(3),

            Section::make(__('Footer Description'))
                ->description(__('Short description displayed in the website footer'))
                ->schema([
                    Textarea::make('footer_desc_ar')
                        ->label(__('Footer Description (Arabic)'))
                        ->rows(2)
                        ->maxLength(500),
                    Textarea::make('footer_desc_en')
                        ->label(__('Footer Description (English)'))
                        ->rows(2)
                        ->maxLength(500),
                    Textarea::make('footer_desc_ku')
                        ->label(__('Footer Description (Kurdish)'))
                        ->rows(2)
                        ->maxLength(500),
                ])->columns(3),

            Section::make(__('Multilingual Address'))
                ->description(__('Address displayed in the footer and contact page'))
                ->schema([
                    TextInput::make('address_ar')
                        ->label(__('Address (Arabic)'))
                        ->maxLength(500),
                    TextInput::make('address_en')
                        ->label(__('Address (English)'))
                        ->maxLength(500),
                    TextInput::make('address_ku')
                        ->label(__('Address (Kurdish)'))
                        ->maxLength(500),
                ])->columns(3),

            Section::make(__('Contact Phones (Frontend)'))
                ->description(__('Phone numbers shown on the public website'))
                ->schema([
                    TextInput::make('contact_phone_1')
                        ->label(__('Primary Phone'))
                        ->tel()
                        ->maxLength(50),
                    TextInput::make('contact_phone_2')
                        ->label(__('Secondary Phone'))
                        ->tel()
                        ->maxLength(50),
                ])->columns(2),
        ];
    }



    private function getSeoFlatSchema(): array
    {
        return [
            Section::make(__('Default SEO Titles'))
                ->description(__('Default page title used across the website for each language'))
                ->schema([
                    TextInput::make('seo_title_ar')
                        ->label(__('SEO Title (Arabic)'))
                        ->maxLength(255),
                    TextInput::make('seo_title_en')
                        ->label(__('SEO Title (English)'))
                        ->maxLength(255),
                    TextInput::make('seo_title_ku')
                        ->label(__('SEO Title (Kurdish)'))
                        ->maxLength(255),
                ])->columns(3),

            Section::make(__('Default SEO Descriptions'))
                ->description(__('Default meta description for search engines'))
                ->schema([
                    Textarea::make('seo_description_ar')
                        ->label(__('SEO Description (Arabic)'))
                        ->rows(2)
                        ->maxLength(500),
                    Textarea::make('seo_description_en')
                        ->label(__('SEO Description (English)'))
                        ->rows(2)
                        ->maxLength(500),
                    Textarea::make('seo_description_ku')
                        ->label(__('SEO Description (Kurdish)'))
                        ->rows(2)
                        ->maxLength(500),
                ])->columns(3),

            Section::make(__('Default SEO Keywords'))
                ->description(__('Comma-separated keywords for search engine optimization'))
                ->schema([
                    TextInput::make('seo_keywords_ar')
                        ->label(__('Keywords (Arabic)'))
                        ->maxLength(500),
                    TextInput::make('seo_keywords_en')
                        ->label(__('Keywords (English)'))
                        ->maxLength(500),
                    TextInput::make('seo_keywords_ku')
                        ->label(__('Keywords (Kurdish)'))
                        ->maxLength(500),
                ])->columns(3),

            Section::make(__('OG Image'))
                ->description(__('Default Open Graph image for social media sharing'))
                ->schema([
                    FileUpload::make('seo_og_image')
                        ->label(__('OG Image'))
                        ->image()
                        ->disk('public')
                        ->directory('seo')
                        ->helperText(__('Recommended: 1200×630 pixels'))
                        ->columnSpanFull(),
                ]),
        ];
    }

    private function getCompanySchema(): array
    {
        return [
            Section::make(__('Company Information'))
                ->schema([
                    Translate::make()
                        ->schema([
                            TextInput::make('company_name')
                                ->label(__('Company Name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('company_slogan')
                                ->label(__('Slogan/Tagline'))
                                ->maxLength(255),

                            Textarea::make('company_description')
                                ->label(__('Company Description'))
                                ->rows(4)
                                ->maxLength(1000)
                                ->columnSpanFull(),
                        ])
                        ->locales(appLocales())
                        ->columnSpanFull(),

                    TextInput::make('company_registration_no')
                        ->label(__('Registration Number'))
                        ->maxLength(100),

                    TextInput::make('company_tax_id')
                        ->label(__('Tax ID'))
                        ->maxLength(100),

                    TextInput::make('company_founded_year')
                        ->label(__('Founded Year'))
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(\date('Y')),
                ])->columns(2),

            Section::make(__('Logo & Branding'))
                ->schema([
                    FileUpload::make('company_logo')
                        ->label(__('Company Logo'))
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('logos')
                        ->helperText(__('Recommended size: 200x60 pixels')),

                    FileUpload::make('company_favicon')
                        ->label(__('Favicon'))
                        ->image()
                        ->disk('public')
                        ->directory('logos')
                        ->helperText(__('Recommended size: 32x32 pixels')),

                    Select::make('logo_display_mode')
                        ->label(__('Logo Display Mode'))
                        ->options([
                            'logo_only' => __('Logo Only'),
                            'text_only' => __('Text Only'),
                            'logo_with_text' => __('Logo with Text'),
                        ])
                        ->default('logo_with_text')
                        ->required()
                        ->helperText(__('Choose how the logo should be displayed in the sidebar'))
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }

    private function getContactSchema(): array
    {
        return [
            Section::make(__('Contact Information'))
                ->schema([
                    TextInput::make('contact_email')
                        ->label(__('Primary Email'))
                        ->email()
                        ->required()
                        ->maxLength(255),

                    TextInput::make('contact_phone')
                        ->label(__('Primary Phone'))
                        ->tel()
                        ->required()
                        ->maxLength(50),

                    TextInput::make('contact_phone_secondary')
                        ->label(__('Secondary Phone'))
                        ->tel()
                        ->maxLength(50),

                    TextInput::make('contact_whatsapp')
                        ->label(__('WhatsApp Number'))
                        ->tel()
                        ->maxLength(50)
                        ->helperText(__('Include country code: +964 XXX XXX XXXX')),

                    TextInput::make('contact_fax')
                        ->label(__('Fax Number'))
                        ->tel()
                        ->maxLength(50),
                ])->columns(2),

            Section::make(__('Business Hours'))
                ->schema([
                    TextInput::make('business_hours_weekdays')
                        ->label(__('Weekdays Hours'))
                        ->default('9:00 AM - 6:00 PM')
                        ->helperText(__('Sunday to Thursday')),

                    TextInput::make('business_hours_weekend')
                        ->label(__('Weekend Hours'))
                        ->default('Closed')
                        ->helperText(__('Friday & Saturday')),

                    TextInput::make('business_days')
                        ->label(__('Working Days'))
                        ->default('Sunday,Monday,Tuesday,Wednesday,Thursday')
                        ->helperText(__('Comma-separated list'))
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }

    private function getLocationSchema(): array
    {
        return [
            Section::make(__('Address'))
                ->schema([
                    Textarea::make('location_address')
                        ->label(__('Street Address'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    TextInput::make('location_city')
                        ->label(__('City'))
                        ->required()
                        ->maxLength(100),

                    TextInput::make('location_state')
                        ->label(__('State/Province'))
                        ->maxLength(100),

                    TextInput::make('location_country')
                        ->label(__('Country'))
                        ->default('Iraq')
                        ->maxLength(100),

                    TextInput::make('location_postal_code')
                        ->label(__('Postal Code'))
                        ->maxLength(20),
                ])->columns(2),

            Section::make(__('Map Coordinates'))
                ->schema([

                    TextInput::make('location_map_url')
                        ->label(__('Google Maps URL'))
                        ->url()
                        ->maxLength(500)
                        ->helperText(__('Direct link to Google Maps'))
                        ->columnSpanFull(),

                    Textarea::make('location_map_embed')
                        ->label(__('Map Embed Code'))
                        ->rows(3)
                        ->helperText(__('Google Maps iframe embed code'))
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }

    private function getSocialSchema(): array
    {
        return [
            Section::make(__('Social Media Links'))
                ->schema([
                    TextInput::make('social_website')
                        ->label(__('Website'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-globe-alt'),

                    TextInput::make('social_facebook')
                        ->label(__('Facebook'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-share')
                        ->helperText(__('https://facebook.com/yourpage')),

                    TextInput::make('social_instagram')
                        ->label(__('Instagram'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-camera')
                        ->helperText(__('https://instagram.com/yourprofile')),

                    TextInput::make('social_twitter')
                        ->label(__('Twitter / X'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-chat-bubble-left')
                        ->helperText(__('https://twitter.com/yourprofile')),

                    TextInput::make('social_linkedin')
                        ->label(__('LinkedIn'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-user-group')
                        ->helperText(__('https://linkedin.com/company/yourcompany')),

                    TextInput::make('social_youtube')
                        ->label(__('YouTube'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-video-camera')
                        ->helperText(__('https://youtube.com/@yourchannel')),

                    TextInput::make('social_tiktok')
                        ->label(__('TikTok'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-musical-note')
                        ->helperText(__('https://tiktok.com/@yourprofile')),
                ])->columns(2),

            Section::make(__('SEO & Meta Tags'))
                ->schema([
                    Translate::make()
                        ->schema([
                            TextInput::make('meta_title')
                                ->label(__('Meta Title'))
                                ->maxLength(255)
                                ->helperText(__('For search engines'))
                                ->columnSpanFull(),

                            Textarea::make('meta_description')
                                ->label(__('Meta Description'))
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText(__('Brief description for search engines'))
                                ->columnSpanFull(),

                            Textarea::make('meta_keywords')
                                ->label(__('Meta Keywords'))
                                ->rows(2)
                                ->maxLength(500)
                                ->helperText(__('Comma-separated keywords for SEO'))
                                ->columnSpanFull(),
                        ])
                        ->locales(appLocales())
                        ->columnSpanFull(),
                ])->columns(1),
        ];
    }

    private function getEmailSchema(): array
    {
        return [
            Section::make(__('Email Configuration'))
                ->schema([
                    TextInput::make('email_from_address')
                        ->label(__('From Email Address'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Default sender email address')),

                    TextInput::make('email_from_name')
                        ->label(__('From Name'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Default sender name')),

                    Textarea::make('email_signature')
                        ->label(__('Email Signature'))
                        ->rows(4)
                        ->helperText(__('HTML allowed - appears at bottom of emails'))
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make(__('SMTP Settings'))
                ->schema([
                    TextInput::make('mail_mailer')
                        ->label(__('Mail Driver'))
                        ->default('smtp')
                        ->helperText(__('Mail service driver (smtp, sendmail, mailgun)')),

                    TextInput::make('mail_host')
                        ->label(__('SMTP Host'))
                        ->placeholder('smtp.example.com')
                        ->helperText(__('Mail server hostname')),

                    TextInput::make('mail_port')
                        ->label(__('SMTP Port'))
                        ->numeric()
                        ->default(587)
                        ->helperText(__('587 for TLS, 465 for SSL')),

                    Select::make('mail_encryption')
                        ->label(__('Encryption'))
                        ->options([
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                            'none' => __('None'),
                        ])
                        ->default('tls'),

                    TextInput::make('mail_username')
                        ->label(__('Username'))
                        ->helperText(__('SMTP username')),

                    TextInput::make('mail_password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => $state ?: null)
                        ->helperText(__('SMTP password')),
                ])->columns(2),
        ];
    }

    private function getWhatsAppSettingsSchema(): array
    {
        return [
            Section::make(__('UltraMsg Settings'))
                ->description(__('Configure the UltraMsg WhatsApp integration settings'))
                ->schema([
                    TextInput::make('ultramsg_instance_id')
                        ->label(__('UltraMsg Instance ID'))
                        ->default(config('ultramsg.default_instance_id'))
                        ->helperText(__('Leave empty to use the global default instance ID.')),
                    TextInput::make('ultramsg_token')
                        ->label(__('UltraMsg Token'))
                        ->password()
                        ->revealable()
                        ->default(config('ultramsg.default_token'))
                        ->helperText(__('Leave empty to use the global default token.')),
                    TextInput::make('ultramsg_test_phone_number')
                        ->label(__('Test Phone Number'))
                        ->default(config('ultramsg.test_phone_number'))
                        ->helperText(__('Phone number used when UltraMsg is in test mode.')),
                    Toggle::make('ultramsg_test_mode')
                        ->label(__('Enable Test Mode'))
                        ->default(config('ultramsg.test_mode'))
                        ->helperText(__('When enabled, all WhatsApp messages are re-routed to the test phone number.')),
                    Toggle::make('ultramsg_log_requests')
                        ->label(__('Log Requests'))
                        ->default(config('ultramsg.log_requests'))
                        ->helperText(__('Enable to log every WhatsApp API request.')),
                    Toggle::make('ultramsg_log_responses')
                        ->label(__('Log Responses'))
                        ->default(config('ultramsg.log_responses'))
                        ->helperText(__('Enable to log every WhatsApp API response.')),
                ])
                ->columns(2),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Currency is now managed through the Currency resource, not settings
            // Remove currency from data if it exists
            if (isset($data['currency'])) {
                unset($data['currency']);
            }

            foreach ($data as $key => $value) {
                // Encode arrays as JSON, keep strings as-is
                $valueToSave = is_array($value) ? json_encode($value) : $value;

                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $valueToSave]
                );
            }

            // Clear settings cache
            cache()->forget('site_settings');

            Notification::make()
                ->title(__('Settings saved successfully'))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title(__('Failed to save settings'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        /** @var User $user */
        $user = Filament::getCurrentOrDefaultPanel()->auth()->user();

        return $user ? $user->can('access_settings') : false;
    }
}
