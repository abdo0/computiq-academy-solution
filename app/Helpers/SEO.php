<?php

namespace App\Helpers;

class SEO
{
    protected static $tags = [];

    public static function setTitle($title)
    {
        self::$tags['title'] = $title . ' | ' . settings('company_name');

        return new self;
    }

    public static function setDescription($description)
    {
        self::$tags['description'] = $description;

        return new self;
    }

    public static function setKeywords($keywords)
    {
        self::$tags['keywords'] = $keywords;

        return new self;
    }

    public static function setMeta()
    {
        // You can add additional meta processing here if needed
        return new self;
    }

    public static function setTag($name, $value)
    {
        self::$tags[$name] = $value;

        return new self;
    }

    public static function getTag($tag)
    {
        return self::$tags[$tag] ?? null;
    }

    public static function hasTag($tag): bool
    {
        return collect(self::$tags)->contains($tag);
    }

    public function meta(): array
    {
        return [
            'title' => self::getTag('title'),
            'description' => self::getTag('description'),
            'keywords' => self::getTag('keywords'),
            'ogImage' => self::getTag('og:image'),
        ];
    }
}
