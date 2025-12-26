<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * AcademicYear Model
 * 
 * Represents academic years in the system (academic_years table).
 * This is the MAIN academic year table used throughout the system.
 * 
 * IMPORTANT: Academic Year Structure
 * ----------------------------------
 * DO NOT CONFUSE WITH:
 * - MruAcademicYear (acad_acadyears table) - Legacy table for historical results only
 * 
 * This table (academic_years) is used by:
 * - Terms/Semesters (terms table via academic_year_id)
 * - Student Semester Enrollments (student_has_semeters table)
 * - Academic Classes (academic_classes table)
 * - University Classes (theology_classes table)
 * 
 * Auto-Creation of Semesters:
 * When a new AcademicYear is created for a University enterprise,
 * it automatically creates 2 semesters (Semester 1 and 2) via boot() method.
 * For other enterprise types, it creates 3 terms.
 * 
 * @property int $id Primary key
 * @property int $enterprise_id Enterprise ID
 * @property string $name Academic year name (e.g., "2024/2025")
 * @property date $starts Start date
 * @property date $ends End date
 * @property string $details Additional details
 * @property int $is_active Whether this is the active academic year (1=Yes, 0=No)
 * @property string $process_data Processing status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AcademicYear extends Model
{
    use HasFactory;
    
    /**
     * Get the enterprise that owns this academic year.
     */
    function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Get all theology classes for this academic year.
     */
    function theology_classes()
    {
        return $this->hasMany(TheologyClass::class, 'academic_year_id');
    }

    /**
     * Get all classes for this academic year.
     */
    function classes()
    {
        return $this->hasMany(AcademicClass::class, 'academic_year_id');
    }
    
    /**
     * Get all terms/semesters for this academic year.
     * 
     * For Universities: Returns 2 semesters (Semester 1 and 2)
     * For Other Enterprises: Returns 3 terms (Term 1, 2, and 3)
     */
    function terms()
    {
        return $this->hasMany(Term::class);
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });

        self::created(function ($m) {
            $terms = [1, 2, 3];
            $ent = Enterprise::find($m->enterprise_id);
            if ($ent == null) {
                throw new Exception("Enterprise not found.", 1);
            }
            $term_name = 'Term';
            if ($ent->type == 'University') {
                $terms = [1, 2];
                $term_name = 'Semester';
            }
            foreach ($terms as $t) {
                $term = new Term();
                $term->enterprise_id = $m->enterprise_id;
                $term->academic_year_id = $m->id;
                $term->name = $t;
                $term->starts = $m->starts;
                $term->ends = $m->ends;
                $term->demo_id = 0;
                $term->details = "$term_name $t - " . $m->name;
                if ($t == 1) {
                    $term->is_active = 1;
                } else {
                    $term->is_active = 0;
                }
                $term->save();
            }
            try {
                if ($ent->type != 'University') { 
                    AcademicYear::generate_classes($m);
                }
            } catch (\Throwable $th) {
            }
        });


        self::creating(function ($m) {

            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null && $m->is_active == 1) { 
                throw new Exception("You cannot have to active academic years.", 1);
            }
            $m->process_data = 'Yes';
        });

        self::updating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();

            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        DB::update("update academic_years set is_active = ? where id = ? ", [0, $_m->id]);
                    }
                }
            }

            return $m;
        });

        self::updated(function ($m) {

            return $m;
            if ($m->process_data != 'Yes') {
                return $m;
            }

            if (((int)($m->is_active)) != 1) {
                foreach ($m->terms as $t) {
                    $t->is_active = 0;
                    $t->save();
                }
                foreach ($m->classes as $class) {
                    foreach ($class->students as $student) {
                        $a = $student->student;
                        $is_final = false;
                        if ($a->current_class != null) {
                            if ($a->current_class->level != null) {
                                $is_final = $a->current_class->level->is_final_class;
                            }
                        }

                        if ($is_final) {
                            $a->status = STATUS_NOT_ACTIVE;
                        } else {
                            $a->status = STATUS_PENDING;
                        }
                        $a->save();
                    }
                }
            } else {
                $has_active_term = false;
                foreach ($m->terms as $t) {
                    if ($t->is_active) {
                        $has_active_term = true;
                    }
                }
                if (!$has_active_term) {
                    foreach ($m->terms as $t) {
                        $t->is_active = true;
                        $t->save();
                        break;
                    }
                }

                /* try {
                    AcademicYear::generate_classes($m);   
                } catch (\Throwable $th) {
 
                } */
            }
        });
    }
    /* 

    "id" => 4
    "created_at" => "2022-12-14 20:51:40"
    "updated_at" => "2022-12-14 20:51:40"
    "name" => "Primary one"
    "category" => "Primary"
    "details" => "Primary one"
    "short_name" => "P.1"
    "is_final_class" => 0
    
    */

    public static function generate_classes($m)
    {

        $ent = Enterprise::find($m->enterprise_id);
        if ($ent == null) {
            die("Ent not found");
        }
        $classes = [];

        if ($ent->type == 'Primary') {
            foreach (
                AcademicClassLevel::where(
                    'category',
                    'Primary'
                )->orwhere(
                    'category',
                    'Nursery'
                )->get() as $level
            ) {
                $classes[] = $level;
            }
        } else if ($ent->type == 'Secondary') {
            foreach (
                AcademicClassLevel::where(
                    'category',
                    'Secondary'
                )->get() as $level
            ) {
                $classes[] = $level;
            }
        } else if ($ent->type == 'Advanced') {
            foreach (
                AcademicClassLevel::where(
                    'category',
                    'Secondary'
                )->orwhere(
                    'category',
                    'A-Level'
                )->get() as $level
            ) {
                $classes[] = $level;
            }
        }

        foreach ($classes as $class) {

            $_class = AcademicClass::where([
                'enterprise_id' =>  $ent->id,
                'academic_year_id' =>  $m->id,
                'academic_class_level_id' => $class->id,
            ])->first();

            if ($_class != null) {
                AcademicClass::generate_subjects($_class);
                continue;
            }

            $c = new AcademicClass();
            $c->enterprise_id = $ent->id;
            $c->academic_year_id = $m->id;
            $c->class_teahcer_id = $ent->administrator_id;
            $c->name = $class->name;
            $c->short_name = $class->short_name;
            $c->academic_class_level_id = $class->id;
            $c->details = $class->name;
            $c->save();
            AcademicClass::generate_subjects($c);
        }
    }
}
