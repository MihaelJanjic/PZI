<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bug extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'severity',
        'created_by',
        'category_id',
        'assigned_admin'
    ];

    protected static function booted()
    {
        static::saving(function ($bug) {
            if ($bug->status === 'open') {
                $bug->assigned_admin = null;
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
