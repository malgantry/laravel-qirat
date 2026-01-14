<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_id',
        'category',
        'amount',
        'occurred_at',
        'note',
    ];

    protected $casts = [
        'occurred_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function categoryRef()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
