<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'uploaded_by',
        'title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'notes',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
