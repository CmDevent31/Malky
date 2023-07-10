<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $fillable = [
        'title','news_content','author','image' 
    ];

    /**
     * Get the writer that owns the post
     *
     * @return BelongsTo*/
    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class,'author', 'id');
    }
}
