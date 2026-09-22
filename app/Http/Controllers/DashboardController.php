<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $totalFiles  = File::owned($userId)->count();
        $totalSize   = File::owned($userId)->sum('size');
        $trashedCnt  = File::owned($userId)->onlyTrashed()->count();

        $stats = File::owned($userId)
            ->whereHas('category')
            ->with('category')
            ->get(['id', 'category_id', 'size']);

        $catNames = ['Dokumen', 'Gambar', 'Video', 'Lainnya'];

        // Data untuk doughnut chart
        $byCategory = [];
        $sizeByCategory = [];
        foreach ($catNames as $name) {
            $rows = $stats->filter(fn ($f) => $f->category->name === $name);
            $byCategory[$name]     = $rows->count();
            $sizeByCategory[$name] = (int) $rows->sum('size');
        }

        // Data untuk bar chart 7 hari terakhir
        $uploads7days = [];
        $labels7days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $uploads7days[] = File::owned($userId)
                ->whereDate('created_at', $day->toDateString())
                ->count();
            $labels7days[] = $day->locale('id')->isoFormat('ddd');
        }

        $recent = File::owned($userId)->with('category')->latest()->take(5)->get();

        return view('dashboard', [
            'totalFiles'     => $totalFiles,
            'totalSize'      => $totalSize,
            'trashedCount'   => $trashedCnt,
            'byCategory'     => $byCategory,
            'sizeByCategory' => $sizeByCategory,
            'uploads7days'   => $uploads7days,
            'labels7days'    => $labels7days,
            'recent'         => $recent,
        ]);
    }
}
