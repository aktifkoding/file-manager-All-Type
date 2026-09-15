<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Kolom yang diizinkan untuk sorting (mencegah SQL injection).
     */
    private const SORTABLE_COLUMNS = ['original_name', 'created_at', 'size'];

    public function index(Request $request)
    {
        $query = File::with('category');

        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter nama
        if ($request->filled('name')) {
            $query->where('original_name', 'like', '%' . $request->name . '%');
        }

        // Filter tanggal (range) — masing-masing tanggal opsional, jadikan inclusive
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting (whitelist kolom + arah)
        if (in_array($request->sort_by, self::SORTABLE_COLUMNS, true)) {
            $direction = $request->direction === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }

        $files = $query->paginate(10)->appends($request->query());
        $categories = Category::all();

        // Hitung jumlah berdasarkan kategori
        $countDocuments = File::whereHas('category', fn ($q) => $q->where('name', 'Dokumen'))->count();
        $countImages    = File::whereHas('category', fn ($q) => $q->where('name', 'Gambar'))->count();
        $countVideos    = File::whereHas('category', fn ($q) => $q->where('name', 'Video'))->count();
        $countOthers    = File::whereHas('category', fn ($q) => $q->where('name', 'Lainnya'))->count();

        return view('files.index', compact(
            'files', 'categories', 'countDocuments', 'countImages', 'countVideos', 'countOthers'
        ));
    }

    public function create()
    {
        $categories = Category::all();

        return view('files.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // mimes + max 5MB (5120 KB) — 5MB dihitung dari basis 1000 KB oleh Laravel
            'file'        => 'required|file|max:5000|mimes:jpg,jpeg,png,gif,webp,pdf,mp4,webm,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
            'category_id' => 'required|exists:categories,id',
        ], [
            'file.max'   => 'Ukuran file maksimal 5 MB.',
            'file.mimes' => 'Tipe file tidak diizinkan.',
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('uploads');

        $file = File::create([
            'category_id'   => $request->category_id,
            'original_name' => $uploaded->getClientOriginalName(),
            'path'          => $path,
            'size'          => $uploaded->getSize(),
        ]);

        if ($file === null || ! Storage::exists($file->path)) {
            // Rollback DB entry bila file gagal tersimpan di disk
            optional($file)->delete();

            return back()->withInput()->withErrors(['file' => 'Gagal menyimpan file. Silakan coba lagi.']);
        }

        return redirect()
            ->route('files.index')
            ->with('success', 'File "' . $file->original_name . '" berhasil di-upload!');
    }

    public function download(File $file)
    {
        abort_unless(Storage::exists($file->path), 404, 'File tidak ditemukan di penyimpanan.');

        return $file->download();
    }
}
