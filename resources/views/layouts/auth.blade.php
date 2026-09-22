<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Masuk') — File Manager</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3">

</head>
<body class="fm-authbody">
<div class="fm-authwrap">

  <!-- Panel branding -->
  <div class="fm-authbrand">
    <div class="fm-authbrand-inner">
      <a href="{{ route('login') }}" class="fm-authlogo">
        <i class="fas fa-folder-open"></i> File<span>Manager</span>
      </a>
      <h2>Kelola semua file Anda<br>dalam satu tempat.</h2>
      <p>Upload, kategorikan, cari, dan download file dengan cepat dan aman.</p>

      <ul class="fm-authfeat">
        <li><i class="fas fa-circle-check"></i> Upload hingga 10 file sekaligus</li>
        <li><i class="fas fa-circle-check"></i> Kategori & filter pintar</li>
        <li><i class="fas fa-circle-check"></i> Trash & restore — file tak hilang permanen</li>
        <li><i class="fas fa-circle-check"></i> Download ZIP & export CSV</li>
      </ul>
    </div>
    <div class="fm-authbrand-glow"></div>
  </div>

  <!-- Panel form -->
  <div class="fm-authpanel">
    <div class="fm-authcard">
      @yield('auth-content')

      <p class="fm-authswitch">
        @yield('auth-switch')
      </p>
    </div>
  </div>

</div>
</body>
</html>
