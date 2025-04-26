@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
  <div class="col">
    <h1>File Manager</h1>
  </div>
  <div class="col text-end">
    <a href="{{ route('files.create') }}" class="btn btn-success">
      <i class="fas fa-upload"></i> Upload File Baru
    </a>
  </div>
</div>

<!-- Statistic Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $countDocuments }}</h3>
        <p>Dokumen</p>
      </div>
      <div class="icon">
        <i class="fas fa-file-alt"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $countImages }}</h3>
        <p>Gambar</p>
      </div>
      <div class="icon">
        <i class="fas fa-image"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $countVideos }}</h3>
        <p>Video</p>
      </div>
      <div class="icon">
        <i class="fas fa-video"></i>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $countOthers }}</h3>
        <p>Lainnya</p>
      </div>
      <div class="icon">
        <i class="fas fa-folder"></i>
      </div>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter"></i> Filter & Pencarian</h3>
  </div>
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="category" class="form-label">Kategori</label>
        <select id="category" name="category" class="form-select">
          <option value="">-- Semua --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label for="name" class="form-label">Nama File</label>
        <input id="name" type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Cari nama...">
      </div>

      <div class="col-md-2">
        <label for="date_from" class="form-label">Dari</label>
        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
      </div>
      <div class="col-md-2">
        <label for="date_to" class="form-label">Sampai</label>
        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
      </div>

      <div class="col-md-2">
        <label for="sort_by" class="form-label">Sort By</label>
        <select id="sort_by" name="sort_by" class="form-select">
          <option value="">-- Default --</option>
          <option value="original_name" @selected(request('sort_by')=='original_name')>Nama</option>
          <option value="created_at" @selected(request('sort_by')=='created_at')>Tanggal</option>
          <option value="size" @selected(request('sort_by')=='size')>Ukuran</option>
        </select>
      </div>

      <div class="col-md-1">
        <label for="direction" class="form-label">Arah</label>
        <select id="direction" name="direction" class="form-select">
          <option value="asc" @selected(request('direction')=='asc')>Asc</option>
          <option value="desc" @selected(request('direction')=='desc')>Desc</option>
        </select>
      </div>

      <div class="col-md-1 text-end">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search"></i>
        </button>
      </div>

      <div class="col-md-1 text-end">
        <a href="{{ route('files.index') }}" class="btn btn-secondary">
          <i class="fas fa-sync-alt"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Files Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-table"></i> Daftar File</h3>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="files-table" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Ukuran (KB)</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($files as $file)
          <tr>
            <td>{{ $file->original_name }}</td>
            <td>
              @php
                $cat = $file->category->name;
                $badge = 'secondary';
                if($cat=='Dokumen') $badge='info';
                elseif($cat=='Gambar') $badge='success';
                elseif($cat=='Video') $badge='warning';
                elseif($cat=='Lainnya') $badge='danger';
              @endphp
              <span class="badge bg-{{ $badge }}">{{ $cat }}</span>
            </td>
            <td>{{ number_format($file->size/1024,2) }}</td>
            <td>{{ \Carbon\Carbon::parse($file->created_at)->locale('id')->isoFormat('LL') }}</td>
            <td>
              <a href="{{ route('files.download',$file) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-download"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination (optional if using DataTables) -->
<div class="mt-3">
  {{ $files->links() }}
</div>
@endsection
