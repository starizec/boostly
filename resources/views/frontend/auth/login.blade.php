@extends('layout.master2')

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
    <div class="card">
      <div class="row">
        <div class="col-md-4 pe-md-0">
          <div class="auth-side-wrapper" style="background-image: url({{ url('https://placehold.co/220x450') }})">

          </div>
        </div>
        <div class="col-md-8 ps-md-0">
          <div class="auth-form-wrapper px-4 py-5">
            <a href="#" class="nobleui-logo d-block mb-2">MeetBoostly.com</span></a>
            <h5 class="text-secondary fw-normal mb-4">Prijavi se</h5>

            @if (session('status'))
              <div class="alert alert-success" role="alert">
                {{ session('status') }}
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="forms-sample">
              @csrf

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Email" 
                       required 
                       autofocus>
                @error('email')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Lozinka</label>
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       autocomplete="current-password" 
                       placeholder="Lozinka" 
                       required>
                @error('password')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
              </div>

              <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                Zapamti me
                </label>
              </div>

              <div>
                <button type="submit" class="btn btn-primary me-2 mb-2 mb-md-0">Prijavi se</button>
                
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection