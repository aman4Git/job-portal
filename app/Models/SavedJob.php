<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedJob extends Model
{
    use HasFactory;

    //Custom Relations
    public function job(){
        return $this->belongsTo(Job::class);
    }
}
