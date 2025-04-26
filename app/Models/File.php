<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = ['category_id','original_name','path','size'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Helper untuk download
    public function download()
    {
        return Storage::download($this->path, $this->original_name);
    }
}
