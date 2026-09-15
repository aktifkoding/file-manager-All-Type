<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'File Manager')</title>

  <!-- Google Font: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- Design System kustom -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">

</head>
<body class="hold-transition sidebar-mini layout-fixed fm-body">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light fm-navbar">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('files.index') }}" class="nav-link font-weight-bold">File Manager</a>
      </li>
    </ul>
    <!-- Right navbar -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('files.create') }}" class="btn btn-primary btn-sm mr-2">
          <i class="fas fa-cloud-upload-alt mr-1"></i> Upload
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-0 fm-sidebar">
    <a href="{{ route('files.index') }}" class="brand-link">
      <span class="brand-image-lite"><i class="fas fa-folder-open fa-lg" style="color:#818cf8;"></i></span>
      <span class="brand-text font-weight-bold">File<span style="color:#818cf8;">Manager</span></span>
    </a>

    <div class="sidebar">
      <nav class="mt-3">
        <p class="text-uppercase text-xs px-3 text-muted mb-2" style="font-size:.68rem; letter-spacing:.08em;">Menu</p>
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          <li class="nav-item">
            <a href="{{ route('files.index') }}" class="nav-link {{ request()->routeIs('files.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-file-alt"></i>
              <p>Daftar File</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('files.create') }}" class="nav-link {{ request()->routeIs('files.create') ? 'active' : '' }}">
              <i class="nav-icon fas fa-cloud-upload-alt"></i>
              <p>Upload File</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
  <!-- /.sidebar -->

  <!-- Toast notifications -->
  <div class="fm-alerts" aria-live="polite">
    @if (session('success'))
      <div class="fm-toast fm-toast-success" data-fm-toast>
        <i class="fas fa-circle-check"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="close text-white ml-auto" data-dismiss-fm-toast aria-label="Tutup">&times;</button>
      </div>
    @endif
    @if (session('error'))
      <div class="fm-toast fm-toast-danger" data-fm-toast>
        <i class="fas fa-circle-xmark"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="close text-white ml-auto" data-dismiss-fm-toast aria-label="Tutup">&times;</button>
      </div>
    @endif
    @if ($errors->any())
      <div class="fm-toast fm-toast-danger" data-fm-toast>
        <i class="fas fa-triangle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
        <button type="button" class="close text-white ml-auto" data-dismiss-fm-toast aria-label="Tutup">&times;</button>
      </div>
    @endif
  </div>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content fm-content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer fm-footer">
    <strong>&copy; {{ date('Y') }} Aktif Koding.</strong>
    <span class="text-fm-muted d-none d-sm-inline">— Dibangun dengan Laravel & AdminLTE.</span>
    <div class="float-right d-none d-sm-inline text-fm-muted">v2.0</div>
  </footer>

</div>
<!-- ./wrapper -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
  // Auto-dismiss toast setelah 4 detik + tombol tutup
  (function () {
    var toasts = document.querySelectorAll('[data-fm-toast]');
    toasts.forEach(function (t) {
      setTimeout(function () {
        t.style.transition = 'opacity .4s ease, transform .4s ease';
        t.style.opacity = '0';
        t.style.transform = 'translateX(24px)';
        setTimeout(function () { t.remove(); }, 400);
      }, 4000);
      var btn = t.querySelector('[data-dismiss-fm-toast]');
      if (btn) {
        btn.addEventListener('click', function () { t.remove(); });
      }
    });
  })();
</script>
@stack('scripts')

</body>
</html>
