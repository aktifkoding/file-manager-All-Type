<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'category_id', 'original_name', 'path', 'mime_type', 'size'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: hanya file milik user tertentu.
     */
    public function scopeOwned(Builder $query, ?int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Helper untuk download.
     */
    public function download()
    {
        return Storage::download($this->path, $this->original_name);
    }

    /**
     * Hapus file fisik dari storage (dipanggil saat permanent delete).
     */
    public function deleteFromStorage(): void
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }
    }

    /**
     * Versi statis untuk format byte dari dashboard.
     */
    public static function humanSizeStatic($bytes): string
    {
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf($i === 0 ? '%.0f %s' : '%.2f %s', $bytes, $units[$i]);
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
