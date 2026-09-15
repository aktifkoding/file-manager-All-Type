@extends('layouts.app')

@section('title', 'Daftar File — File Manager')

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1><i class="fas fa-folder-open mr-2"></i>Daftar File</h1>
    <p>Kelola semua file yang telah di-upload dalam satu tempat.</p>
  </div>
  <div>
    <a href="{{ route('files.create') }}" class="btn btn-success">
      <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File Baru
    </a>
  </div>
</div>

<!-- Statistic Cards -->
<div class="row">
  <div class="col-lg-3 col-6">
    <a href="{{ route('files.index', ['category' => $categories->firstWhere('name', 'Dokumen')?->id ?? '']) }}"
       class="fm-stat fm-stat-docs fm-stat-clickable mb-3">
      <div class="inner">
        <h3>{{ $countDocuments }}</h3>
        <p>Dokumen</p>
      </div>
      <div class="icon"><i class="fas fa-file-alt"></i></div>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('files.index', ['category' => $categories->firstWhere('name', 'Gambar')?->id ?? '']) }}"
       class="fm-stat fm-stat-images fm-stat-clickable mb-3">
      <div class="inner">
        <h3>{{ $countImages }}</h3>
        <p>Gambar</p>
      </div>
      <div class="icon"><i class="fas fa-image"></i></div>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('files.index', ['category' => $categories->firstWhere('name', 'Video')?->id ?? '']) }}"
       class="fm-stat fm-stat-videos fm-stat-clickable mb-3">
      <div class="inner">
        <h3>{{ $countVideos }}</h3>
        <p>Video</p>
      </div>
      <div class="icon"><i class="fas fa-video"></i></div>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('files.index', ['category' => $categories->firstWhere('name', 'Lainnya')?->id ?? '']) }}"
       class="fm-stat fm-stat-others fm-stat-clickable mb-3">
      <div class="inner">
        <h3>{{ $countOthers }}</h3>
        <p>Lainnya</p>
      </div>
      <div class="icon"><i class="fas fa-folder"></i></div>
    </a>
  </div>
</div>

<!-- Filter & Table Card -->
<div class="fm-card">
  <!-- Filter -->
  <div class="card-header">
    <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-2"></i>Filter & Pencarian</h3>
  </div>
  <div class="px-4 py-3 border-bottom">
    <form method="GET" action="{{ route('files.index') }}" class="fm-filter">
      <div class="row">
        <div class="col-lg-2 col-md-3 col-6 mb-2">
          <label for="category">Kategori</label>
          <select id="category" name="category" class="form-select w-100">
            <option value="">Semua</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3 col-md-3 col-6 mb-2">
          <label for="name">Nama File</label>
          <div class="position-relative">
            <i class="fas fa-search position-absolute text-fm-muted" style="left:12px; top:50%; transform:translateY(-50%); font-size:.8rem;"></i>
            <input id="name" type="text" name="name" value="{{ request('name') }}" class="form-control" style="padding-left:2rem;" placeholder="Cari nama file...">
          </div>
        </div>
        <div class="col-lg-2 col-md-2 col-6 mb-2">
          <label for="date_from">Dari Tanggal</label>
          <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="form-control w-100">
        </div>
        <div class="col-lg-2 col-md-2 col-6 mb-2">
          <label for="date_to">Sampai Tanggal</label>
          <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="form-control w-100">
        </div>
        <div class="col-lg-2 col-md-1 col-6 mb-2">
          <label for="sort_by">Urutkan</label>
          <select id="sort_by" name="sort_by" class="form-select w-100">
            <option value="">Terbaru</option>
            <option value="original_name" @selected(request('sort_by') == 'original_name')>Nama A-Z</option>
            <option value="created_at" @selected(request('sort_by') == 'created_at')>Tanggal</option>
            <option value="size" @selected(request('sort_by') == 'size')>Ukuran</option>
          </select>
        </div>
        <div class="col-lg-1 col-md-1 col-6 mb-2">
          <label for="direction">Arah</label>
          <select id="direction" name="direction" class="form-select w-100">
            <option value="asc" @selected(request('direction') == 'asc')>↑</option>
            <option value="desc" @selected(request('direction') == 'desc')>↓</option>
          </select>
        </div>
        <div class="col-12 d-flex mt-2" style="gap:.5rem;">
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="fas fa-search mr-1"></i> Terapkan
          </button>
          <a href="{{ route('files.index') }}" class="btn btn-secondary btn-sm px-3">
            <i class="fas fa-rotate mr-1"></i> Reset
          </a>
        </div>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="px-4 py-3">
    <div class="d-flex align-items-center mb-3">
      <h3 class="mb-0 font-weight-bold" style="font-size:1rem;"><i class="fas fa-table mr-2" style="color:var(--fm-primary);"></i>Daftar File</h3>
      <span class="badge badge-muted ml-2">{{ $files->total() }} file</span>
    </div>
    <div class="table-responsive">
      <table class="table fm-table align-middle">
        <thead>
          <tr>
            <th>Nama File</th>
            <th>Kategori</th>
            <th>Ukuran</th>
            <th>Tanggal Upload</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($files as $file)
          <tr>
            <td>
              @php
                $ext = $file->extension;
                $iconMap = [
                  'pdf' => ['fa-file-pdf', 'fm-fi-doc'], 'doc' => ['fa-file-word', 'fm-fi-doc'],
                  'docx' => ['fa-file-word', 'fm-fi-doc'], 'xls' => ['fa-file-excel', 'fm-fi-doc'],
                  'xlsx' => ['fa-file-excel', 'fm-fi-doc'], 'ppt' => ['fa-file-powerpoint', 'fm-fi-doc'],
                  'pptx' => ['fa-file-powerpoint', 'fm-fi-doc'], 'txt' => ['fa-file-lines', 'fm-fi-doc'],
                  'csv' => ['fa-file-csv', 'fm-fi-doc'],
                  'jpg' => ['fa-file-image', 'fm-fi-image'], 'jpeg' => ['fa-file-image', 'fm-fi-image'],
                  'png' => ['fa-file-image', 'fm-fi-image'], 'gif' => ['fa-file-image', 'fm-fi-image'],
                  'webp' => ['fa-file-image', 'fm-fi-image'],
                  'mp4' => ['fa-file-video', 'fm-fi-video'], 'webm' => ['fa-file-video', 'fm-fi-video'],
                  'zip' => ['fa-file-zipper', 'fm-fi-archive'],
                ];
                [$iconClass, $colorClass] = $iconMap[$ext] ?? ['fa-file', 'fm-fi-other'];
              @endphp
              <div class="fm-filecell">
                <span class="fm-fileicon {{ $colorClass }}"><i class="fas {{ $iconClass }}"></i></span>
                <span class="fm-filename">
                  {{ $file->original_name }}
                  <small>{{ strtoupper($ext ?: 'FILE') }}</small>
                </span>
              </div>
            </td>
            <td>
              @php
                $badgeMap = [1 => 'badge-doc', 2 => 'badge-image', 3 => 'badge-video', 4 => 'badge-other'];
              @endphp
              <span class="badge badge-kategori {{ $badgeMap[$file->category_id] ?? 'badge-muted' }}">
                {{ $file->category->name }}
              </span>
            </td>
            <td class="text-nowrap">{{ $file->human_size }}</td>
            <td class="text-nowrap text-fm-muted">{{ \Carbon\Carbon::parse($file->created_at)->locale('id')->isoFormat('D MMM Y') }}</td>
            <td class="text-right text-nowrap">
              <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary" title="Download {{ $file->original_name }}">
                <i class="fas fa-download"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5">
              <div class="fm-empty">
                <i class="fas fa-inbox"></i>
                <h5 class="font-weight-bold" style="color:var(--fm-ink-soft);">Belum ada file</h5>
                <p class="mb-3">Belum ada file yang cocok dengan filter saat ini.</p>
                <a href="{{ route('files.create') }}" class="btn btn-primary">
                  <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File Pertama
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  @if ($files->hasPages())
    <div class="px-4 py-3 border-top d-flex justify-content-center">
      {{ $files->onEachSide(1)->links('pagination::bootstrap-4') }}
    </div>
  @endif
</div>
@endsection
