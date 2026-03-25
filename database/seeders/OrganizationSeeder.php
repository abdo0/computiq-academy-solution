<?php

namespace Database\Seeders;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'جمعية الإغاثة الطبية العراقية',
                'code' => 'ORG-IMA',
                'phone' => '+964 750 123 4567',
                'email' => 'info@iraqmedicalrelief.org',
                'address' => 'شارع الكرادة، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 1,
                'registration_number' => 'REG-IMA-001',
                'tax_id' => 'TAX-IMA-001',
                'contact_person_name' => 'د. علي حسين',
                'contact_person_email' => 'ali.hussein@iraqmedicalrelief.org',
                'contact_person_phone' => '+964 750 321 0000',
            ],
            [
                'name' => 'مؤسسة التعليم العراقي',
                'code' => 'ORG-IEF',
                'phone' => '+964 790 234 5678',
                'email' => 'contact@iraqeducation.org',
                'address' => 'منطقة المنصور، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 2,
            ],
            [
                'name' => 'منظمة المساعدات الإنسانية',
                'code' => 'ORG-IHA',
                'phone' => '+964 750 345 6789',
                'email' => 'help@iraqhumanitarian.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 3,
            ],
            [
                'name' => 'جمعية تنمية المجتمع المحلي',
                'code' => 'ORG-CDA',
                'phone' => '+964 790 456 7890',
                'email' => 'info@communitydev.org',
                'address' => 'الرصافة، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 4,
            ],
            [
                'name' => 'مؤسسة الأوقاف الإسلامية',
                'code' => 'ORG-IAW',
                'phone' => '+964 750 567 8901',
                'email' => 'contact@iraqawqaf.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 5,
            ],
            [
                'name' => 'جمعية دعم المشاريع الصغيرة',
                'code' => 'ORG-SBS',
                'phone' => '+964 790 678 9012',
                'email' => 'support@smallbusiness.org',
                'address' => 'المنصور، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 6,
            ],
            [
                'name' => 'مؤسسة رعاية الأيتام',
                'code' => 'ORG-OCF',
                'phone' => '+964 750 789 0123',
                'email' => 'info@orphancare.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 7,
            ],
            [
                'name' => 'جمعية الخدمات الجنائزية',
                'code' => 'ORG-FS',
                'phone' => '+964 790 890 1234',
                'email' => 'contact@funeralservices.org',
                'address' => 'الرصافة، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 8,
            ],
            [
                'name' => 'منظمة الاستجابة للطوارئ',
                'code' => 'ORG-ERO',
                'phone' => '+964 750 901 2345',
                'email' => 'emergency@response.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 9,
            ],
            [
                'name' => 'مؤسسة الرياضة والثقافة',
                'code' => 'ORG-SCF',
                'phone' => '+964 790 012 3456',
                'email' => 'info@sportsculture.org',
                'address' => 'المنصور، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 10,
            ],
            [
                'name' => 'جمعية دعم الأسر النازحة',
                'code' => 'ORG-DSF',
                'phone' => '+964 750 123 7890',
                'email' => 'support@displacedfamilies.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 11,
            ],
            [
                'name' => 'مؤسسة الصحة النفسية',
                'code' => 'ORG-MHF',
                'phone' => '+964 790 234 8901',
                'email' => 'contact@mentalhealth.org',
                'address' => 'الرصافة، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 12,
            ],
            [
                'name' => 'جمعية دعم المرأة',
                'code' => 'ORG-WS',
                'phone' => '+964 750 345 9012',
                'email' => 'info@womensupport.org',
                'address' => 'المنصور، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 13,
            ],
            [
                'name' => 'مؤسسة المياه النظيفة',
                'code' => 'ORG-CWF',
                'phone' => '+964 790 456 0123',
                'email' => 'water@cleanwater.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 14,
            ],
            [
                'name' => 'جمعية الشباب والرياضة',
                'code' => 'ORG-YSA',
                'phone' => '+964 750 567 1234',
                'email' => 'youth@sports.org',
                'address' => 'الرصافة، بغداد، العراق',
                'status' => OrganizationStatus::ACTIVE,
                'sort_order' => 15,
            ],
            [
                'name' => 'مؤسسة تحت المراجعة',
                'code' => 'ORG-PENDING',
                'phone' => '+964 790 567 8901',
                'email' => 'pending@example.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::PENDING,
                'sort_order' => 16,
            ],
            [
                'name' => 'جمعية غير نشطة',
                'code' => 'ORG-INACTIVE',
                'phone' => '+964 750 678 9012',
                'email' => 'inactive@example.org',
                'address' => 'المنصور، بغداد، العراق',
                'status' => OrganizationStatus::INACTIVE,
                'sort_order' => 17,
            ],
            [
                'name' => 'مؤسسة معلقة مؤقتاً',
                'code' => 'ORG-SUSPENDED',
                'phone' => '+964 790 789 0123',
                'email' => 'suspended@example.org',
                'address' => 'الرصافة، بغداد، العراق',
                'status' => OrganizationStatus::SUSPENDED,
                'sort_order' => 18,
            ],
            [
                'name' => 'منظمة محظورة',
                'code' => 'ORG-BANNED-1',
                'phone' => '+964 750 890 1234',
                'email' => 'banned1@example.org',
                'address' => 'الكرخ، بغداد، العراق',
                'status' => OrganizationStatus::BANNED,
                'sort_order' => 19,
            ],
            [
                'name' => 'جمعية محظورة',
                'code' => 'ORG-BANNED-2',
                'phone' => '+964 790 901 2345',
                'email' => 'banned2@example.org',
                'address' => 'المنصور، بغداد، العراق',
                'status' => OrganizationStatus::BANNED,
                'sort_order' => 20,
            ],
        ];

        $reviewerId = User::first()?->id;

        foreach ($organizations as $data) {
            $status = $data['status'] ?? OrganizationStatus::ACTIVE;
            $isBanned = $status === OrganizationStatus::BANNED;
            $isPending = $status === OrganizationStatus::PENDING;

            $organization = Organization::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'status' => $status,
                    'sort_order' => $data['sort_order'],
                    'verification_status' => $isBanned ? 'rejected' : ($isPending ? 'pending' : 'verified'),
                    'verification_tier' => $isBanned ? 'basic' : 'standard',
                    'verification_submitted_at' => $isPending ? null : now()->subDays(rand(10, 20)),
                    'verification_reviewed_at' => $isPending ? null : now()->subDays(rand(1, 5)),
                    'verification_reviewer_id' => $isPending ? null : $reviewerId,
                    'verification_notes' => $isBanned ? __('Banned organization - seeded for testing') : __('Auto-approved during seeding'),
                ]
            );

            $verification = $organization->verification()->firstOrCreate([], [
                'status' => $isBanned ? 'rejected' : ($isPending ? 'pending' : 'verified'),
                'tier' => $isBanned ? 'basic' : 'standard',
            ]);

            $verification->update([
                'status' => $isBanned ? 'rejected' : ($isPending ? 'pending' : 'verified'),
                'tier' => $isBanned ? 'basic' : 'standard',
                'registration_number' => $data['registration_number'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'contact_person_name' => $data['contact_person_name'] ?? null,
                'contact_person_email' => $data['contact_person_email'] ?? null,
                'contact_person_phone' => $data['contact_person_phone'] ?? null,
                'country' => 'Iraq',
                'city' => 'Baghdad',
                'address_line' => $data['address'],
                'submitted_at' => $organization->verification_submitted_at,
                'reviewed_at' => $organization->verification_reviewed_at,
                'reviewed_by' => $organization->verification_reviewer_id,
                'internal_notes' => $isBanned ? __('Banned organization - seeded for testing') : __('Seeded verification'),
                'rejection_reason' => $isBanned ? __('Violation of terms of service') : null,
            ]);
        }
    }
}
