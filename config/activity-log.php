<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Log Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all mappings and icons used by the
    | activity log system for displaying changes, relationships, and UI elements.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Action Colors and Icons
    |--------------------------------------------------------------------------
    |
    | Define colors and icons for different activity actions.
    |
    */
    'actions' => [
        'created' => [
            'color' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
            'icon' => 'heroicon-o-plus',
        ],
        'updated' => [
            'color' => 'bg-blue-100 dark:bg-blue-800 text-blue-800',
            'icon' => 'heroicon-o-pencil',
        ],
        'deleted' => [
            'color' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
            'icon' => 'heroicon-o-trash',
        ],
        'restored' => [
            'color' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
            'icon' => 'heroicon-o-arrow-uturn-left',
        ],
        'viewed' => [
            'color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
            'icon' => 'heroicon-o-eye',
        ],
        'exported' => [
            'color' => 'bg-purple-100 dark:bg-purple-800 text-purple-800 dark:text-purple-200',
            'icon' => 'heroicon-o-arrow-down-tray',
        ],
        'imported' => [
            'color' => 'bg-indigo-100 dark:bg-indigo-800 text-indigo-800 dark:text-indigo-200',
            'icon' => 'heroicon-o-arrow-up-tray',
        ],
        'approved' => [
            'color' => 'bg-emerald-100 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200',
            'icon' => 'heroicon-o-check-circle',
        ],
        'rejected' => [
            'color' => 'bg-rose-100 dark:bg-rose-800 text-rose-800 dark:text-rose-200',
            'icon' => 'heroicon-o-x-circle',
        ],
        'default' => [
            'color' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
            'icon' => 'heroicon-o-question-mark-circle',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Icons
    |--------------------------------------------------------------------------
    |
    | Define icons for different model types.
    |
    */
    'model_icons' => [
        'App\\Models\\User' => 'heroicon-o-users',
        'App\\Models\\Client' => 'heroicon-o-user-circle',
        'App\\Models\\Customer' => 'heroicon-o-users',
        'App\\Models\\Donor' => 'heroicon-o-user',
        'App\\Models\\Organization' => 'heroicon-o-building-office',
        'App\\Models\\Campaign' => 'heroicon-o-megaphone',
        'App\\Models\\Project' => 'heroicon-o-folder',
        'App\\Models\\Task' => 'heroicon-o-clipboard-document-list',
        'App\\Models\\Department' => 'heroicon-o-building-office',
        'App\\Models\\Branch' => 'heroicon-o-building-office-2',
        'App\\Models\\ActivityLog' => 'heroicon-o-clipboard-document-list',
        'App\\Models\\Setting' => 'heroicon-o-cog-6-tooth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Icons
    |--------------------------------------------------------------------------
    |
    | Define icons for different field types and names.
    |
    */
    'field_icons' => [
        // User related fields
        'user_id' => 'heroicon-o-users',
        'assigned_to' => 'heroicon-o-user-plus',

        // Organization fields
        'branch_id' => 'heroicon-o-building-office-2',
        'department_id' => 'heroicon-o-building-office',
        'organization_id' => 'heroicon-o-building-office',

        // Client fields
        'client_id' => 'heroicon-o-user-circle',
        'customer_id' => 'heroicon-o-users',
        'donor_id' => 'heroicon-o-user',

        // Campaign fields
        'campaign_id' => 'heroicon-o-megaphone',
        'project_id' => 'heroicon-o-folder',
        'task_id' => 'heroicon-o-clipboard-document-list',

        // Common fields
        'name' => 'heroicon-o-tag',
        'title' => 'heroicon-o-document-text',
        'description' => 'heroicon-o-document-text',
        'email' => 'heroicon-o-envelope',
        'phone' => 'heroicon-o-phone',
        'mobile' => 'heroicon-o-device-phone-mobile',
        'address' => 'heroicon-o-map-pin',
        'website' => 'heroicon-o-globe-alt',
        'is_active' => 'heroicon-o-check-circle',
        'status' => 'heroicon-o-check-circle',
        'notes' => 'heroicon-o-document-text',
        'date' => 'heroicon-o-calendar',
        'created_at' => 'heroicon-o-calendar',
        'updated_at' => 'heroicon-o-calendar',
        'amount' => 'heroicon-o-currency-dollar',
        'value' => 'heroicon-o-currency-dollar',
        'price' => 'heroicon-o-currency-dollar',
        'cost' => 'heroicon-o-currency-dollar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Mappings
    |--------------------------------------------------------------------------
    |
    | Define how to resolve relationship field values to their related models.
    |
    */
    'relationship_mappings' => [
        'branch_id' => ['model' => 'App\\Models\\Branch', 'field' => 'name'],
        'user_id' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'assigned_to' => ['model' => 'App\\Models\\User', 'field' => 'name'],
        'client_id' => ['model' => 'App\\Models\\Client', 'field' => 'name'],
        'customer_id' => ['model' => 'App\\Models\\Customer', 'field' => 'name'],
        'donor_id' => ['model' => 'App\\Models\\Donor', 'field' => 'name'],
        'organization_id' => ['model' => 'App\\Models\\Organization', 'field' => 'name'],
        'campaign_id' => ['model' => 'App\\Models\\Campaign', 'field' => 'name'],
        'project_id' => ['model' => 'App\\Models\\Project', 'field' => 'name'],
        'task_id' => ['model' => 'App\\Models\\Task', 'field' => 'name'],
        'department_id' => ['model' => 'App\\Models\\Department', 'field' => 'name'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be excluded from activity log display.
    |
    */
    'excluded_fields' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'email_verified_at',
        'password',
        'password_confirmation',
        'api_token',
        'last_login_at',
        'last_activity_at',
        'user_agent',
        'ip_address',
        'client_ip',
        'metadata',
        'attachments',
        'source',
        'sort',
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale Keys for Translatable Fields
    |--------------------------------------------------------------------------
    |
    | Locale keys used to detect translatable fields.
    |
    */
    'locale_keys' => [
        'en', 'ar', 'ku', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enum Color Mappings
    |--------------------------------------------------------------------------
    |
    | Map Filament enum colors to Tailwind CSS classes.
    |
    */
    'enum_colors' => [
        'primary' => 'bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200',
        'success' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
        'warning' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
        'danger' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
        'info' => 'bg-cyan-100 dark:bg-cyan-800 text-cyan-800 dark:text-cyan-200',
        'secondary' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        'default' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Change Status Colors
    |--------------------------------------------------------------------------
    |
    | Colors for different change statuses in the activity log.
    |
    */
    'change_status_colors' => [
        'added' => 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200',
        'removed' => 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200',
        'modified' => 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default values for various configurations.
    |
    */
    'defaults' => [
        'model_icon' => 'heroicon-o-document-text',
        'field_icon' => 'heroicon-o-cog-6-tooth',
        'max_string_length' => 50,
        'max_simple_key_value_items' => 5,
        'min_locale_keys_for_translatable' => 2,
    ],
];
