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
    'is_passed' => $examModule['exam']['is_passed'] ?? null,
    'latest_score' => $examModule['exam']['latest_score'] ?? null,
    'best_score' => $examModule['exam']['best_score'] ?? null,
    'latest_result_status' => $examModule['exam']['latest_result_status'] ?? null,
    'attempts_used' => $examModule['exam']['attempts_used'] ?? null,
    'attempts_remaining' => $examModule['exam']['attempts_remaining'] ?? null,
  ],
  'resume_target' => $payload['resume_target'],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
