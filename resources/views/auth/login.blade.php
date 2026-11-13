@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <section class="card border-0 shadow-sm login-panel">
                <div class="card-body p-4 p-md-5">
                    <div class="brand-mark login-mark mb-4" aria-hidden="true">PS</div>
                    <p class="text-uppercase small fw-bold text-success mb-2">Persib Bandung · Akademik</p>
                    <h1 class="h3 mb-1">Masuk ke SIKAD</h1>
                    <p class="text-secondary mb-4">Gunakan ID akun yang diberikan administrator.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="identifier" class="form-label">ID pengguna</label>
                            <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" class="form-control @error('identifier') is-invalid @enderror" autocomplete="username" required autofocus>
                            @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata sandi</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-4">
                            <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input">
                            <label for="remember" class="form-check-label">Ingat saya</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection