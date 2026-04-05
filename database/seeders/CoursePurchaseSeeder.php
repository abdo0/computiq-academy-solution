<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursePurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::query()
            ->where('email', 'like', 'student-demo-%@computiq.test')
            ->orderBy('id')
            ->get();

        $gateway = PaymentGateway::query()->where('is_active', true)->orderBy('id')->first();
        $courses = Course::query()->orderBy('sort_order')->orderBy('id')->get();

        if ($students->isEmpty() || ! $gateway || $courses->isEmpty()) {
            return;
        }

        $studentIds = $students->pluck('id');
        $orderIds = DB::table('orders')->whereIn('user_id', $studentIds)->pluck('id');

        if ($orderIds->isNotEmpty()) {
            DB::table('course_enrollments')->whereIn('order_id', $orderIds)->delete();
            DB::table('transactions')->whereIn('order_id', $orderIds)->delete();
            DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
        }

        DB::table('orders')->whereIn('user_id', $studentIds)->delete();

        $studentPool = $students->values();
        $studentCount = $studentPool->count();

        foreach ($courses as $courseIndex => $course) {
            $buyersCount = min($studentCount, 5 + ($courseIndex % 4));
            $startOffset = ($courseIndex * 2) % $studentCount;
            $buyers = collect();

            for ($i = 0; $i < $buyersCount; $i++) {
                $buyers->push($studentPool[($startOffset + $i) % $studentCount]);
            }

            foreach ($buyers->values() as $buyerIndex => $buyer) {
                $paidAt = now()
                    ->subDays(($courseIndex + 1) * 3 + $buyerIndex)
                    ->subHours(($buyerIndex % 5) + 1);

                $order = Order::create([
                    'user_id' => $buyer->id,
                    'payment_gateway_id' => $gateway->id,
                    'subtotal_amount' => $course->price,
                    'gateway_processing_fee' => 0,
                    'total_amount' => $course->price,
                    'status' => 'paid',
                    'paid_at' => $paidAt,
                    'notes' => 'Seeded demo course purchase',
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'unit_price' => $course->price,
                    'total_price' => $course->price,
                    'course_snapshot' => [
                        'title' => $course->getTranslations('title'),
                        'slug' => $course->slug,
                        'image' => $course->image,
                    ],
                ]);

                $transaction = Transaction::create([
                    'order_id' => $order->id,
                    'payment_gateway_id' => $gateway->id,
                    'amount' => $course->price,
                    'gateway_processing_fee' => 0,
                    'platform_commission' => 0,
                    'net_amount' => $course->price,
                    'total_amount' => $course->price,
                    'status' => 'completed',
                    'notes' => 'Seeded completed transaction',
                ]);

                CourseEnrollment::create([
                    'user_id' => $buyer->id,
                    'course_id' => $course->id,
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'enrolled_at' => $paidAt,
                ]);
            }

            $course->forceFill([
                'students_count' => $buyers->count(),
            ])->save();
        }
    }
}
