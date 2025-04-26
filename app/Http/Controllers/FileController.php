<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::with('category');

        $files = $query->paginate(10)->appends($request->query());
        $categories = Category::all();
        
         // Hitung jumlah berdasarkan kategori
        $countDocuments = File::whereHas('category', fn($q)=>$q->where('name','Dokumen'))->count();
        $countImages    = File::whereHas('category', fn($q)=>$q->where('name','Gambar'))->count();
        $countVideos    = File::whereHas('category', fn($q)=>$q->where('name','Video'))->count();
        $countOthers    = File::whereHas('category', fn($q)=>$q->where('name','Lainnya'))->count();


        // Filter kategori
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        // Filter nama
        if ($request->filled('name')) {
            $query->where('original_name','like','%'.$request->name.'%');
        }
        // Filter tanggal (range)
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        // Sorting
        if ($request->filled('sort_by')) {
            $direction = $request->get('direction','asc');
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->latest();
        }

        $files = $query->paginate(10)->appends($request->query());
        $categories = Category::all();

        return view('files.index', compact('files','categories','countDocuments','countImages','countVideos','countOthers'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('files.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
            'category_id' => 'required|exists:categories,id'
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('uploads');

        File::create([
            'category_id'   => $request->category_id,
            'original_name' => $uploaded->getClientOriginalName(),
            'path'          => $path,
            'size'          => $uploaded->getSize(),
        ]);

        return redirect()->route('files.index')
                         ->with('success','File berhasil di-upload!');
    }

    public function download(File $file)
    {
        return $file->download();
    }
}
