<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('Personal Information'))
                            ->description(__('Basic user personal details'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Full Name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('email')
                                    ->label(__('Email Address'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label(__('Password'))
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->helperText(__('Minimum 8 characters'))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null),

                                PhoneInput::make('phone')
                                    ->label(__('Phone'))
                                    ->placeholder(__('Enter phone number'))
                                    ->helperText(__('Enter phone number with country code'))
                                    ->prefixIcon('heroicon-o-phone')
                                    ->unique(\App\Models\User::class, 'phone', ignoreRecord: true)
                                    ->columnSpanFull()
                                    ->countryStatePath('country_code')
                                    ->defaultCountry('IQ') // Default to Iraq
                                    ->displayNumberFormat(PhoneInputNumberType::NATIONAL)
                                    ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL),

                                Select::make('locale')
                                    ->label(__('Language'))
                                    ->options([
                                        'en' => __('English'),
                                        'ar' => __('Arabic'),
                                        'ku' => __('Kurdish'),
                                    ])
                                    ->default('en')
                                    ->helperText(__('User preferred language')),

                                SpatieMediaLibraryFileUpload::make('cover')
                                    ->label(__('Cover Photo'))
                                    ->helperText(__('Upload a cover photo for this user'))
                                    ->collection('cover')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(5120) // 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make(__('Roles & Permissions'))
                            ->description(__('User roles and access control'))
                            ->schema([
                                CheckboxList::make('roles')
                                    ->label(__('Roles'))
                                    ->helperText(__('Select roles for this user'))
                                    ->relationship('roles', 'name')
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Status'))
                            ->description(__('User account status'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('Active'))
                                    ->default(true)
                                    ->helperText(__('Inactive users cannot login'))
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}
