<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exam = App\Models\CourseExam::query()->with('module.course','questions.options')->latest('id')->first();
if (! $exam) { echo "NO_EXAM\n"; exit; }

echo json_encode([
  'exam_id' => $exam->id,
  'course_slug' => $exam->module?->course?->slug,
  'question_count' => $exam->questions->count(),
  'questions' => $exam->questions->map(fn($q) => [
    'id' => $q->id,
    'question' => $q->question,
    'options_count' => $q->options->count(),
  ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
