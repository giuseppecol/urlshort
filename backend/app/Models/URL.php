<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class URL extends Model
{
    use HasFactory;

    protected $table = 'urls';  
    protected $fillable = ['original_url', 'short_code', 'user_id']; // Add 'user_id' here

    // Optionally, define a relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class); // Each URL belongs to a user
    }
}
