@extends('layouts.auth')

@section('title', 'Masuk')

@section('auth-content')
  <h1 class="fm-authtitle">Selamat datang kembali 👋</h1>
  <p class="fm-authsub">Masuk untuk mengelola file Anda.</p>

  @if ($errors->any())
    <div class="fm-alert-danger">
      <i class="fas fa-triangle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('login.attempt') }}">
    @csrf
    <div class="fm-field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@contoh.com">
    </div>
    <div class="fm-field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" required placeholder="••••••••">
    </div>
    <div class="fm-field fm-field-row">
      <label class="fm-check">
        <input type="checkbox" name="remember" value="1"> Ingat saya
      </label>
    </div>
    <button type="submit" class="fm-btn fm-btn-primary fm-btn-block">
      <i class="fas fa-right-to-bracket mr-1"></i> Masuk
    </button>
  </form>
@endsection

@section('auth-switch')
  Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
@endsection
