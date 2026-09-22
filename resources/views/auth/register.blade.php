@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('auth-content')
  <h1 class="fm-authtitle">Buat akun baru ✨</h1>
  <p class="fm-authsub">Gratis — mulai kelola file Anda dalam hitungan detik.</p>

  @if ($errors->any())
    <div class="fm-alert-danger">
      <i class="fas fa-triangle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('register.store') }}">
    @csrf
    <div class="fm-field">
      <label for="name">Nama Lengkap</label>
      <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Nama Anda">
    </div>
    <div class="fm-field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@contoh.com">
    </div>
    <div class="fm-field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter">
    </div>
    <div class="fm-field">
      <label for="password_confirmation">Konfirmasi Password</label>
      <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi password">
    </div>
    <button type="submit" class="fm-btn fm-btn-primary fm-btn-block">
      <i class="fas fa-user-plus mr-1"></i> Buat Akun
    </button>
  </form>
@endsection

@section('auth-switch')
  Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
@endsection
