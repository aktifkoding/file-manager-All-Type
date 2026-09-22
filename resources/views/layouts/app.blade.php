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
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3">

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
        <a href="{{ route('dashboard') }}" class="nav-link font-weight-bold">File Manager</a>
      </li>
    </ul>
    <!-- Right navbar -->
    <ul class="navbar-nav ml-auto align-items-center">
      <li class="nav-item mr-2">
        <button type="button" id="dark-toggle" class="btn btn-sm btn-secondary" title="Mode gelap/terang">
          <i class="fas fa-moon"></i>
        </button>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
          <span class="fm-avatar">{{ substr(auth()->user()->name, 0, 1) }}</span>
          <span class="d-none d-md-inline ml-2 font-weight-semibold" style="font-size:.875rem;">{{ auth()->user()->name }}</span>
          <i class="fas fa-chevron-down ml-2 text-fm-muted" style="font-size:.7rem;"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right fm-dropdown">
          <a class="dropdown-item" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-pie mr-2 text-fm-muted"></i> Dashboard
          </a>
          <a class="dropdown-item" href="{{ route('files.trash') }}">
            <i class="fas fa-trash-can mr-2 text-fm-muted"></i> Trash
          </a>
          <div class="dropdown-divider"></div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item text-danger" style="cursor:pointer;">
              <i class="fas fa-right-from-bracket mr-2"></i> Keluar
            </button>
          </form>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-0 fm-sidebar">
    <a href="{{ route('dashboard') }}" class="brand-link">
      <span class="brand-image-lite"><i class="fas fa-folder-open fa-lg" style="color:#818cf8;"></i></span>
      <span class="brand-text font-weight-bold">File<span style="color:#818cf8;">Manager</span></span>
    </a>

    <div class="sidebar">
      <nav class="mt-3">
        <p class="text-uppercase text-fm-muted px-3 mb-2 fm-side-label">Menu</p>
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('files.index') }}" class="nav-link {{ request()->routeIs('files.index') || request()->routeIs('files.show') ? 'active' : '' }}">
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
          <li class="nav-item">
            <a href="{{ route('files.trash') }}" class="nav-link {{ request()->routeIs('files.trash') ? 'active' : '' }}">
              <i class="nav-icon fas fa-trash-can"></i>
              <p>Trash</p>
            </a>
          </li>
        </ul>

        <p class="text-uppercase text-fm-muted px-3 mt-4 mb-2 fm-side-label">Ekspor</p>
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
          <li class="nav-item">
            <a href="{{ route('files.export') }}" class="nav-link">
              <i class="nav-icon fas fa-file-csv"></i>
              <p>Export CSV</p>
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
    <div class="float-right d-none d-sm-inline text-fm-muted">v3.0</div>
  </footer>

</div>
<!-- ./wrapper -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
  // Toast: auto-dismiss + tombol tutup
  (function () {
    document.querySelectorAll('[data-fm-toast]').forEach(function (t) {
      setTimeout(function () {
        t.style.transition = 'opacity .4s ease, transform .4s ease';
        t.style.opacity = '0';
        t.style.transform = 'translateX(24px)';
        setTimeout(function () { t.remove(); }, 400);
      }, 4000);
      var btn = t.querySelector('[data-dismiss-fm-toast]');
      if (btn) btn.addEventListener('click', function () { t.remove(); });
    });
  })();

  // Dark mode: toggle + persist di localStorage
  (function () {
    var body = document.body;
    var toggle = document.getElementById('dark-toggle');
    var icon = toggle ? toggle.querySelector('i') : null;

    function apply(dark) {
      body.classList.toggle('dark-mode', dark);
      if (icon) { icon.className = dark ? 'fas fa-sun' : 'fas fa-moon'; }
      try { localStorage.setItem('fm-dark', dark ? '1' : '0'); } catch (e) {}
    }

    try { apply(localStorage.getItem('fm-dark') === '1'); } catch (e) {}

    if (toggle) {
      toggle.addEventListener('click', function () {
        apply(!body.classList.contains('dark-mode'));
      });
    }
  })();
</script>
@stack('scripts')

</body>
</html>
