<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: Arial, sans-serif; background: #f4f7f6; color: #173d31; padding: 20px; }
        .login { width: min(420px, 100%); background: #fff; border: 1px solid #dce7e2; border-radius: 8px; padding: 24px; }
        h1 { margin: 0 0 6px; font-size: 25px; }
        p { margin: 0 0 18px; color: #61756d; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        label { font-size: 13px; font-weight: 700; color: #31463e; }
        input { height: 42px; border: 1px solid #cddbd5; border-radius: 6px; padding: 0 12px; font-size: 14px; width: 100%; }
        .row { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        .row input { width: auto; height: auto; }
        .btn { border: 0; border-radius: 6px; padding: 11px 14px; font-weight: 700; cursor: pointer; background: #1a7f5a; color: #fff; width: 100%; }
        .errors { background: #fff0f0; border: 1px solid #f0b8b8; color: #8a1f1f; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <form class="login" method="POST" action="{{ route('login.store') }}">
        @csrf
        <h1>Admin Login</h1>
        <p>Access the pharmacy dashboard.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="field">
            <label for="gmail">Gmail</label>
            <input id="gmail" name="gmail" type="email" value="{{ old('gmail') }}" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <label class="row">
            <input name="remember" type="checkbox" value="1">
            Remember me
        </label>
        <button class="btn" type="submit">Login</button>
    </form>
</body>
</html>
