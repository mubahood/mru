<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\MruProgramme;

class AutoFillProgrammeSemesterConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mru:autofill-programme-semesters {--force : Force update even if already configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Intelligently auto-fill programme semester configuration based on course registration data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Analyzing ACTUAL student course registrations...');
        $this->info('📊 Calculating average courses per student per semester...');
        
        $programmes = MruProgramme::where('progcode', '!=', 'PLACEHOLDER')
            ->where('progcode', '!=', 'ALL')
            ->get();
        
        $updated = 0;
        $skipped = 0;
        
        $progressBar = $this->output->createProgressBar($programmes->count());
        $progressBar->start();
        
        foreach ($programmes as $programme) {
            // Skip if already configured unless --force is used
            if ($programme->total_semesters > 0 && !$this->option('force')) {
                $skipped++;
                $progressBar->advance();
                continue;
            }
            
            // Calculate AVERAGE courses per student from actual registrations
            $registrationStats = DB::table('acad_course_registration')
                ->where('prog_id', $programme->progcode)
                ->select(
                    'semester',
                    DB::raw('COUNT(*) as total_registrations'),
                    DB::raw('COUNT(DISTINCT regno) as unique_students'),
                    DB::raw('ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT regno), 0), 1) as avg_courses')
                )
                ->groupBy('semester')
                ->orderBy('semester')
                ->get();
            
            if ($registrationStats->isEmpty()) {
                // No registration data, try curriculum data
                $curriculumStats = DB::table('acad_programmecourses')
                    ->where('progcode', $programme->progcode)
                    ->select('study_year', 'semester', DB::raw('COUNT(DISTINCT course_code) as course_count'))
                    ->groupBy('study_year', 'semester')
                    ->orderBy('study_year')
                    ->orderBy('semester')
                    ->get();
                
                if ($curriculumStats->isEmpty()) {
                    // No data at all, use estimates based on programme level
                    if ($programme->couselength > 0) {
                        $inferredSemesters = intval($programme->couselength * 2);
                        $programme->total_semesters = min($inferredSemesters, 12);
                        
                        $estimatedCoursesPerSemester = match($programme->levelCode) {
                            1 => 5,  // Certificate: 5 courses/semester
                            2 => 6,  // Diploma: 6 courses/semester
                            3 => 7,  // Degree: 7 courses/semester
                            4 => 5,  // Masters: 5 courses/semester
                            5 => 4,  // PhD: 4 courses/semester
                            default => 6
                        };
                        
                        for ($i = 1; $i <= $programme->total_semesters; $i++) {
                            $field = "number_of_semester_{$i}_courses";
                            $programme->$field = $estimatedCoursesPerSemester;
                        }
                        
                        $programme->is_processed = 'Yes';
                        $programme->process_passed = 'Yes';
                        $programme->error_mess = sprintf(
                            'Estimated: %d semesters × %d courses = %d total (no registration/curriculum data)',
                            $programme->total_semesters,
                            $estimatedCoursesPerSemester,
                            $programme->total_semesters * $estimatedCoursesPerSemester
                        );
                        $programme->save();
                        $updated++;
                    }
                    $progressBar->advance();
                    continue;
                }
                
                // Use curriculum data as fallback
                $maxYear = $curriculumStats->max('study_year');
                $hasSemester3 = $curriculumStats->where('semester', 3)->count() > 0;
                $programme->total_semesters = min($hasSemester3 ? $maxYear * 3 : $maxYear * 2, 12);
                
                $semesterMap = [];
                foreach ($curriculumStats as $stat) {
                    $absoluteSemester = $hasSemester3 
                        ? (($stat->study_year - 1) * 3) + $stat->semester
                        : (($stat->study_year - 1) * 2) + $stat->semester;
                    $semesterMap[$absoluteSemester] = (int)$stat->course_count;
                }
                
                for ($i = 1; $i <= 12; $i++) {
                    $field = "number_of_semester_{$i}_courses";
                    $programme->$field = $semesterMap[$i] ?? null;
                }
                
                $programme->is_processed = 'Yes';
                $programme->process_passed = 'Yes';
                $programme->error_mess = 'Configured from curriculum data (no registration history)';
                $programme->save();
                $updated++;
                $progressBar->advance();
                continue;
            }
            
            // We have registration data! Calculate average per student
            $semesterMap = [];
            $totalStudents = 0;
            $totalCourses = 0;
            
            foreach ($registrationStats as $stat) {
                $semesterMap[$stat->semester] = (int)round($stat->avg_courses);
                $totalStudents += $stat->unique_students;
                $totalCourses += $stat->total_registrations;
            }
            
            // Determine total semesters from registration data
            $maxSemester = max(array_keys($semesterMap));
            $programme->total_semesters = min($maxSemester, 12);
            
            // Apply average courses per semester
            for ($i = 1; $i <= 12; $i++) {
                $field = "number_of_semester_{$i}_courses";
                $programme->$field = $semesterMap[$i] ?? null;
            }
            
            // Validate: realistic range is 3-15 courses per semester
            $avgOverall = array_sum($semesterMap) / count($semesterMap);
            $isRealistic = $avgOverall >= 3 && $avgOverall <= 15;
            
            $programme->is_processed = 'Yes';
            $programme->process_passed = $isRealistic ? 'Yes' : 'No';
            $programme->error_mess = sprintf(
                '%s %d semesters, avg %.1f courses/student/sem (%d students analyzed)',
                $isRealistic ? 'Configured:' : 'Warning:',
                $programme->total_semesters,
                $avgOverall,
                $totalStudents
            );
            
            try {
                $programme->save();
                $updated++;
            } catch (\Exception $e) {
                $this->error("\n❌ Error updating {$programme->progcode}: " . $e->getMessage());
                $programme->is_processed = 'Yes';
                $programme->process_passed = 'No';
                $programme->error_mess = 'Error: ' . $e->getMessage();
                $programme->save();
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Successfully updated: {$updated} programmes");
        $this->warn("⏭️  Skipped (already configured): {$skipped} programmes");
        $this->info("💡 Use --force option to update already configured programmes");
        
        return 0;
    }
}
