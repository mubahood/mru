<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MruSpecialisation Model
 * 
 * Represents academic specializations/teaching subjects for programmes (mainly Education).
 * Maps to acad_specialisation table.
 * 
 * @property int $spec_id Primary key
 * @property string $prog_id Programme code (e.g., BAED)
 * @property string $spec Specialization name (e.g., "Luganda & History")
 * @property string $abbrev Abbreviation (e.g., "L & H")
 */
class MruSpecialisation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acad_specialisation';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'spec_id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'prog_id',
        'spec',
        'abbrev',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'spec_id' => 'integer',
        'prog_id' => 'string',
        'spec' => 'string',
        'abbrev' => 'string',
    ];

    /**
     * Get the programme this specialisation belongs to
     *
     * @return BelongsTo
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(MruProgramme::class, 'prog_id', 'progcode');
    }
}
