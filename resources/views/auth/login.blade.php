<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk &middot; Klinik Sederhana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    <style>
        body.login-bg{min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:radial-gradient(1200px 600px at 50% -10%,#6366f1 0%,#4f46e5 45%,#312e81 100%);
            padding:1.5rem;font-family:"Inter",system-ui,sans-serif;}
        .login-card{width:100%;max-width:420px;background:#fff;border-radius:18px;
            box-shadow:0 24px 60px rgba(15,23,42,.35);overflow:hidden;animation:loginIn .35s ease both;}
        @keyframes loginIn{from{opacity:0;transform:translateY(12px) scale(.98);}to{opacity:1;transform:none;}}
        .login-top{padding:2rem 2rem 1.25rem;text-align:center;border-bottom:1px solid #f1f5f9;}
        .login-logo{width:52px;height:52px;border-radius:14px;background:#eef2ff;color:#4f46e5;
            display:inline-flex;align-items:center;justify-content:center;margin-bottom:.85rem;}
        .login-logo svg{width:28px;height:28px;}
        .login-top h1{font-size:1.3rem;font-weight:800;color:#0f172a;margin:0 0 .2rem;letter-spacing:-.02em;}
        .login-top p{font-size:.88rem;color:#64748b;margin:0;}
        .login-body{padding:1.5rem 2rem 2rem;}
        .login-alert{border-radius:10px;padding:.7rem .9rem;font-size:.85rem;margin-bottom:1rem;
            border:1px solid #fecaca;background:#fef2f2;color:#b42318;}
        .login-alert ul{margin:.2rem 0 0;padding-left:1.1rem;}
        .login-field{margin-bottom:1rem;}
        .login-field label{display:block;font-weight:600;font-size:.82rem;color:#334155;margin-bottom:.35rem;}
        .login-input{width:100%;padding:.65rem .8rem;border:1px solid #dfe3ec;border-radius:10px;
            font-size:.95rem;color:#1e293b;transition:border-color .15s,box-shadow .15s;background:#fff;}
        .login-input:focus{outline:none;border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.14);}
        .login-input.is-invalid{border-color:#dc2626;background:#fff5f5;}
        .login-err{font-size:.76rem;color:#dc2626;margin-top:.25rem;}
        .login-row{display:flex;align-items:center;justify-content:space-between;margin:.25rem 0 1.1rem;}
        .login-remember{display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#475569;cursor:pointer;}
        .login-remember input{width:16px;height:16px;accent-color:#4f46e5;}
        .login-forgot{font-size:.82rem;color:#4f46e5;text-decoration:none;font-weight:600;}
        .login-forgot:hover{text-decoration:underline;}
        .login-submit{width:100%;padding:.7rem;border:none;border-radius:10px;background:#4f46e5;color:#fff;
            font-weight:700;font-size:.95rem;cursor:pointer;transition:all .15s;
            box-shadow:0 4px 14px rgba(79,70,229,.3);display:flex;align-items:center;justify-content:center;gap:.45rem;}
        .login-submit:hover{background:#4338ca;transform:translateY(-1px);box-shadow:0 6px 18px rgba(79,70,229,.4);}
        .login-submit svg{width:18px;height:18px;}
        .login-foot{text-align:center;font-size:.74rem;color:#94a3b8;margin-top:1.25rem;}
    </style>
</head>
<body class="login-bg">
    <div class="login-card">
        <div class="login-top">
            <div class="login-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <h1>Klinik Sederhana</h1>
            <p>Masuk ke panel kendali Anda</p>
        </div>
        <div class="login-body">
            @if(session('error'))
                <div class="login-alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="login-alert">
                    <ul class="m-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ url('login') }}">
                @csrf
                <div class="login-field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="login-input @error('email') is-invalid @enderror" placeholder="anda@klinik.test">
                    @error('email')<div class="login-err">{{ $message }}</div>@enderror
                </div>
                <div class="login-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required
                           class="login-input @error('password') is-invalid @enderror" placeholder="••••••••">
                    @error('password')<div class="login-err">{{ $message }}</div>@enderror
                </div>
                <div class="login-row">
                    <label class="login-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label>
                    <a class="login-forgot" href="#">Lupa password?</a>
                </div>
                <button type="submit" class="login-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Masuk
                </button>
            </form>
            <div class="login-foot">&copy; {{ date('Y') }} Klinik Sederhana &middot; Sistem Informasi Klinik</div>
        </div>
    </div>
</body>
</html>