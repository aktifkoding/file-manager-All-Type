@extends('layouts.app')

@section('title', 'Daftar File — File Manager')

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1><i class="fas fa-folder-open mr-2"></i>Daftar File</h1>
    <p>Kelola semua file Anda — pilih file untuk aksi massal.</p>
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
          <label for="per_page">Per Hal.</label>
          <select id="per_page" name="per_page" class="form-select w-100">
            @foreach ([10, 25, 50, 100] as $opt)
              <option value="{{ $opt }}" @selected((int) request('per_page', 10) === $opt)>{{ $opt }}</option>
            @endforeach
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

  <!-- Bulk Actions -->
  <div class="px-4 py-2 border-bottom d-flex flex-wrap align-items-center" style="gap:.5rem; background:#f8fafc;">
    <span class="text-fm-muted" style="font-size:.8rem; font-weight:600;"><i class="fas fa-layer-group mr-1"></i> Aksi Massal:</span>
    <form id="bulk-form" method="POST" action="{{ route('files.bulk') }}" class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
      @csrf
      <input type="hidden" name="action" id="bulk-action" value="delete">
      <select name="category" id="bulk-category" class="form-select form-select-sm d-none" style="width:auto;">
        <option value="">-- Pilih kategori --</option>
        @foreach ($categories as $cat)
          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-sm btn-secondary" data-bulk="download" disabled>
        <i class="fas fa-file-zipper mr-1"></i> Download ZIP
      </button>
      <button type="submit" class="btn btn-sm btn-secondary" data-bulk="category" disabled>
        <i class="fas fa-tags mr-1"></i> Pindah Kategori
      </button>
      <button type="submit" class="btn btn-sm btn-danger" data-bulk="delete" disabled>
        <i class="fas fa-trash mr-1"></i> Hapus
      </button>
    </form>
    <span id="bulk-count" class="badge badge-muted ml-auto">0 dipilih</span>
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
            <th style="width:36px;"><input type="checkbox" id="select-all" title="Pilih semua"></th>
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
            <td><input type="checkbox" class="row-check" name="ids[]" value="{{ $file->id }}" form="bulk-form"></td>
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
                  <a href="{{ route('files.show', $file) }}" class="stretched-link" style="position:static;">{{ $file->original_name }}</a>
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
              <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary" title="Download">
                <i class="fas fa-download"></i>
              </a>
              <form method="POST" action="{{ route('files.destroy', $file) }}" class="d-inline"
                    onsubmit="return confirm('Pindahkan file &quot;{{ $file->original_name }}&quot; ke trash?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Hapus (ke trash)">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6">
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

@push('scripts')
<script>
  (function () {
    var form = document.getElementById('bulk-form');
    var actionInput = document.getElementById('bulk-action');
    var categorySelect = document.getElementById('bulk-category');
    var countBadge = document.getElementById('bulk-count');
    var buttons = form.querySelectorAll('[data-bulk]');
    var checks = document.querySelectorAll('.row-check');
    var selectAll = document.getElementById('select-all');

    function selected() {
      return Array.prototype.filter.call(checks, function (c) { return c.checked; });
    }

    function refresh() {
      var n = selected().length;
      countBadge.textContent = n + ' dipilih';
      buttons.forEach(function (b) { b.disabled = n === 0; });
    }

    selectAll.addEventListener('change', function () {
      checks.forEach(function (c) { c.checked = selectAll.checked; });
      refresh();
    });

    checks.forEach(function (c) { c.addEventListener('change', refresh); });

    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        actionInput.value = b.getAttribute('data-bulk');
        categorySelect.classList.toggle('d-none', actionInput.value !== 'category');

        if (actionInput.value === 'delete') {
          var n = selected().length;
          if (!confirm('Pindahkan ' + n + ' file ke trash?')) {
            event.preventDefault();
            return;
          }
        }
        if (actionInput.value === 'category' && !categorySelect.value) {
          alert('Pilih kategori tujuan terlebih dahulu.');
          event.preventDefault();
        }
      });
    });
  })();
</script>
@endpush
