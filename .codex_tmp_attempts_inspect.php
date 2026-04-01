<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$attempts = App\Models\CourseExamAttempt::query()
    ->where('user_id', 7)
    ->where('course_exam_id', 7)
    ->orderBy('id')
    ->get(['id','attempt_number','score','passed','answers_json','started_at','submitted_at']);

$out = $attempts->map(fn($a) => [
  'id' => $a->id,
  'attempt_number' => $a->attempt_number,
  'score' => $a->score,
  'passed' => $a->passed,
  'answers_json' => $a->answers_json,
  'started_at' => optional($a->started_at)->toIso8601String(),
  'submitted_at' => optional($a->submitted_at)->toIso8601String(),
])->values()->all();

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
