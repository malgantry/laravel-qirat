<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_amount',
        'current_amount',
        'deadline',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
    ];

    public function getProgressAttribute(): float
    {
        $target = (float) $this->target_amount;
        if ($target <= 0) {
            return 0.0;
        }

        return min(100, round(((float) $this->current_amount / $target) * 100, 2));
    }
}
