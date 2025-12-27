<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MruAcademicResultExport extends Model
{
    protected $table = 'mru_academic_result_exports';

    protected $fillable = [
        'export_name',
        'export_type',
        'academic_year',
        'semester',
        'study_year',
        'programme_id',
        'specialisation_id',
        'minimum_passes_required',
        'start_range',
        'end_range',
        'sort_by',
        'excel_path',
        'pdf_path',
        'status',
        'error_message',
        'total_records',
        'created_by',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the creator of the export
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the programme
     */
    public function programme()
    {
        return $this->belongsTo(MruProgramme::class, 'programme_id', 'progcode');
    }

    /**
     * Get the academic year relation
     */
    public function academicYearRelation()
    {
        return $this->belongsTo(MruAcademicYear::class, 'academic_year', 'acadyear');
    }

    /**
     * Get the specialisation
     */
    public function specialisation()
    {
        return $this->hasOne(\stdClass::class); // Placeholder - using DB query in grid
    }

    /**
     * Scope for completed exports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed exports
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending exports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark export as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark export as completed
     */
    public function markAsCompleted($totalRecords = 0, $excelPath = null, $pdfPath = null)
    {
        $this->update([
            'status' => 'completed',
            'total_records' => $totalRecords,
            'excel_path' => $excelPath,
            'pdf_path' => $pdfPath,
        ]);
    }

    /**
     * Mark export as failed
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get display name for export type
     */
    public function getExportTypeNameAttribute()
    {
        return ucfirst($this->export_type);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
        ][$this->status] ?? 'default';
    }
}
