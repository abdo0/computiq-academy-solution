<?php

namespace App\Services\Learning;

use Illuminate\Support\Str;
use InvalidArgumentException;

class VideoEmbedService
{
    public static function normalize(?string $url): array
    {
        $url = trim((string) $url);

        if ($url === '') {
            throw new InvalidArgumentException(__('Please provide a video URL.'));
        }

        $parts = parse_url($url);
        $host = Str::lower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        parse_str($parts['query'] ?? '', $query);

        return match (true) {
            Str::contains($host, ['youtube.com', 'youtu.be']) => self::normalizeYouTube($host, $path, $query, $url),
            Str::contains($host, ['vimeo.com']) => self::normalizeVimeo($path, $url),
            Str::contains($host, ['loom.com']) => self::normalizeLoom($path, $url),
            Str::contains($host, ['wistia.com', 'wi.st', 'fast.wistia.net']) => self::normalizeWistia($path, $url),
            default => throw new InvalidArgumentException(__('This video provider is not supported.')),
        };
    }

    protected static function normalizeYouTube(string $host, string $path, array $query, string $originalUrl): array
    {
        $videoId = null;
        $segments = array_values(array_filter(explode('/', $path)));

        if (Str::contains($host, 'youtu.be')) {
            $videoId = $segments[0] ?? null;
        } elseif (($segments[0] ?? null) === 'watch') {
            $videoId = $query['v'] ?? null;
        } elseif (in_array($segments[0] ?? null, ['embed', 'shorts'], true)) {
            $videoId = $segments[1] ?? null;
        } else {
            $videoId = $query['v'] ?? ($segments[0] ?? null);
        }

        if (! $videoId) {
            throw new InvalidArgumentException(__('Unable to detect the YouTube video ID.'));
        }

        return [
            'provider' => 'youtube',
            'video_url' => $originalUrl,
            'embed_url' => 'https://www.youtube.com/embed/' . $videoId,
        ];
    }

    protected static function normalizeVimeo(string $path, string $originalUrl): array
    {
        if (! preg_match('/(?:video\/)?(\d+)/', $path, $matches)) {
            throw new InvalidArgumentException(__('Unable to detect the Vimeo video ID.'));
        }

        return [
            'provider' => 'vimeo',
            'video_url' => $originalUrl,
            'embed_url' => 'https://player.vimeo.com/video/' . $matches[1],
        ];
    }

    protected static function normalizeLoom(string $path, string $originalUrl): array
    {
        if (! preg_match('/(?:share|embed)\/([a-zA-Z0-9]+)/', $path, $matches)) {
            throw new InvalidArgumentException(__('Unable to detect the Loom video ID.'));
        }

        return [
            'provider' => 'loom',
            'video_url' => $originalUrl,
            'embed_url' => 'https://www.loom.com/embed/' . $matches[1],
        ];
    }

    protected static function normalizeWistia(string $path, string $originalUrl): array
    {
        if (! preg_match('/(?:medias|iframe)\/([a-zA-Z0-9]+)/', $path, $matches)) {
            throw new InvalidArgumentException(__('Unable to detect the Wistia media ID.'));
        }

        return [
            'provider' => 'wistia',
            'video_url' => $originalUrl,
            'embed_url' => 'https://fast.wistia.net/embed/iframe/' . $matches[1],
        ];
    }
}
