<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            '045adc9f-1660-4d31-be05-30393cf561fe',
            '057d603c-20d1-4585-be16-a5e70b05a331',
            '11a5080d-a7f8-4816-927a-ac0b2ee24d69',
            '14f2f55e-1947-4752-a3a9-5f96a65f618f',
            '550ef0d8-01f3-495b-b124-0c6c1b5d9169',
            '6187206e-31f6-491f-b948-a0928dcdea3a',
            '61973387-0365-4ced-a4d7-ead807a5bf06',
            '65b4e78e-8642-4377-b1e0-375165851bcd',
            '67f6de7e-d7f4-45a0-b516-8480bfab7a55',
            '743417e8-87ee-4de1-a57a-562ae4a674d3',
            '7803f5b7-464f-4059-9449-3f60181898b4',
            '7f857c96-e846-4fbe-bfe1-ccb894bef25e',
            'a4c5afd1-09ce-47f2-9390-8b2dc95a6b01',
            'c66def1a-af73-4ebb-bd4b-3a1334824338',
            'd284bf34-0a4c-414d-bef8-482f7c29bd11',
            'd82fd305-5f5e-4f16-8ab3-eadefc05e232',
            'e2b307ee-6575-4dcd-a0a4-58d534e6533d',
            'ee36cc80-62af-453f-a53f-5e6aa703ee1a',
        ];

        $employment = [
            '00d22ccc-00d3-4286-8398-504760c7ff19',
            '0c512b1e-7adb-4b5a-b446-52f0de16811a',
            '0e8a0b81-d3cf-4d12-9a59-f91df203bd78',
            '13fa874c-4a33-4758-a178-d461f72f5271',
            '167c57fa-5e4c-4be2-bd47-660b32416c05',
            '1e3cdadf-6548-4540-80b1-3d644f69176f',
            '213caa64-bad3-422f-a827-4d42194854a3',
            '21989be9-b371-4a99-ba5c-c14d9093a295',
            '25bad09a-554e-4dbc-98c8-13c09d9cf794',
            '2f1ad05b-1a5e-4bf8-b96c-d19988b6f5d7',
            '3736d63d-ab3a-4717-9028-bff976bdbb35',
            '37d911ea-a094-4302-a2dc-5c723289cabe',
            '38d9a718-170d-4e9b-ba77-21491ca88c6f',
            '3c6c046c-8531-4dcb-b4f4-581a48f0a250',
            '3fc7ab95-6259-4910-9a83-93f6f69ac850',
            '4cd8cd4c-3685-4c2e-b6f3-e69b41550444',
            '4d21eff5-459e-4e1f-acbc-59b9d3ec85c7',
            '4f7585f4-638c-4e20-ba82-0e347f00abd8',
            '5290c347-e35a-4330-a259-db28bb9d7b96',
            '56970a3e-0b51-4704-b07f-a5df6222800f',
            '5af0826d-cbe6-4e3d-8e0e-7d314a635756',
            '682407ab-e2a9-4c9b-ab14-9c30873b0805',
            '6de15ec9-cc96-4784-832d-948a135be3ac',
            '74adc05e-69a3-4ba2-8530-0ab355866e04',
            '74f2b838-8e13-492e-bbf8-0d72d0bb5199',
            '8de94128-1b95-424f-bef8-59d4fdccd1ce',
            '93e62fe0-abaa-483a-82d8-021804484c3a',
            '98abc266-19b1-4289-9a37-89d63fe8d85b',
            '98aef086-fe01-42f0-bb3e-5e7dab3a9200',
            '9cc68353-3e15-46ac-a5b9-7008e26bad1c',
            'a0e0bc36-de45-4d66-9b8b-e845a732aa95',
            'a6cd4dd5-573c-426c-a87b-0bfbc18dce78',
            'ae40df43-68a7-4aa1-b3e3-6ec05894f2a8',
            'aeb9bd1e-d3d6-42b7-9d67-482da19065b2',
            'b065fe06-3991-427b-83fe-d3e6f38d0b6f',
            'b1dbf166-5fbd-4ce5-8233-090b2e421185',
            'b4b261f1-3856-41a3-a3b4-168f22ae5dcc',
            'b6107964-5923-4d26-8e5a-6b2bb2926903',
            'b64f27ad-3a34-48b3-9ec6-eefa85ed2db9',
            'c6a4c3a4-98f2-4909-ace1-d9402fa84a35',
            'c8338bae-927c-4a07-acc1-eae9d2ecef1c',
            'd7876d50-7b30-450c-984f-f8ceaf7dc54d',
            'dc7faaef-a955-43b5-99d3-f42c3ca75b84',
            'dd8e5d84-4dfc-44f5-a583-f2c78bb41d52',
            'dfefcace-1012-4ee5-8308-8efbdc07640f',
            'e0188b82-070a-48a2-9b9e-29bf8e0f5ad7',
            'e0831575-d195-407d-94b6-7c6316d7bca6',
            'e290f0c4-b87b-47e6-afab-2850d8b8b3fc',
            'e569b7cf-486d-4150-9cc2-625a5ea360e1',
            'e8aae3a8-2775-4e6e-aa6f-da4d78ff10da',
            'ec346433-d423-46ec-9af4-82ed7851348e',
            'ec9dd289-9047-42cf-b17a-03d5865fbcef',
            'ee7ad894-b5e7-4988-bae1-d32c3f8de201',
            'f80b7a61-d063-4397-ab8a-35f717ca6bbd',
        ];

        Storage::disk('public')->makeDirectory('sponsors');

        $this->command->info('Downloading Partner Sponsors...');
        $this->seedSponsors($partners, 'partner');

        $this->command->info('Downloading Employment Sponsors...');
        $this->seedSponsors($employment, 'employment');
    }

    private function seedSponsors(array $ids, string $type)
    {
        foreach ($ids as $index => $id) {
            $url = "https://api.computiq.tech/api/website/sponsors/{$id}/image";
            $filename = "sponsors/{$id}.png";

            try {
                $this->command->info("Fetching image for $id...");
                $response = Http::get($url);
                if ($response->successful()) {
                    Storage::disk('public')->put($filename, $response->body());
                    $this->command->info("Downloaded $filename successfully.");
                } else {
                    $this->command->error("Failed to download image for ID: $id - Status Code: " . $response->status());
                    continue;
                }

                Sponsor::updateOrCreate(
                    ['image' => $filename],
                    [
                        'name' => "Sponsor {$id}",
                        'type' => $type,
                        'sort_order' => $index,
                        'is_active' => true,
                    ]
                );
            } catch (\Exception $e) {
                $this->command->error("Error seeding sponsor $id: " . $e->getMessage());
            }
        }
    }
}
