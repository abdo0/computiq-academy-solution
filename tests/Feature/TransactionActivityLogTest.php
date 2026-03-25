<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Events\TransactionCanceled;
use App\Events\TransactionCompleted;
use App\Events\TransactionCreated;
use App\Events\TransactionFailed;
use App\Events\TransactionPending;
use App\Events\TransactionRefunded;
use App\Events\TransactionRefundRequested;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\TransactionActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TransactionActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected PaymentGateway $paymentGateway;

    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Create payment gateway (required for transaction)
        $this->paymentGateway = PaymentGateway::create([
            'name' => ['en' => 'Test Gateway'],
            'code' => 'test_gateway',
            'is_active' => true,
        ]);

        // Create test transaction
        $this->transaction = Transaction::create([
            'transaction_ref' => 'TXN-TEST123',
            'type' => TransactionType::DONATION,
            'payment_gateway_id' => $this->paymentGateway->id,
            'amount' => 10000,
            'gateway_processing_fee' => 500,
            'platform_commission' => 200,
            'net_amount' => 9300,
            'total_amount' => 10000,
            'status' => TransactionStatus::PENDING,
        ]);
    }

    public function test_transaction_created_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchCreated($this->user);

        Event::assertDispatched(TransactionCreated::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_created_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionCreated::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has created a transaction'), $log->description);
    }

    public function test_transaction_pending_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchPending($this->user);

        Event::assertDispatched(TransactionPending::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_pending_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionPending::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has set transaction to pending'), $log->description);
    }

    public function test_transaction_completed_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchCompleted($this->user);

        Event::assertDispatched(TransactionCompleted::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_completed_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionCompleted::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has completed a transaction'), $log->description);
    }

    public function test_transaction_failed_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchFailed($this->user);

        Event::assertDispatched(TransactionFailed::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_failed_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionFailed::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has failed a transaction'), $log->description);
    }

    public function test_transaction_canceled_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchCanceled($this->user);

        Event::assertDispatched(TransactionCanceled::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_canceled_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionCanceled::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has canceled a transaction'), $log->description);
    }

    public function test_transaction_refund_requested_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchRefundRequested($this->user);

        Event::assertDispatched(TransactionRefundRequested::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_refund_requested_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionRefundRequested::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has requested a transaction refund'), $log->description);
    }

    public function test_transaction_refunded_event_dispatches_and_logs(): void
    {
        Event::fake();

        $this->transaction->dispatchRefunded($this->user);

        Event::assertDispatched(TransactionRefunded::class, function ($event) {
            return $event->transaction->id === $this->transaction->id
                && $event->user->id === $this->user->id;
        });
    }

    public function test_transaction_refunded_listener_logs_activity(): void
    {
        $initialCount = TransactionActivityLog::count();

        TransactionRefunded::dispatch($this->transaction, $this->user);

        $this->assertDatabaseCount('transaction_activity_logs', $initialCount + 1);

        $log = TransactionActivityLog::latest()->first();
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has refunded a transaction'), $log->description);
    }

    public function test_transaction_log_activity_without_user(): void
    {
        $log = $this->transaction->logActivity(__('has performed an action'));

        $this->assertNotNull($log);
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertNull($log->user_id);
        $this->assertStringNotContainsString(':', $log->description);
    }

    public function test_transaction_log_created_method(): void
    {
        $log = $this->transaction->logCreated($this->user);

        $this->assertNotNull($log);
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has created a transaction'), $log->description);
    }

    public function test_transaction_log_canceled_method(): void
    {
        $log = $this->transaction->logCanceled($this->user);

        $this->assertNotNull($log);
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has canceled a transaction'), $log->description);
    }

    public function test_transaction_log_refunded_method(): void
    {
        $log = $this->transaction->logRefunded($this->user);

        $this->assertNotNull($log);
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has refunded a transaction'), $log->description);
    }

    public function test_transaction_log_status_change_method(): void
    {
        $log = $this->transaction->logStatusChange(
            TransactionStatus::PENDING,
            TransactionStatus::COMPLETED,
            $this->user
        );

        $this->assertNotNull($log);
        $this->assertEquals($this->transaction->id, $log->transaction_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertStringContainsString($this->user->name, $log->description);
        $this->assertStringContainsString(__('has completed a transaction'), $log->description);
    }

    public function test_transaction_activity_log_uses_auth_user_when_not_provided(): void
    {
        $this->actingAs($this->user);

        $log = TransactionActivityLog::log(
            \App\Enums\ActivityAction::SYSTEM,
            __('Test activity'),
            $this->transaction
        );

        $this->assertEquals($this->user->id, $log->user_id);
    }

    public function test_transaction_activity_log_relationship_to_transaction(): void
    {
        $log = $this->transaction->logCreated($this->user);

        $this->assertEquals($this->transaction->id, $log->transaction->id);
        $this->assertEquals($this->transaction->transaction_ref, $log->transaction->transaction_ref);
    }

    public function test_transaction_activity_log_relationship_to_user(): void
    {
        $log = $this->transaction->logCreated($this->user);

        $this->assertEquals($this->user->id, $log->user->id);
        $this->assertEquals($this->user->name, $log->user->name);
    }

    public function test_multiple_events_can_be_dispatched_for_same_transaction(): void
    {
        Event::fake();

        $this->transaction->dispatchCreated($this->user);
        $this->transaction->dispatchPending($this->user);
        $this->transaction->dispatchCompleted($this->user);

        Event::assertDispatched(TransactionCreated::class);
        Event::assertDispatched(TransactionPending::class);
        Event::assertDispatched(TransactionCompleted::class);
    }

    public function test_all_events_create_separate_log_entries(): void
    {
        $initialCount = TransactionActivityLog::where('transaction_id', $this->transaction->id)->count();

        $this->transaction->dispatchCreated($this->user);
        $this->transaction->dispatchPending($this->user);
        $this->transaction->dispatchCompleted($this->user);
        $this->transaction->dispatchFailed($this->user);
        $this->transaction->dispatchCanceled($this->user);
        $this->transaction->dispatchRefundRequested($this->user);
        $this->transaction->dispatchRefunded($this->user);

        $finalCount = TransactionActivityLog::where('transaction_id', $this->transaction->id)->count();

        $this->assertEquals($initialCount + 7, $finalCount);
    }
}
