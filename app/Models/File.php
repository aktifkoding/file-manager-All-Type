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

    // Aksesori ukuran yang mudah dibaca manusia, contoh: 1.42 MB
    public function getHumanSizeAttribute(): string
    {
        $bytes = (float) $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf($i === 0 ? '%.0f %s' : '%.2f %s', $bytes, $units[$i]);
    }

    // Aksesori ekstensi file (huruf kecil)
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }
}
