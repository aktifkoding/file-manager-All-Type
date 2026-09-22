@extends('layouts.app')

@section('title', 'Trash — File Manager')

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1><i class="fas fa-trash-can mr-2"></i>Trash</h1>
    <p>File terhapus tetap di sini sebelum dihapus permanen.</p>
  </div>
  <div>
    <a href="{{ route('files.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
    </a>
  </div>
</div>

<div class="fm-card">
  <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-recycle mr-2"></i>File Terhapus</h3>
    <form id="trash-bulk-form" method="POST" action="{{ route('files.bulk') }}" class="d-flex" style="gap:.5rem;">
      @csrf
      <input type="hidden" name="action" id="trash-bulk-action" value="restore">
      <button type="submit" class="btn btn-sm btn-secondary" id="bulk-restore" data-action="restore" disabled>
        <i class="fas fa-rotate-left mr-1"></i> Pulihkan Terpilih
      </button>
      <button type="submit" class="btn btn-sm btn-danger" id="bulk-force" data-action="force_delete" disabled>
        <i class="fas fa-xmark mr-1"></i> Hapus Permanen
      </button>
    </form>
  </div>
  <div class="px-4 py-3">
    <div class="table-responsive">
      <table class="table fm-table align-middle">
        <thead>
          <tr>
            <th style="width:36px;"><input type="checkbox" id="trash-select-all"></th>
            <th>Nama File</th>
            <th>Kategori</th>
            <th>Ukuran</th>
            <th>Dihapus</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($files as $file)
          <tr>
            <td>
              <input type="checkbox" class="trash-check" form="trash-bulk-form" value="{{ $file->id }}">
            </td>
            <td>
              @php
                $ext = $file->extension;
                $iconMap = [
                  'pdf' => ['fa-file-pdf', 'fm-fi-doc'], 'doc' => ['fa-file-word', 'fm-fi-doc'],
                  'docx' => ['fa-file-word', 'fm-fi-doc'], 'xls' => ['fa-file-excel', 'fm-fi-doc'],
                  'xlsx' => ['fa-file-excel', 'fm-fi-doc'], 'txt' => ['fa-file-lines', 'fm-fi-doc'],
                  'jpg' => ['fa-file-image', 'fm-fi-image'], 'png' => ['fa-file-image', 'fm-fi-image'],
                  'mp4' => ['fa-file-video', 'fm-fi-video'], 'zip' => ['fa-file-zipper', 'fm-fi-archive'],
                ];
                [$iconClass, $colorClass] = $iconMap[$ext] ?? ['fa-file', 'fm-fi-other'];
              @endphp
              <div class="fm-filecell">
                <span class="fm-fileicon {{ $colorClass }}" style="opacity:.5;"><i class="fas {{ $iconClass }}"></i></span>
                <span class="fm-filename" style="opacity:.75;">
                  {{ $file->original_name }}
                  <small>{{ strtoupper($ext ?: 'FILE') }}</small>
                </span>
              </div>
            </td>
            <td>{{ $file->category->name ?? '-' }}</td>
            <td class="text-nowrap">{{ $file->human_size }}</td>
            <td class="text-nowrap text-fm-muted">{{ $file->deleted_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}</td>
            <td class="text-right text-nowrap">
              <form method="POST" action="{{ route('files.restore', $file->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary" title="Pulihkan">
                  <i class="fas fa-rotate-left"></i>
                </button>
              </form>
              <form method="POST" action="{{ route('files.force-delete', $file->id) }}" class="d-inline"
                    onsubmit="return confirm('Hapus PERMANEN file &quot;{{ $file->original_name }}&quot;?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Hapus permanen">
                  <i class="fas fa-xmark"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6">
              <div class="fm-empty">
                <i class="fas fa-trash-can"></i>
                <h5 class="font-weight-bold" style="color:var(--fm-ink-soft);">Trash kosong</h5>
                <p class="mb-0">Tidak ada file terhapus. Bagus! 🎉</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

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
    var selectAll = document.getElementById('trash-select-all');
    var checks = document.querySelectorAll('.trash-check');
    var btnRestore = document.getElementById('bulk-restore');
    var btnForce = document.getElementById('bulk-force');

    function refresh() {
      var n = Array.prototype.filter.call(checks, function (c) { return c.checked; }).length;
      btnRestore.disabled = n === 0;
      btnForce.disabled = n === 0;
    }

    selectAll.addEventListener('change', function () {
      checks.forEach(function (c) { c.checked = selectAll.checked; });
      refresh();
    });
    checks.forEach(function (c) { c.addEventListener('change', refresh); });

    [btnRestore, btnForce].forEach(function (b) {
      b.addEventListener('click', function () {
        document.getElementById('trash-bulk-action').value = b.getAttribute('data-action');
        if (b.getAttribute('data-action') === 'force_delete') {
          if (!confirm('Hapus PERMANEN file terpilih? Tindakan ini tidak bisa dibatalkan.')) {
            event.preventDefault();
          }
        }
      });
    });
  })();
</script>
@endpush
