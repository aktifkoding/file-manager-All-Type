<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class FileController extends Controller
{
    /** Kolom yang diizinkan untuk sorting (mencegah SQL injection). */
    private const SORTABLE_COLUMNS = ['original_name', 'created_at', 'size'];

    /** Ekstensi file yang diizinkan. */
    private const ALLOWED_EXTENSIONS = 'jpg,jpeg,png,gif,webp,pdf,mp4,webm,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip';

    /** Ukuran maksimal per file dalam kilobyte. */
    private const MAX_FILE_KB = 5000;

    public function index(Request $request)
    {
        $query = File::owned($request->user()->id)->with('category');
        $this->applyFilters($query, $request);

        $files = $query->paginate($this->perPage($request))->appends($request->query());
        $categories = Category::all();

        $counts = $this->categoryCounts($request->user()->id);

        return view('files.index', [
            'files'          => $files,
            'categories'     => $categories,
            'countDocuments' => $counts['Dokumen'],
            'countImages'    => $counts['Gambar'],
            'countVideos'    => $counts['Video'],
            'countOthers'    => $counts['Lainnya'],
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return view('files.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files'       => ['required', 'array', 'min:1', 'max:10'],
            'files.*'     => [
                'required',
                'file',
                'max:' . self::MAX_FILE_KB,
                'mimes:' . self::ALLOWED_EXTENSIONS,
            ],
            'category_id' => ['required', 'exists:categories,id'],
        ], [
            'files.required'         => 'Pilih minimal satu file.',
            'files.max'              => 'Maksimal 10 file per upload.',
            'files.*.max'            => 'Ukuran file maksimal 5 MB.',
            'files.*.mimes'          => 'Tipe file tidak diizinkan.',
            'category_id.required'   => 'Pilih kategori terlebih dahulu.',
            'category_id.exists'     => 'Kategori tidak valid.',
        ]);

        $stored = 0;

        foreach ($request->file('files') as $uploaded) {
            $path = $uploaded->store('uploads');

            $file = File::create([
                'user_id'       => $request->user()->id,
                'category_id'   => $request->category_id,
                'original_name' => $uploaded->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $uploaded->getMimeType(),
                'size'          => $uploaded->getSize(),
            ]);

            if ($file === null || ! Storage::exists($file->path)) {
                optional($file)->delete();
                continue;
            }

            $stored++;
        }

        if ($stored === 0) {
            return back()->withErrors(['files' => 'Gagal menyimpan file. Silakan coba lagi.']);
        }

        $message = $stored === 1
            ? '1 file berhasil di-upload!'
            : $stored . ' file berhasil di-upload!';

        return redirect()->route('files.index')->with('success', $message);
    }

    public function show(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);

        return view('files.show', [
            'file'       => $file->load('category', 'user'),
            'storageTot' => File::owned($request->user()->id)->sum('size'),
        ]);
    }

    public function download(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);
        abort_unless(Storage::exists($file->path), 404, 'File tidak ditemukan di penyimpanan.');

        return $file->download();
    }

    public function destroy(Request $request, File $file)
    {
        $this->authorizeFile($request, $file);
        $file->delete(); // soft delete → masuk trash

        return back()->with('success', 'File "' . $file->original_name . '" dipindahkan ke trash.');
    }

    public function trash(Request $request)
    {
        $query = File::owned($request->user()->id)->onlyTrashed()->with('category');
        $this->applyFilters($query, $request);

        $files = $query->paginate($this->perPage($request))->appends($request->query());

        return view('files.trash', compact('files'));
    }

    public function restore(Request $request, int $id)
    {
        $file = File::owned($request->user()->id)->onlyTrashed()->findOrFail($id);
        $file->restore();

        return back()->with('success', 'File "' . $file->original_name . '" berhasil dipulihkan.');
    }

    public function forceDelete(Request $request, int $id)
    {
        $file = File::owned($request->user()->id)->onlyTrashed()->findOrFail($id);
        $file->deleteFromStorage();
        $file->forceDelete();

        return back()->with('success', 'File "' . $file->original_name . '" dihapus permanen.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action'    => ['required', 'in:delete,restore,force_delete,category,download'],
            'ids'       => ['required', 'array', 'min:1'],
            'ids.*'     => ['integer'],
            'category'  => ['nullable', 'exists:categories,id'],
        ]);

        $action = $request->action;

        if ($action === 'category') {
            $request->validate(['category' => ['required', 'exists:categories,id']]);
            $query->update(['category_id' => $request->category]);
            $message = $count . ' file dipindahkan ke kategori baru.';
        } elseif ($action === 'delete') {
            foreach ($query->get() as $file) {
                $file->delete();
            }
            $message = $count . ' file dipindahkan ke trash.';
        } elseif ($action === 'restore') {
            $trashed = File::owned($request->user()->id)->onlyTrashed()->whereIn('id', $request->ids)->get();
            foreach ($trashed as $file) {
                $file->restore();
            }
            $message = $trashed->count() . ' file dipulihkan.';
        } elseif ($action === 'force_delete') {
            $trashed = File::owned($request->user()->id)->onlyTrashed()->whereIn('id', $request->ids)->get();
            foreach ($trashed as $file) {
                $file->deleteFromStorage();
                $file->forceDelete();
            }
            $message = $trashed->count() . ' file dihapus permanen.';
        } else { // download (ZIP)
            $files = $query->get();
            if ($files->isEmpty()) {
                return back()->withErrors(['files' => 'Tidak ada file untuk di-download.']);
            }

            return $this->downloadAsZip($files);
        }

        return back()->with('success', $message);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'daftar-file-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nama File', 'Kategori', 'Ukuran', 'Tipe MIME', 'Tanggal Upload']);

            File::owned($request->user()->id)->with('category')
                ->orderBy('created_at', 'desc')
                ->chunk(500, function ($files) use ($out) {
                    foreach ($files as $file) {
                        fputcsv($out, [
                            $file->original_name,
                            $file->category->name ?? '-',
                            $file->human_size,
                            $file->mime_type ?? '-',
                            $file->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($out);
        }, $fileName, $headers);
    }

    /* -----------------------------------------------------------------
     |  Private helpers
     | ----------------------------------------------------------------- */

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('category')) {
            $query->where('files.category_id', $request->category);
        }

        if ($request->filled('name')) {
            $query->where('original_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if (in_array($request->sort_by, self::SORTABLE_COLUMNS, true)) {
            $direction = $request->direction === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }
    }

    private function perPage(Request $request): int
    {
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }

    private function categoryCounts(int $userId): array
    {
        $rows = File::owned($userId)
            ->whereHas('category')
            ->with('category')
            ->get(['category_id'])
            ->countBy(fn ($f) => $f->category->name);

        return [
            'Dokumen' => $rows['Dokumen'] ?? 0,
            'Gambar'  => $rows['Gambar'] ?? 0,
            'Video'   => $rows['Video'] ?? 0,
            'Lainnya' => $rows['Lainnya'] ?? 0,
        ];
    }

    private function authorizeFile(Request $request, File $file): void
    {
        abort_unless($file->user_id === $request->user()->id, 403, 'Anda tidak memiliki akses ke file ini.');
    }

    private function downloadAsZip($files)
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'fm_zip_');
        $zip = new ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['files' => 'Gagal membuat arsip ZIP.']);
        }

        foreach ($files as $file) {
            if (Storage::exists($file->path)) {
                $zip->addFromString($file->original_name, Storage::get($file->path));
            }
        }
        $zip->close();

        return response()->download($zipFile, 'files-' . now()->format('Ymd-His') . '.zip')
            ->deleteFileAfterSend(true);
    }
}
