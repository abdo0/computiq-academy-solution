<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::find(7);
$service = app(App\Services\Learning\LearningService::class);
$payload = $service->getCourseForLearner($user, 'generative-ai-chatgpt');
$examModule = collect($payload['modules'])->first(fn ($module) => ($module['exam']['id'] ?? null) === 7);
echo json_encode([
  'exam' => [
    'is_unlocked' => $examModule['exam']['is_unlocked'] ?? null,
    'active_attempt_id' => $examModule['exam']['active_attempt_id'] ?? null,
    'question_count' => $examModule['exam']['question_count'] ?? null,
    'questions_count_in_payload' => count($examModule['exam']['questions'] ?? []),
  ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
