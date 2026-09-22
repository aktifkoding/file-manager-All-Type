@extends('layouts.app')

@section('title', $file->original_name . ' — File Manager')

@php
    $showExt = $file->extension;
    $showIconMap = [
      'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
      'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel', 'ppt' => 'fa-file-powerpoint',
      'pptx' => 'fa-file-powerpoint', 'txt' => 'fa-file-lines', 'csv' => 'fa-file-csv',
      'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image',
      'gif' => 'fa-file-image', 'webp' => 'fa-file-image',
      'mp4' => 'fa-file-video', 'webm' => 'fa-file-video', 'zip' => 'fa-file-zipper',
    ];
    $iconClass = $showIconMap[$showExt] ?? 'fa-file';
@endphp

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1 class="text-truncate" style="max-width:520px;">
      <i class="fas {{ $iconClass ?? 'fa-file' }} mr-2"></i>{{ $file->original_name }}
    </h1>
    <p>Detail informasi file.</p>
  </div>
  <div>
    <a href="{{ route('files.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
  </div>
</div>

<div class="row">
  <!-- Preview -->
  <div class="col-lg-7 mb-3">
    <div class="fm-card h-100">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-eye mr-2"></i>Preview</h3>
      </div>
      <div class="p-4 text-center">
        @php
          $mime = $file->mime_type ?? '';
          $isImage = str_starts_with($mime, 'image/');
          $isPdf = $mime === 'application/pdf';
          $isVideo = str_starts_with($mime, 'video/');
        @endphp

        @if ($isImage)
          <img src="{{ route('files.download', $file) }}" alt="{{ $file->original_name }}" class="fm-detailimg">
        @elseif ($isPdf)
          <embed src="{{ route('files.download', $file) }}" type="application/pdf" class="fm-detailembed">
        @elseif ($isVideo)
          <video src="{{ route('files.download', $file) }}" controls class="fm-detailvideo"></video>
        @else
          <div class="fm-empty py-5">
            <i class="fas {{ $iconClass ?? 'fa-file' }}"></i>
            <h5 class="font-weight-bold" style="color:var(--fm-ink-soft);">Tidak ada preview</h5>
            <p class="mb-0">Preview tersedia untuk gambar, PDF, dan video.</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Info -->
  <div class="col-lg-5 mb-3">
    <div class="fm-card mb-3">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-circle-info mr-2"></i>Informasi</h3>
      </div>
      <div class="p-4">
        <table class="table table-sm table-borderless fm-infotable mb-0">
          <tr>
            <td>Nama File</td>
            <td class="font-weight-bold text-break">{{ $file->original_name }}</td>
          </tr>
          <tr>
            <td>Kategori</td>
            <td>
              @php
                $badgeMap = [1 => 'badge-doc', 2 => 'badge-image', 3 => 'badge-video', 4 => 'badge-other'];
              @endphp
              <span class="badge badge-kategori {{ $badgeMap[$file->category_id] ?? 'badge-muted' }}">{{ $file->category->name }}</span>
            </td>
          </tr>
          <tr>
            <td>Ukuran</td>
            <td class="font-weight-bold">{{ $file->human_size }}</td>
          </tr>
          <tr>
            <td>Tipe MIME</td>
            <td><code>{{ $file->mime_type ?? '-' }}</code></td>
          </tr>
          <tr>
            <td>Diupload</td>
            <td class="font-weight-bold">{{ $file->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</td>
          </tr>
          <tr>
            <td>Pemilik</td>
            <td class="font-weight-bold">{{ $file->user->name ?? '-' }}</td>
          </tr>
        </table>
      </div>
    </div>

    <div class="fm-card">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-share mr-2"></i>Bagikan</h3>
      </div>
      <div class="p-4">
        <label class="fm-filter" style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--fm-muted); display:block; margin-bottom:.4rem;">Link Download</label>
        <div class="d-flex" style="gap:.5rem;">
          <input id="copy-link" type="text" class="form-control" readonly value="{{ route('files.download', $file) }}" onclick="this.select()">
          <button type="button" id="btn-copy" class="btn btn-primary" style="white-space:nowrap;">
            <i class="fas fa-copy mr-1"></i> Copy
          </button>
        </div>
        <p class="fm-skeleton-note mt-2 mb-0">
          <i class="fas fa-lock mr-1"></i> Link hanya bisa diakses setelah login sebagai pemilik file.
        </p>
      </div>
    </div>

    <div class="d-flex mt-3" style="gap:.5rem;">
      <a href="{{ route('files.download', $file) }}" class="btn btn-primary flex-grow-1">
        <i class="fas fa-download mr-1"></i> Download
      </a>
      <form method="POST" action="{{ route('files.destroy', $file) }}" onsubmit="return confirm('Pindahkan file ini ke trash?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash mr-1"></i> Hapus
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('btn-copy').addEventListener('click', function () {
    var input = document.getElementById('copy-link');
    input.select();
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(input.value).then(function () {
        var btn = document.getElementById('btn-copy');
        var old = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Tersalin!';
        setTimeout(function () { btn.innerHTML = old; }, 2000);
      });
    } else {
      document.execCommand('copy');
    }
  });
</script>
@endpush
