@extends('layouts.app')

@section('title', 'Dashboard — File Manager')

@section('content')
<!-- Page Header -->
<div class="fm-pagehead">
  <div>
    <h1><i class="fas fa-chart-pie mr-2"></i>Dashboard</h1>
    <p>Ringkasan penyimpanan file Anda.</p>
  </div>
  <div>
    <a href="{{ route('files.create') }}" class="btn btn-success">
      <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File Baru
    </a>
  </div>
</div>

<!-- Statistik utama -->
<div class="row">
  <div class="col-lg-4 col-6">
    <div class="fm-stat fm-stat-docs mb-3">
      <div class="inner">
        <h3>{{ $totalFiles }}</h3>
        <p>Total File</p>
      </div>
      <div class="icon"><i class="fas fa-files"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="fm-stat fm-stat-images mb-3">
      <div class="inner">
        <h3>{{ \App\Models\File::humanSizeStatic($totalSize) }}</h3>
        <p>Total Ukuran</p>
      </div>
      <div class="icon"><i class="fas fa-hard-drive"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-12">
    <div class="fm-stat fm-stat-others mb-3">
      <div class="inner">
        <h3>{{ $trashedCount }}</h3>
        <p>Di Trash</p>
      </div>
      <div class="icon"><i class="fas fa-trash-can"></i></div>
    </div>
  </div>
</div>

<!-- Charts -->
<div class="row">
  <div class="col-lg-5 mb-3">
    <div class="fm-card h-100">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-chart-pie mr-2"></i>File per Kategori</h3>
      </div>
      <div class="p-3">
        <canvas id="chart-category" height="260"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-7 mb-3">
    <div class="fm-card h-100">
      <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-chart-column mr-2"></i>Upload 7 Hari Terakhir</h3>
      </div>
      <div class="p-3">
        <canvas id="chart-uploads" height="260"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- File terbaru -->
<div class="fm-card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-clock-rotate-left mr-2"></i>File Terbaru</h3>
    <a href="{{ route('files.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
  </div>
  <div class="px-4 py-3">
    <div class="table-responsive">
      <table class="table fm-table align-middle">
        <thead>
          <tr>
            <th>Nama File</th>
            <th>Kategori</th>
            <th>Ukuran</th>
            <th>Tanggal</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($recent as $file)
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
                  <a href="{{ route('files.show', $file) }}" style="position:static;">{{ $file->original_name }}</a>
                  <small>{{ strtoupper($ext ?: 'FILE') }}</small>
                </span>
              </div>
            </td>
            <td>{{ $file->category->name ?? '-' }}</td>
            <td class="text-nowrap">{{ $file->human_size }}</td>
            <td class="text-nowrap text-fm-muted">{{ $file->created_at->locale('id')->isoFormat('D MMM Y') }}</td>
            <td class="text-right">
              <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary" title="Download">
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
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  (function () {
    var css = getComputedStyle(document.documentElement);
    function v(name) { return css.getPropertyValue(name).trim(); }

    var gridColor = 'rgba(148, 163, 184, .18)';
    var fontColor = '#64748b';

    // Doughnut: file per kategori
    var ctx1 = document.getElementById('chart-category');
    if (ctx1) {
      new Chart(ctx1, {
        type: 'doughnut',
        data: {
          labels: {{ Js::from(array_keys($byCategory)) }},
          datasets: [{
            data: {{ Js::from(array_values($byCategory)) }},
            backgroundColor: ['#0ea5e9', '#10b981', '#f59e0b', '#f43f5e'],
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: { legend: { position: 'bottom', labels: { color: fontColor, usePointStyle: true, padding: 16 } } }
        }
      });
    }

    // Bar: upload 7 hari terakhir
    var ctx2 = document.getElementById('chart-uploads');
    if (ctx2) {
      new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: {{ Js::from($labels7days) }},
          datasets: [{
            label: 'File diupload',
            data: {{ Js::from($uploads7days) }},
            backgroundColor: 'rgba(79, 70, 229, .75)',
            hoverBackgroundColor: '#4f46e5',
            borderRadius: 8,
            maxBarThickness: 42
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { display: false }, ticks: { color: fontColor } },
            y: { beginAtZero: true, ticks: { color: fontColor, precision: 0 }, grid: { color: gridColor } }
          }
        }
      });
    }
  })();
</script>
@endpush
