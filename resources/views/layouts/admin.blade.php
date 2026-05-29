<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f7f6; color: #173d31; }
        a { color: inherit; text-decoration: none; }
        .nav { background: #1a7f5a; color: #fff; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between; }
        .brand { font-size: 21px; font-weight: 800; }
        .nav-actions { display: flex; gap: 10px; }
        .btn { border: 0; border-radius: 6px; padding: 10px 14px; font-weight: 700; cursor: pointer; display: inline-block; }
        .btn-light { background: #fff; color: #1a7f5a; }
        .btn-primary { background: #1a7f5a; color: #fff; }
        .page { max-width: 1180px; margin: 0 auto; padding: 24px; }
        .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .title { margin: 0; font-size: 25px; }
        .subtitle { margin: 6px 0 0; color: #61756d; }
        .notice { background: #e8f5ef; border: 1px solid #b8dccd; color: #14543e; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 14px; }
        .card { background: #fff; border: 1px solid #dce7e2; border-radius: 8px; overflow: hidden; }
        .image-wrap { height: 150px; background: #eef4f1; position: relative; }
        .image-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .discount { position: absolute; top: 12px; left: 0; background: #e43d30; color: #fff; font-size: 12px; font-weight: 800; padding: 7px 10px; border-radius: 0 5px 5px 0; }
        .card-body { padding: 14px; }
        .product-name { font-size: 17px; font-weight: 800; margin: 0; }
        .muted { color: #657870; font-size: 13px; }
        .meta { display: flex; justify-content: space-between; gap: 12px; margin-top: 12px; }
        .price { color: #1a7f5a; font-weight: 800; }
        .form { background: #fff; border: 1px solid #dce7e2; border-radius: 8px; padding: 20px; max-width: 760px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field-full { grid-column: 1 / -1; }
        label { font-size: 13px; font-weight: 700; color: #31463e; }
        input { height: 42px; border: 1px solid #cddbd5; border-radius: 6px; padding: 0 12px; font-size: 14px; }
        .errors { background: #fff0f0; border: 1px solid #f0b8b8; color: #8a1f1f; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; }
        @media (max-width: 680px) {
            .nav, .toolbar { align-items: flex-start; flex-direction: column; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="brand">Med Bangladesh Admin</div>
        <nav class="nav-actions">
            <a class="btn btn-light" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="btn btn-light" href="{{ route('products.create') }}">Create Product</a>
        </nav>
    </header>
    <main class="page">
        @yield('content')
    </main>
</body>
</html>
