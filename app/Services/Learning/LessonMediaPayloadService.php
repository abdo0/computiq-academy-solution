<?php

namespace App\Services\Learning;

use App\Models\CourseLesson;

class LessonMediaPayloadService
{
    public function __construct(
        protected VideoPayloadService $videoPayloadService,
    ) {
    }

    public function buildVideoPayload(CourseLesson $lesson): ?array
    {
        return $this->videoPayloadService->buildPayload(
            $lesson,
            'video',
            $lesson->video_source_type,
            $lesson->video_provider,
            $lesson->video_url,
            $lesson->embed_url,
        );
    }

    public function buildPublicPreviewPayload(CourseLesson $lesson): array
    {
        $lesson->loadMissing('module');

        return [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->getTranslations('title'),
                'description' => $lesson->getTranslations('description'),
                'duration_minutes' => $lesson->duration_minutes,
                'video' => $this->buildVideoPayload($lesson),
            ],
            'module' => [
                'id' => $lesson->module?->id,
                'title' => $lesson->module?->getTranslations('title'),
            ],
        ];
    }
}
