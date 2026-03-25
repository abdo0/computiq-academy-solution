<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'پێویستە :attribute قبوڵ بکرێت',
    'accepted_if' => 'پێویستە :attribute قبوڵ بکرێت کاتێک :other یەکسانە بە :value.',
    'active_url' => ':attribute بەستەرێکی دروست نییە',
    'after' => ':attribute پێویستە ڕێکەوتێک دوای :date بێت.',
    'after_or_equal' => ':attribute پێویستە ڕێکەوتێک دوای یان یەکسان بە :date بێت.',
    'alpha' => ':attribute تەنها پێویستە پیتی ئەلفوبێیی تێدابێت',
    'alpha_dash' => ':attribute تەنها پێویستە پیتی ئەلفوبێیی، ژمارە و هێڵ تێدابێت.',
    'alpha_num' => ':attribute تەنها پێویستە پیتی ئەلفوبێیی و ژمارە تێدابێت',
    'array' => ':attribute پێویستە ڕیزبەند بێت',
    'before' => ':attribute پێویستە ڕێکەوتێک پێش :date بێت.',
    'before_or_equal' => ':attribute پێویستە ڕێکەوتێک پێش یان یەکسان بە :date بێت',
    'between' => [
        'array' => ':attribute پێویستە لە نێوان :min و :max دانە بێت',
        'file' => ':attribute پێویستە لە نێوان :min و :max کیلۆبایت بێت.',
        'numeric' => ':attribute پێویستە لە نێوان :min و :max بێت.',
        'string' => ':attribute پێویستە لە نێوان :min و :max پیت بێت',
    ],
    'boolean' => ':attribute پێویستە دروست یان نادروست بێت',
    'confirmed' => ':attribute و دڵنیاکردنەوەکەی یەکسان نییە',
    'current_password' => 'وشەی نهێنی هەڵەیە',
    'date' => ':attribute ڕێکەوتێکی دروست نییە',
    'date_equals' => ':attribute پێویستە یەکسان بێت بە :date.',
    'date_format' => ':attribute پێویستە بە شێوازی :format بێت.',
    'declined' => ':attribute پێویستە ڕەت بکرێتەوە',
    'declined_if' => ':attribute پێویستە ڕەت بکرێتەوە کاتێک :other یەکسانە بە :value.',
    'different' => ':attribute و :other پێویستە جیاواز بن',
    'digits' => ':attribute پێویستە :digits ژمارە بێت',
    'digits_between' => ':attribute پێویستە لە نێوان :min و :max ژمارە بێت',
    'dimensions' => ':attribute ڕەهەندی وێنەی دروست نییە.',
    'distinct' => ':attribute بەها دووبارەکراوەی هەیە.',
    'doesnt_end_with' => ':attribute نابێت کۆتایی بێت بە :values.',
    'doesnt_start_with' => ':attribute نابێت دەستپێبکات بە :values.',
    'email' => ':attribute پێویستە ئیمەیڵێکی دروست بێت',
    'ends_with' => ':attribute پێویستە کۆتایی بێت بە :values.',
    'enum' => ':attribute هەڵەیە',
    'exists' => ':attribute هەڵەیە',
    'file' => ':attribute پێویستە پەڕگە بێت.',
    'filled' => ':attribute پێویستە پڕ بکرێتەوە',
    'gt' => [
        'array' => ':attribute پێویستە زیاتر لە :value دانە بێت.',
        'file' => ':attribute پێویستە گەورەتر بێت لە :value کیلۆبایت.',
        'numeric' => ':attribute پێویستە گەورەتر بێت لە :value.',
        'string' => ':attribute پێویستە زیاتر لە :value پیت بێت.',
    ],
    'gte' => [
        'array' => ':attribute پێویستە :value دانە یان زیاتر بێت.',
        'file' => ':attribute پێویستە گەورەتر یان یەکسان بێت بە :value کیلۆبایت.',
        'numeric' => ':attribute پێویستە گەورەتر یان یەکسان بێت بە :value.',
        'string' => ':attribute پێویستە :value پیت یان زیاتر بێت.',
    ],
    'image' => ':attribute پێویستە وێنە بێت',
    'in' => ':attribute هەڵەیە',
    'in_array' => ':attribute لە :other دا نییە.',
    'integer' => ':attribute پێویستە ژمارەی تەواو بێت',
    'ip' => ':attribute پێویستە ناونیشانی IPی دروست بێت',
    'ipv4' => ':attribute پێویستە ناونیشانی IPv4ی دروست بێت.',
    'ipv6' => ':attribute پێویستە ناونیشانی IPv6ی دروست بێت.',
    'json' => ':attribute پێویستە JSONی دروست بێت.',
    'lowercase' => ':attribute پێویستە پیتە بچووکەکان بێت',
    'lt' => [
        'array' => ':attribute پێویستە کەمتر لە :value دانە بێت.',
        'file' => ':attribute پێویستە بچووکتر بێت لە :value کیلۆبایت.',
        'numeric' => ':attribute پێویستە بچووکتر بێت لە :value.',
        'string' => ':attribute پێویستە کەمتر لە :value پیت بێت.',
    ],
    'lte' => [
        'array' => ':attribute پێویستە کەمتر لە :value دانە بێت.',
        'file' => ':attribute پێویستە بچووکتر یان یەکسان بێت بە :value کیلۆبایت.',
        'numeric' => ':attribute پێویستە بچووکتر یان یەکسان بێت بە :value.',
        'string' => ':attribute پێویستە کەمتر یان یەکسان بێت بە :value پیت.',
    ],
    'mac_address' => ':attribute پێویستە ناونیشانی MACی دروست بێت.',
    'max' => [
        'array' => ':attribute نابێت زیاتر لە :max دانە بێت.',
        'file' => ':attribute نابێت گەورەتر بێت لە :max کیلۆبایت',
        'numeric' => ':attribute نابێت گەورەتر بێت لە :max.',
        'string' => ':attribute نابێت زیاتر لە :max پیت بێت',
    ],
    'max_digits' => ':attribute نابێت زیاتر لە :max ژمارە بێت.',
    'mimes' => ':attribute پێویستە جۆری پەڕگەکە بێت: :values.',
    'mimetypes' => ':attribute پێویستە جۆری پەڕگەکە بێت: :values.',
    'min' => [
        'array' => ':attribute پێویستە کەمترین :min دانە بێت',
        'file' => ':attribute پێویستە کەمترین :min کیلۆبایت بێت',
        'numeric' => ':attribute پێویستە کەمترین :min بێت.',
        'string' => ':attribute پێویستە کەمترین :min پیت بێت',
    ],
    'min_digits' => ':attribute پێویستە کەمترین :min ژمارە بێت.',
    'multiple_of' => ':attribute پێویستە چەندجارەی :value بێت.',
    'not_in' => ':attribute هەڵەیە',
    'not_regex' => ':attribute شێوازی هەڵەیە',
    'numeric' => ':attribute پێویستە ژمارە بێت',
    'password' => [
        'letters' => ':attribute پێویستە کەمترین یەک پیتی تێدابێت.',
        'mixed' => ':attribute پێویستە کەمترین یەک پیتی گەورە و بچووک تێدابێت.',
        'numbers' => ':attribute پێویستە کەمترین یەک ژمارە تێدابێت.',
        'symbols' => ':attribute پێویستە کەمترین یەک هێما تێدابێت.',
        'uncompromised' => ':attribute ناسەلامەتە. تکایە بهایەکی تر هەڵبژێرە.',
    ],
    'present' => ':attribute پێویستە هەبێت',
    'prohibited' => ':attribute قەدەغەکراوە',
    'prohibited_if' => ':attribute قەدەغەکراوە کاتێک :other یەکسانە بە :value.',
    'prohibited_unless' => ':attribute قەدەغەکراوە مەگەر :other یەکسان بێت بە :value.',
    'prohibits' => ':attribute قەدەغە دەکات :other لە بوون',
    'regex' => ':attribute شێوازی هەڵەیە',
    'required' => ':attribute پێویستە',
    'phone' => ':attribute ژمارەیەکی دروست نییە',
    'required_array_keys' => ':attribute پێویستە بەهاکانی :values تێدابێت.',
    'required_if' => ':attribute پێویستە کاتێک :other یەکسانە بە :value.',
    'required_if_accepted' => ':attribute پێویستە کاتێک :other قبوڵ کراوە.',
    'required_unless' => ':attribute پێویستە مەگەر :other یەکسان بێت بە :values.',
    'required_with' => ':attribute پێویستە کاتێک :values هەیە.',
    'required_with_all' => ':attribute پێویستە کاتێک :values هەمووی هەیە.',
    'required_without' => ':attribute پێویستە کاتێک :values نییە.',
    'required_without_all' => ':attribute پێویستە کاتێک :values هیچ یەک نییە.',
    'same' => ':attribute و :other پێویستە یەکسان بن',
    'size' => [
        'array' => ':attribute پێویستە :size دانە بێت',
        'file' => ':attribute پێویستە :size کیلۆبایت بێت',
        'numeric' => ':attribute پێویستە :size بێت',
        'string' => ':attribute پێویستە :size پیت بێت',
    ],
    'starts_with' => ':attribute پێویستە دەستپێبکات بە :values.',
    'string' => ':attribute پێویستە دەق بێت.',
    'timezone' => ':attribute پێویستە ناوچەی کاتی دروست بێت',
    'unique' => ':attribute پێشتر هەیە',
    'uploaded' => ':attribute بارکردنی هەڵەی هەیە',
    'uppercase' => ':attribute پێویستە پیتە گەورەکان بێت',
    'url' => ':attribute بەستەرێکی دروست نییە',
    'uuid' => ':attribute پێویستە UUIDی دروست بێت',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'پەیامی تایبەت',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'ناو',
        'username' => 'ناوی بەکارهێنەر',
        'email' => 'ئیمەیڵ',
        'comment' => 'تێبینی',
        'first_name' => 'ناوی یەکەم',
        'last_name' => 'ناوی کۆتایی',
        'password' => 'وشەی نهێنی',
        'password_confirmation' => 'دڵنیاکردنەوەی وشەی نهێنی',
        'city' => 'شار',
        'country' => 'وڵات',
        'address' => 'ناونیشان',
        'phone' => 'تەلەفۆن',
        'mobile' => 'مۆبایل',
        'age' => 'تەمەن',
        'sex' => 'رەگەز',
        'gender' => 'جێندەر',
        'day' => 'رۆژ',
        'month' => 'مانگ',
        'year' => 'ساڵ',
        'hour' => 'کاتژمێر',
        'minute' => 'خولەک',
        'second' => 'چرکە',
        'content' => 'ناوەڕۆک',
        'description' => 'پێناس',
        'excerpt' => 'پوختە',
        'date' => 'بەروار',
        'time' => 'کات',
        'available' => 'بەردەستە',
        'size' => 'قەبارە',
        'price' => 'نرخ',
        'desc' => 'پێناس',
        'title' => 'سەردێڕ',
        'q' => 'گەڕان',
        'link' => 'بەستەر',
        'slug' => 'سلەگ',
    ],

];
