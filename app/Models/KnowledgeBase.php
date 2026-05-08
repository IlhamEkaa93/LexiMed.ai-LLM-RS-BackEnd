<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;

    // Ini yang tadi bikin error 500 (Mass Assignment)
    protected $fillable = [
        'title', 
        'category', 
        'file_path', 
        'version', 
        'description', 
        'status'
    ];
}