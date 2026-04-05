<?php

namespace App\Services\Learning;

use Spatie\MediaLibrary\HasMedia;

class VideoPayloadService
{
    public function buildPayload(
        HasMedia $model,
        string $collection,
        ?string $sourceType,
        ?string $provider,
        ?string $videoUrl,
        ?string $embedUrl,
    ): ?array {
        if ($sourceType === 'upload') {
            $media = $model->getFirstMedia($collection);

            return $media ? [
                'source_type' => 'upload',
                'provider' => 'upload',
                'url' => $media->getFullUrl(),
                'mime_type' => $media->mime_type,
                'name' => $media->name,
            ] : null;
        }

        if ($sourceType === 'embed' && $embedUrl) {
            return [
                'source_type' => 'embed',
                'provider' => $provider,
                'url' => $videoUrl,
                'embed_url' => $embedUrl,
            ];
        }

        return null;
    }
}
