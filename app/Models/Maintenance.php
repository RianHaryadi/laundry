<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'machine_id',
        'technician_id',
        'type',
        'status',
        'priority',
        'description',
        'issues_found',
        'actions_taken',
        'date',
        'start_time',
        'end_time',
        'cost',
        'cost_breakdown',
        'parts_replaced',
        'materials_used',
        'next_maintenance_date',
        'recommendations',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the machine that owns the maintenance.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the technician that performed the maintenance.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Scope a query to only include scheduled maintenances.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include in progress maintenances.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed maintenances.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include overdue maintenances.
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now())
            ->whereNotNull('next_maintenance_date');
    }

    /**
     * Scope a query to filter by maintenance type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get the formatted cost.
     */
    public function getFormattedCostAttribute(): string
    {
        return $this->cost ? 'Rp ' . number_format($this->cost, 0, ',', '.') : 'Rp 0';
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->status, '_'));
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->type, '_'));
    }

    /**
     * Get the priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Check if maintenance is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }

    /**
     * Check if maintenance is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if maintenance is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if maintenance is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Get the duration of maintenance in minutes.
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        
        return null;
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if ($duration = $this->duration) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            
            return $hours > 0 
                ? "{$hours}h {$minutes}m" 
                : "{$minutes}m";
        }
        
        return null;
    }
}