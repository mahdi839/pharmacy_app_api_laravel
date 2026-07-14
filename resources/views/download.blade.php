<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Med Bangladesh</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: Arial, sans-serif; color: #173d31; background: linear-gradient(135deg, #effaf5, #f7fbff); }
        .card { width: min(520px, 100%); text-align: center; background: #fff; border: 1px solid #dce7e2; border-radius: 18px; padding: 42px 28px; box-shadow: 0 20px 55px rgba(15, 61, 49, .12); }
        h1 { margin: 0 0 10px; font-size: 32px; }
        p { color: #61756d; line-height: 1.6; margin: 0 auto 24px; max-width: 410px; }
        .btn { display: inline-block; border-radius: 8px; padding: 14px 22px; background: #1a7f5a; color: #fff; font-weight: 800; text-decoration: none; }
        .admin { display: inline-block; margin-top: 18px; color: #1a7f5a; font-size: 14px; font-weight: 700; text-decoration: none; }
        .note { margin-top: 20px; font-size: 12px; color: #8a9892; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Med Bangladesh</h1>
        <p>Order medicines and manage your pharmacy from the Med Bangladesh Android app.</p>
        <a class="btn" href="{{ env('APK_DOWNLOAD_URL', '/downloads/med-bangladesh.apk') }}" download>Download Android App</a>
        <a class="admin" href="{{ route('login') }}">Admin login</a>
        <div class="note">Android APK download</div>
    </main>
</body>
</html>
