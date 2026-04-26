<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'bug_id',
        'name',
        'description',
    ];

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class, 'bug_id');
    }
}
