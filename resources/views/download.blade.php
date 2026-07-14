<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Download Med Bangladesh for Android and order medicines from your phone.">
    <title>Med Bangladesh | Medicine ordering made simple</title>
    <style>
        :root { --green: #087a55; --dark: #113c32; --mint: #e9f8f1; --text: #415b53; --line: #dceae4; }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; color: var(--dark); background: #fff; font-family: Arial, sans-serif; }
        a { color: inherit; }
        .container { width: min(1120px, calc(100% - 40px)); margin: auto; }
        header { border-bottom: 1px solid var(--line); background: rgba(255,255,255,.96); }
        nav { min-height: 72px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand { font-size: 21px; font-weight: 900; text-decoration: none; }
        .nav-links { display: flex; align-items: center; gap: 22px; font-size: 14px; font-weight: 700; }
        .nav-links a { text-decoration: none; }
        .admin-link { color: var(--green); }
        .hero { background: linear-gradient(135deg, #f0fbf6 0%, #fff 65%); padding: 84px 0; }
        .hero-grid { display: grid; grid-template-columns: 1.2fr .8fr; align-items: center; gap: 64px; }
        .eyebrow { color: var(--green); font-size: 13px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        h1 { max-width: 720px; margin: 14px 0 18px; font-size: clamp(40px, 6vw, 68px); line-height: 1.04; letter-spacing: -.04em; }
        .lead { max-width: 620px; color: var(--text); font-size: 18px; line-height: 1.7; }
        .btn { display: inline-flex; align-items: center; justify-content: center; margin-top: 22px; border-radius: 10px; padding: 15px 23px; color: #fff; background: var(--green); box-shadow: 0 12px 24px rgba(8,122,85,.2); font-weight: 900; text-decoration: none; }
        .download-note { margin: 12px 0 0; color: #71877f; font-size: 12px; }
        .phone { min-height: 390px; display: grid; place-items: center; border-radius: 38px; color: #fff; background: linear-gradient(155deg, var(--dark), var(--green)); box-shadow: 0 30px 60px rgba(17,60,50,.22); padding: 38px; text-align: center; }
        .phone-mark { display: grid; place-items: center; width: 92px; height: 92px; margin: auto auto 24px; border-radius: 25px; background: #fff; color: var(--green); font-size: 38px; font-weight: 900; }
        section.info { padding: 76px 0; }
        .section-head { max-width: 650px; margin: 0 auto 38px; text-align: center; }
        h2 { margin: 0 0 12px; font-size: 34px; }
        .section-head p, .feature p, .steps p { color: var(--text); line-height: 1.65; }
        .features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feature { border: 1px solid var(--line); border-radius: 16px; padding: 26px; background: #fff; }
        .feature span { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 12px; background: var(--mint); color: var(--green); font-weight: 900; }
        .feature h3 { margin: 18px 0 8px; }
        .steps-wrap { background: #f6faf8; }
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 34px; counter-reset: step; }
        .step { counter-increment: step; }
        .step::before { content: counter(step); display: grid; place-items: center; width: 36px; height: 36px; border-radius: 50%; background: var(--green); color: #fff; font-weight: 900; }
        .step h3 { margin: 14px 0 6px; }
        .cta { margin: 74px auto; border-radius: 24px; padding: 48px; color: #fff; background: var(--dark); text-align: center; }
        .cta h2 { font-size: 36px; }
        .cta p { color: #c7ded5; }
        footer { border-top: 1px solid var(--line); padding: 26px 0; color: #677d75; font-size: 13px; }
        .footer-row { display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 18px; }
        @media (max-width: 760px) {
            .nav-links a:not(.admin-link) { display: none; }
            .hero { padding: 54px 0; }
            .hero-grid, .features, .steps { grid-template-columns: 1fr; }
            .phone { min-height: 300px; }
            .cta { padding: 36px 22px; }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <a class="brand" href="{{ route('download') }}">Med Bangladesh</a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How it works</a>
                <a class="admin-link" href="{{ route('login') }}">Admin login</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="eyebrow">Healthcare at your fingertips</div>
                    <h1>Your medicine needs, made simpler.</h1>
                    <p class="lead">Explore available medicines, place an order from home, and keep track of every order with the Med Bangladesh Android app.</p>
                    <a class="btn" href="{{ config('services.apk_download_url') }}" download>Download for Android</a>
                    <p class="download-note">APK download · Android may ask you to allow installation from your browser.</p>
                </div>
                <div class="phone">
                    <div>
                        <div class="phone-mark">M+</div>
                        <h2>Med Bangladesh</h2>
                        <p>Medicine ordering from your phone.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="info" id="features">
            <div class="container">
                <div class="section-head"><h2>Everything you need in one app</h2><p>A clear and convenient experience designed to make medicine ordering easier.</p></div>
                <div class="features">
                    <article class="feature"><span>01</span><h3>Find medicines</h3><p>Browse available products and view prices, strengths, forms, and current availability.</p></article>
                    <article class="feature"><span>02</span><h3>Order conveniently</h3><p>Add the items you need, provide your delivery information, and submit your order in minutes.</p></article>
                    <article class="feature"><span>03</span><h3>Track orders</h3><p>Review your previous orders and follow the latest status from your account.</p></article>
                </div>
            </div>
        </section>

        <section class="info steps-wrap" id="how-it-works">
            <div class="container">
                <div class="section-head"><h2>How it works</h2><p>Start ordering with three simple steps.</p></div>
                <div class="steps">
                    <div class="step"><h3>Download the app</h3><p>Install the Med Bangladesh APK on your Android phone.</p></div>
                    <div class="step"><h3>Create your account</h3><p>Register securely using your contact and delivery information.</p></div>
                    <div class="step"><h3>Place your order</h3><p>Select medicines, confirm quantities, and send your order.</p></div>
                </div>
            </div>
        </section>

        <section class="container cta">
            <h2>Get Med Bangladesh today</h2>
            <p>Download the Android app and place your medicine order from anywhere.</p>
            <a class="btn" href="{{ config('services.apk_download_url') }}" download>Download Android APK</a>
        </section>
    </main>

    <footer>
        <div class="container footer-row">
            <span>&copy; {{ date('Y') }} Med Bangladesh. All rights reserved.</span>
            <div class="footer-links"><a href="{{ route('privacy-policy') }}">Privacy Policy</a><a href="{{ route('account-deletion') }}">Account Deletion</a></div>
        </div>
    </footer>
</body>
</html>
