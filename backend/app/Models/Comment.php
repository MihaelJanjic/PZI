<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'bug_id',
        'user_id',
        'comment',
    ];

    public function bug()
    {
        return $this->belongsTo(Bug::class, 'bug_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
