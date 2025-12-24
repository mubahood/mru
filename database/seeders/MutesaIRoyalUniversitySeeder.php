<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MutesamIRoyalUniversitySeeder extends Seeder
{
    /**
     * Seed Mutesa I Royal University as the main enterprise (ID: 1)
     * 
     * Mutesa I Royal University (MRU) is a private university in Uganda.
     * It was established in 2007 and is located in Mengo, Kampala.
     * The university is named after Kabaka (King) Mutesa I of Buganda.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        // Check if enterprise ID 1 already exists
        $existingEnterprise = DB::table('enterprises')->where('id', 1)->first();
        
        if ($existingEnterprise) {
            echo "Enterprise ID 1 already exists. Updating...\n";
            
            // Update existing enterprise
            DB::table('enterprises')
                ->where('id', 1)
                ->update([
                    'name' => 'Mutesa I Royal University',
                    'short_name' => 'MRU',
                    'type' => 'University',
                    'motto' => 'Excellence in Education and Service',
                    'details' => 'Mutesa I Royal University (MRU) is a private chartered university in Uganda. Established in 2007, the university is named after Ssekabaka Muteesa I, the 30th Kabaka (King) of Buganda. MRU is committed to providing quality higher education and contributing to national development through academic excellence, research, and community service.',
                    'logo' => 'images/mru-logo.png',
                    'phone_number' => '+256 414 271 068',
                    'phone_number_2' => '+256 414 271 069',
                    'email' => 'info@mru.ac.ug',
                    'website' => 'https://www.mru.ac.ug',
                    'address' => 'Mengo, Kampala, Uganda',
                    'p_o_box' => 'P.O. Box 6557, Kampala',
                    'color' => '#01AEF0',
                    'sec_color' => '#39CA78',
                    'welcome_message' => 'Welcome to Mutesa I Royal University - Where Excellence Meets Opportunity',
                    'has_theology' => 'No',
                    'has_valid_lisence' => 'Yes',
                    'school_pay_status' => 'No',
                    'accepts_online_applications' => 'Yes',
                    'application_fee' => 50000,
                    'application_instructions' => 'Please complete all required fields in the application form and upload the necessary documents. For any inquiries, contact the admissions office.',
                    'subdomain' => 'mru',
                    'wallet_balance' => 0,
                    'can_send_messages' => 'Yes',
                    'expiry' => Carbon::now()->addYear()->toDateString(),
                    'updated_at' => $now,
                ]);
                
            echo "✓ Mutesa I Royal University updated successfully (ID: 1)\n";
            
        } else {
            echo "Creating Mutesa I Royal University as Enterprise ID 1...\n";
            
            // Insert new enterprise with ID 1
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            DB::table('enterprises')->insert([
                'id' => 1,
                'name' => 'Mutesa I Royal University',
                'short_name' => 'MRU',
                'type' => 'University',
                'motto' => 'Excellence in Education and Service',
                'details' => 'Mutesa I Royal University (MRU) is a private chartered university in Uganda. Established in 2007, the university is named after Ssekabaka Muteesa I, the 30th Kabaka (King) of Buganda. MRU is committed to providing quality higher education and contributing to national development through academic excellence, research, and community service.',
                'logo' => 'images/mru-logo.png',
                'phone_number' => '+256 414 271 068',
                'phone_number_2' => '+256 414 271 069',
                'email' => 'info@mru.ac.ug',
                'website' => 'https://www.mru.ac.ug',
                'address' => 'Mengo, Kampala, Uganda',
                'p_o_box' => 'P.O. Box 6557, Kampala',
                'color' => '#01AEF0',
                'sec_color' => '#39CA78',
                'welcome_message' => 'Welcome to Mutesa I Royal University - Where Excellence Meets Opportunity',
                'administrator_id' => 1,
                'has_theology' => 'No',
                'has_valid_lisence' => 'Yes',
                'school_pay_status' => 'No',
                'accepts_online_applications' => 'Yes',
                'application_fee' => 50000,
                'application_instructions' => 'Please complete all required fields in the application form and upload the necessary documents. For any inquiries, contact the admissions office.',
                'subdomain' => 'mru',
                'wallet_balance' => 0,
                'can_send_messages' => 'Yes',
                'expiry' => Carbon::now()->addYear()->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            echo "✓ Mutesa I Royal University created successfully (ID: 1)\n";
        }
        
        // Create first academic year
        $academicYear = DB::table('academic_years')->where('enterprise_id', 1)->first();
        
        if (!$academicYear) {
            echo "\nCreating first academic year (2024/2025)...\n";
            
            $academicYearId = DB::table('academic_years')->insertGetId([
                'enterprise_id' => 1,
                'name' => '2024/2025',
                'starts' => '2024-08-01',
                'ends' => '2025-07-31',
                'details' => 'Academic Year 2024/2025 - Mutesa I Royal University',
                'is_active' => 1,
                'demo_id' => 0,
                'process_data' => 'Yes',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            echo "✓ Academic Year created (ID: {$academicYearId})\n";
            
            // Create semesters (2 semesters for university)
            echo "\nCreating semesters...\n";
            
            // Semester 1
            $semester1Id = DB::table('terms')->insertGetId([
                'enterprise_id' => 1,
                'academic_year_id' => $academicYearId,
                'name' => '1',
                'term_name' => '1',
                'starts' => '2024-08-01',
                'ends' => '2024-12-31',
                'details' => 'Semester 1 - 2024/2025',
                'is_active' => 1,
                'demo_id' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            echo "✓ Semester 1 created (ID: {$semester1Id})\n";
            
            // Semester 2
            $semester2Id = DB::table('terms')->insertGetId([
                'enterprise_id' => 1,
                'academic_year_id' => $academicYearId,
                'name' => '2',
                'term_name' => '2',
                'starts' => '2025-01-01',
                'ends' => '2025-07-31',
                'details' => 'Semester 2 - 2024/2025',
                'is_active' => 0,
                'demo_id' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            echo "✓ Semester 2 created (ID: {$semester2Id})\n";
            
        } else {
            echo "\n✓ Academic year already exists for MRU\n";
        }
        
        echo "\n=== Mutesa I Royal University Setup Complete ===\n";
        echo "Enterprise ID: 1\n";
        echo "Type: University\n";
        echo "Semesters: 2 per academic year\n";
        echo "Current Academic Year: 2024/2025\n";
        echo "Active Semester: Semester 1\n\n";
    }
}
