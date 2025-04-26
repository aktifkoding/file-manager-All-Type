@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
  <div class="col">
    <h1>Upload File</h1>
  </div>
  <div class="col text-end">
    <a href="{{ route('files.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<!-- Error Alert -->
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<!-- Upload Form Card -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-upload"></i> Form Upload File</h3>
  </div>
  <form method="POST" enctype="multipart/form-data" action="{{ route('files.store') }}">
    @csrf
    <div class="card-body">
      <div class="mb-3">
        <label for="category_id" class="form-label">Pilih Kategori:</label>
        <select id="category_id" name="category_id" required class="form-select">
          <option value="">-- Pilih Kategori --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label for="file" class="form-label">Pilih File:</label>
        <input id="file" type="file" name="file" required class="form-control">
      </div>

      <!-- Preview Container -->
      <div id="preview-container" class="mb-3 d-none">
        <label class="form-label">Preview:</label>
        <div>
          <!-- Image Preview -->
          <img id="preview-image" src="" alt="Preview Gambar" class="img-fluid d-none" style="max-height: 200px;" />
          <!-- PDF Preview -->
          <embed id="preview-pdf" src="" type="application/pdf" class="d-none" style="width:100%; height:300px;" />
          <!-- Video Preview -->
          <video id="preview-video" class="d-none" controls style="max-width:100%; max-height:300px;">
            <source id="preview-video-source" src="" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          <!-- Fallback File Name -->
          <p id="preview-name" class="d-none fw-bold mt-2"></p>
        </div>
      </div>
    </div>
    <div class="card-footer text-end">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-upload"></i> Upload
      </button>
    </div>
  </form>
</div>

<!-- Preview Script -->
<script>
  document.getElementById('file').addEventListener('change', function(e) {
    const file = this.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    const previewPdf = document.getElementById('preview-pdf');
    const previewVideo = document.getElementById('preview-video');
    const previewVideoSource = document.getElementById('preview-video-source');
    const previewName = document.getElementById('preview-name');

    if (file) {
      previewContainer.classList.remove('d-none');
      // Reset all previews
      previewImage.classList.add('d-none');
      previewPdf.classList.add('d-none');
      previewVideo.classList.add('d-none');
      previewName.classList.add('d-none');

      const fileURL = URL.createObjectURL(file);

      if (file.type.startsWith('image/')) {
        previewImage.src = fileURL;
        previewImage.classList.remove('d-none');
      } else if (file.type === 'application/pdf') {
        previewPdf.src = fileURL;
        previewPdf.classList.remove('d-none');
      } else if (file.type.startsWith('video/')) {
        previewVideoSource.src = fileURL;
        previewVideo.load();
        previewVideo.classList.remove('d-none');
      } else {
        previewName.textContent = file.name;
        previewName.classList.remove('d-none');
      }
    } else {
      previewContainer.classList.add('d-none');
    }
  });
</script>
@endsection
