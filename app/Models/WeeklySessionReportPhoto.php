<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklySessionReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_session_report_id',
        'file_path',
        'original_filename',
        'order',
    ];

    /**
     * Get the report this photo belongs to.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(WeeklySessionReport::class, 'weekly_session_report_id');
    }
}
