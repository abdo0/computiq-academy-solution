<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exam = App\Models\CourseExam::query()->with('questions.options')->find(7);
$attempts = App\Models\CourseExamAttempt::query()->where('user_id', 7)->where('course_exam_id', 7)->orderBy('id')->get();

$out = [
  'questions' => $exam?->questions->map(fn($q) => [
    'question_id' => $q->id,
    'question' => $q->question,
    'correct_option_id' => optional($q->options->firstWhere('is_correct', true))->id,
    'options' => $q->options->map(fn($o) => [
      'id' => $o->id,
      'text' => $o->option_text,
      'is_correct' => (bool) $o->is_correct,
    ])->values()->all(),
  ])->values()->all(),
  'attempts' => $attempts->map(fn($a) => [
    'id' => $a->id,
    'score' => $a->score,
    'answers_json' => $a->answers_json,
  ])->values()->all(),
];

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
