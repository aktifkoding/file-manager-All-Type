@extends('layouts.app')

@section('title', 'Upload File — File Manager')

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1><i class="fas fa-cloud-upload-alt mr-2"></i>Upload File</h1>
    <p>Upload hingga 10 file sekaligus ke dalam satu kategori.</p>
  </div>
  <div>
    <a href="{{ route('files.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="fm-card mb-3">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-file-import mr-2"></i>Pilih File (bisa lebih dari satu)</h3>
      </div>
      <div class="p-4">
        <form id="upload-form" method="POST" enctype="multipart/form-data" action="{{ route('files.store') }}">
          @csrf

          <!-- Dropzone -->
          <div id="dropzone" class="fm-dropzone" role="button" tabindex="0" aria-label="Pilih atau seret file">
            <i class="fas fa-cloud-arrow-up dz-icon"></i>
            <h5>Seret & letakkan file di sini</h5>
            <p>atau <span class="font-weight-bold" style="color:var(--fm-primary);">klik untuk memilih file</span> — bisa banyak file sekaligus</p>
            <p class="dz-hint">Gambar, PDF, Office, Video, ZIP &middot; Maks. 5 MB per file &middot; Maks. 10 file</p>
          </div>
          <input type="file" name="files[]" id="file-input" class="d-none" multiple required>

          <!-- Daftar file terpilih -->
          <div id="file-info" class="d-none mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="font-weight-bold" style="font-size:.9rem;">
                <i class="fas fa-layer-group mr-1" style="color:var(--fm-primary);"></i>
                <span id="fi-count">0</span> file dipilih — <span id="fi-total">0 B</span>
              </span>
              <button type="button" id="fi-clear" class="btn btn-sm btn-secondary">Kosongkan</button>
            </div>
            <div id="file-list" class="fm-filelist"></div>
          </div>

          <!-- Preview terakhir -->
          <div id="preview-container" class="d-none mt-3">
            <label class="fm-filter" style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--fm-muted); display:block; margin-bottom:.4rem;">Preview file pertama</label>
            <div class="fm-preview p-2 text-center">
              <img id="preview-image" src="" alt="Preview Gambar" class="d-none rounded" />
              <embed id="preview-pdf" src="" type="application/pdf" class="d-none" />
              <video id="preview-video" class="d-none" controls>
                <source id="preview-video-source" src="" type="video/mp4">
              </video>
              <p id="preview-name" class="d-none font-weight-bold mb-0 py-3 text-fm-muted"></p>
            </div>
          </div>

          <!-- Submit -->
          <div class="d-flex justify-content-end mt-4">
            <button type="submit" id="btn-submit" class="btn btn-primary px-4" disabled>
              <i class="fas fa-cloud-arrow-up mr-1"></i> Upload Sekarang
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-tags mr-2"></i>Pilih Kategori</h3>
      </div>
      <div class="p-3">
        <div class="row">
          @foreach ($categories as $cat)
            @php
              $iconMap = ['Dokumen' => 'fa-file-alt', 'Gambar' => 'fa-image', 'Video' => 'fa-video', 'Lainnya' => 'fa-folder'];
            @endphp
            <div class="col-sm-6 col-12 mb-2">
              <label class="fm-cat-option">
                <input type="radio" name="category_id" value="{{ $cat->id }}" required @checked(old('category_id') == $cat->id)>
                <span class="fm-cat-box">
                  <i class="fas {{ $iconMap[$cat->name] ?? 'fa-tag' }}"></i>
                  <span>{{ $cat->name }}</span>
                </span>
              </label>
            </div>
          @endforeach
        </div>
        <p class="fm-skeleton-note mt-3 mb-0">
          <i class="fas fa-circle-info mr-1"></i> Semua file dalam satu upload memakai kategori yang sama.
        </p>
      </div>
    </div>

    <div class="fm-card">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-shield-halved mr-2"></i>Ketentuan Upload</h3>
      </div>
      <div class="p-3">
        <ul class="list-unstyled mb-0" style="color:var(--fm-ink-soft); font-size:.875rem;">
          <li class="mb-2"><i class="fas fa-check mr-2" style="color:var(--fm-success);"></i> Maks. 10 file per upload, 5 MB per file</li>
          <li class="mb-2"><i class="fas fa-check mr-2" style="color:var(--fm-success);"></i> Format gambar, PDF, Office, video & ZIP</li>
          <li class="mb-2"><i class="fas fa-check mr-2" style="color:var(--fm-success);"></i> File tersimpan privat per akun Anda</li>
          <li><i class="fas fa-check mr-2" style="color:var(--fm-success);"></i> Bisa dihapus ke trash & dipulihkan</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var MAX_SIZE = 5 * 1024 * 1024; // 5MB
    var MAX_FILES = 10;

    var dropzone = document.getElementById('dropzone');
    var fileInput = document.getElementById('file-input');
    var fileInfo = document.getElementById('file-info');
    var fileList = document.getElementById('file-list');
    var fiCount = document.getElementById('fi-count');
    var fiTotal = document.getElementById('fi-total');
    var fiClear = document.getElementById('fi-clear');
    var previewContainer = document.getElementById('preview-container');
    var previewImage = document.getElementById('preview-image');
    var previewPdf = document.getElementById('preview-pdf');
    var previewVideo = document.getElementById('preview-video');
    var previewVideoSource = document.getElementById('preview-video-source');
    var previewName = document.getElementById('preview-name');
    var btnSubmit = document.getElementById('btn-submit');

    var iconByExt = {
      pdf: 'fa-file-pdf', doc: 'fa-file-word', docx: 'fa-file-word',
      xls: 'fa-file-excel', xlsx: 'fa-file-excel', ppt: 'fa-file-powerpoint',
      pptx: 'fa-file-powerpoint', txt: 'fa-file-lines', csv: 'fa-file-csv',
      jpg: 'fa-file-image', jpeg: 'fa-file-image', png: 'fa-file-image',
      gif: 'fa-file-image', webp: 'fa-file-image',
      mp4: 'fa-file-video', webm: 'fa-file-video', zip: 'fa-file-zipper'
    };

    function formatSize(bytes) {
      if (!bytes) return '0 B';
      var units = ['B', 'KB', 'MB', 'GB'];
      var i = Math.floor(Math.log(bytes) / Math.log(1024));
      return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 2) + ' ' + units[i];
    }

    function extOf(name) {
      return (name.split('.').pop() || '').toLowerCase();
    }

    function resetPreview() {
      previewImage.classList.add('d-none');
      previewPdf.classList.add('d-none');
      previewVideo.classList.add('d-none');
      previewName.classList.add('d-none');
      previewImage.removeAttribute('src');
      previewPdf.removeAttribute('src');
      previewVideoSource.removeAttribute('src');
    }

    function showPreview(file) {
      resetPreview();
      previewContainer.classList.remove('d-none');
      var url = URL.createObjectURL(file);
      var type = file.type || '';

      if (type.indexOf('image/') === 0) {
        previewImage.src = url;
        previewImage.classList.remove('d-none');
      } else if (type === 'application/pdf') {
        previewPdf.src = url;
        previewPdf.classList.remove('d-none');
      } else if (type.indexOf('video/') === 0) {
        previewVideoSource.src = url;
        previewVideo.load();
        previewVideo.classList.remove('d-none');
      } else {
        previewName.textContent = 'Tidak ada preview untuk tipe file ini';
        previewName.classList.remove('d-none');
      }
    }

    function renderList(files) {
      fileList.innerHTML = '';
      var total = 0;

      Array.prototype.forEach.call(files, function (f) {
        total += f.size;
        var ext = extOf(f.name);
        var icon = iconByExt[ext] || 'fa-file';
        var row = document.createElement('div');
        row.className = 'fm-fileitem';
        row.innerHTML =
          '<span class="fm-fileicon fm-fi-other"><i class="fas ' + icon + '"></i></span>' +
          '<span class="flex-grow-1" style="min-width:0;">' +
            '<div class="fm-filename text-truncate">' + f.name + '</div>' +
            '<small class="text-fm-muted">' + formatSize(f.size) + '</small>' +
          '</span>';
        fileList.appendChild(row);
      });

      fiCount.textContent = files.length;
      fiTotal.textContent = formatSize(total);
      fileInfo.classList.toggle('d-none', files.length === 0);
    }

    function handleFiles(list) {
      if (!list || !list.length) return;

      var files = Array.prototype.slice.call(list);
      var valid = [];

      for (var i = 0; i < files.length; i++) {
        var f = files[i];
        if (f.size > MAX_SIZE) {
          alert('"' + f.name + '" melebihi 5 MB (' + formatSize(f.size) + ') — dilewati.');
          continue;
        }
        valid.push(f);
      }

      if (valid.length > MAX_FILES) {
        alert('Maksimal ' + MAX_FILES + ' file per upload — hanya ' + MAX_FILES + ' pertama yang dipakai.');
        valid = valid.slice(0, MAX_FILES);
      }

      if (!valid.length) {
        clearAll();
        return;
      }

      // Buat DataTransfer agar file input tetap sinkron dengan daftar valid
      var dt = new DataTransfer();
      valid.forEach(function (f) { dt.items.add(f); });
      fileInput.files = dt.files;

      renderList(fileInput.files);
      dropzone.classList.add('has-file');
      btnSubmit.disabled = false;

      showPreview(fileInput.files[0]);
    }

    function clearAll() {
      fileInput.value = '';
      renderList([]);
      dropzone.classList.remove('has-file');
      previewContainer.classList.add('d-none');
      resetPreview();
      btnSubmit.disabled = true;
    }

    // Interaksi dropzone
    dropzone.addEventListener('click', function () { fileInput.click(); });
    dropzone.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });
    ['dragenter', 'dragover'].forEach(function (ev) {
      dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.add('is-dragover'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.remove('is-dragover'); });
    });
    dropzone.addEventListener('drop', function (e) {
      var files = e.dataTransfer && e.dataTransfer.files;
      if (files && files.length) handleFiles(files);
    });

    fileInput.addEventListener('change', function () { handleFiles(this.files); });
    fiClear.addEventListener('click', clearAll);

    // Loading state saat submit
    document.getElementById('upload-form').addEventListener('submit', function () {
      if (!btnSubmit.disabled) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Mengunggah...';
      }
    });
  })();
</script>
@endpush
