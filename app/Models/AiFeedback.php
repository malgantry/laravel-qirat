<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiFeedback extends Model
{
    protected $table = 'ai_feedbacks';

    protected $fillable = [
        'feedback_id',
        'user_id',
        'action',
        'object_type',
        'object_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
