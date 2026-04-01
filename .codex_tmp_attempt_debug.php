<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$attempt = App\Models\CourseExamAttempt::query()->with('exam.module.course')->latest('id')->first();
if (! $attempt) { echo "NO_ATTEMPT\n"; exit; }

echo json_encode([
  'attempt_id' => $attempt->id,
  'user_id' => $attempt->user_id,
  'exam_id' => $attempt->course_exam_id,
  'course_slug' => $attempt->exam?->module?->course?->slug,
  'submitted_at' => optional($attempt->submitted_at)->toIso8601String(),
  'started_at' => optional($attempt->started_at)->toIso8601String(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
