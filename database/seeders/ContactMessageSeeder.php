<?php

namespace Database\Seeders;

use App\Enums\ContactMessageSubject;
use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed.mohammed@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'أرغب في معرفة المزيد عن كيفية عمل المنصة وكيف يمكنني المساهمة في المشاريع الخيرية. هل يمكنكم تزويدي بمزيد من المعلومات؟',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'subject' => ContactMessageSubject::DONATION_ISSUE,
                'message' => 'I made a donation yesterday but I haven\'t received a confirmation email. Can you please check my transaction? Transaction ID: TXN-12345',
                'is_read' => true,
                'read_at' => now()->subDays(4)->addHours(2),
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'محمد علي',
                'email' => 'mohammed.ali@example.com',
                'subject' => ContactMessageSubject::SPONSORSHIP,
                'message' => 'أنا مهتم بكفالة مشروع كامل لبناء مدرسة في منطقة ريفية. أريد معرفة التفاصيل والإجراءات المطلوبة.',
                'is_read' => true,
                'read_at' => now()->subDays(3)->addHours(5),
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Fatima Hassan',
                'email' => 'fatima.hassan@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'شكراً لكم على المنصة الرائعة. أريد أن أعرف كيف يمكنني متابعة تأثير تبرعاتي والمشاريع التي ساهمت فيها.',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'subject' => ContactMessageSubject::DONATION_ISSUE,
                'message' => 'I tried to make a donation but the payment gateway seems to be having issues. The transaction failed multiple times. Please help.',
                'is_read' => true,
                'read_at' => now()->subDays(1)->addHours(3),
                'created_at' => now()->subDays(1),
            ],
            [
                'name' => 'ليلى أحمد',
                'email' => 'layla.ahmed@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'أريد أن أكون متطوعة في المنصة. كيف يمكنني التقديم وما هي المتطلبات؟',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHours(12),
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@example.com',
                'subject' => ContactMessageSubject::SPONSORSHIP,
                'message' => 'Our company is interested in sponsoring a water well project. We would like to discuss the details and requirements for full project sponsorship.',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHours(8),
            ],
            [
                'name' => 'خالد إبراهيم',
                'email' => 'khalid.ibrahim@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'أشكركم على جهودكم في مساعدة المحتاجين. أريد معرفة كيفية التحقق من صحة الحملات قبل التبرع.',
                'is_read' => true,
                'read_at' => now()->subHours(6),
                'created_at' => now()->subHours(6),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'subject' => ContactMessageSubject::DONATION_ISSUE,
                'message' => 'I received a receipt for my donation but the amount shown is incorrect. I donated $500 but the receipt shows $450. Please correct this.',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHours(4),
            ],
            [
                'name' => 'عمر محمود',
                'email' => 'omar.mahmoud@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'هل يمكنني التبرع بشكل شهري؟ وما هي الخيارات المتاحة للتبرعات المتكررة؟',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ],
            [
                'name' => 'Michael Wilson',
                'email' => 'michael.wilson@example.com',
                'subject' => ContactMessageSubject::SPONSORSHIP,
                'message' => 'I am interested in sponsoring an education project. Can you provide me with information about available education campaigns and sponsorship options?',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subHour(),
            ],
            [
                'name' => 'نورا سعد',
                'email' => 'nora.saad@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'أريد معرفة المزيد عن الشفافية في المنصة وكيف يتم تتبع التبرعات والتأكد من وصولها للمستفيدين.',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subMinutes(30),
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@example.com',
                'subject' => ContactMessageSubject::DONATION_ISSUE,
                'message' => 'I need to update my payment method. How can I change my saved credit card information?',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subMinutes(15),
            ],
            [
                'name' => 'ريم عبدالله',
                'email' => 'reem.abdullah@example.com',
                'subject' => ContactMessageSubject::GENERAL,
                'message' => 'شكراً لكم على المنصة. أريد أن أعرف إذا كان بإمكاني إنشاء حملة خيرية من خلال المنصة أم أنها مخصصة للمنظمات فقط؟',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subMinutes(5),
            ],
            [
                'name' => 'Jennifer Martinez',
                'email' => 'jennifer.martinez@example.com',
                'subject' => ContactMessageSubject::SPONSORSHIP,
                'message' => 'Our foundation is looking to partner with your platform for a large-scale health project. We would like to schedule a meeting to discuss partnership opportunities.',
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
            ],
        ];

        foreach ($messages as $messageData) {
            // Check if message exists by email and created_at (to allow same email with different messages)
            // We'll use a combination of email and message content to check uniqueness
            $exists = ContactMessage::where('email', $messageData['email'])
                ->where('message', $messageData['message'])
                ->exists();

            if ($exists) {
                continue;
            }

            ContactMessage::create($messageData);
        }
    }
}

