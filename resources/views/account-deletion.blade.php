<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Request deletion of a Med Bangladesh account and associated personal data.">
    <title>Delete Account | Med Bangladesh</title>
    <style>
        :root {
            color-scheme: light;
            --background: #f4f8f6;
            --card: #ffffff;
            --heading: #12372f;
            --text: #34443f;
            --muted: #66756f;
            --accent: #16805b;
            --accent-soft: #e8f5ef;
            --danger: #b42318;
            --danger-soft: #fff1f0;
            --border: #dfe9e4;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.65;
            margin: 0;
        }

        .site-header {
            background: linear-gradient(135deg, #102f2a 0%, #16805b 100%);
            color: #fff;
            padding: 38px 20px 70px;
        }

        .header-inner,
        .content {
            margin: 0 auto;
            max-width: 780px;
        }

        .brand {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 22px;
            text-transform: uppercase;
        }

        h1 {
            font-size: clamp(30px, 6vw, 44px);
            line-height: 1.15;
            margin: 0 0 10px;
        }

        .subtitle {
            color: #d7f2e8;
            margin: 0;
        }

        .content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(16, 47, 42, .08);
            margin-bottom: 48px;
            margin-top: -36px;
            padding: clamp(24px, 6vw, 48px);
        }

        h2 {
            color: var(--heading);
            font-size: 22px;
            line-height: 1.3;
            margin: 34px 0 10px;
        }

        p { margin: 0 0 15px; }
        ol, ul { margin: 0 0 18px; padding-left: 24px; }
        li { margin: 7px 0; }

        a {
            color: var(--accent);
            font-weight: 700;
        }

        .notice,
        .errors {
            border-radius: 9px;
            margin-bottom: 22px;
            padding: 15px 17px;
        }

        .notice {
            background: var(--accent-soft);
            border: 1px solid #b8dccd;
            color: var(--heading);
        }

        .errors {
            background: var(--danger-soft);
            border: 1px solid #f4b4ad;
            color: #8a1f1f;
        }

        .form-card {
            border-top: 1px solid var(--border);
            margin-top: 30px;
            padding-top: 28px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 18px;
        }

        label {
            color: var(--heading);
            font-size: 14px;
            font-weight: 800;
        }

        input[type="text"],
        input[type="password"] {
            border: 1px solid #bccdc5;
            border-radius: 8px;
            font: inherit;
            min-height: 46px;
            padding: 10px 12px;
            width: 100%;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(22, 128, 91, .12);
            outline: 0;
        }

        .confirmation {
            align-items: flex-start;
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .confirmation input {
            height: 18px;
            margin-top: 4px;
            width: 18px;
        }

        .delete-button {
            background: var(--danger);
            border: 0;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 800;
            min-height: 46px;
            padding: 11px 18px;
            width: 100%;
        }

        .delete-button:hover { background: #912018; }

        .fine-print {
            color: var(--muted);
            font-size: 13px;
            margin-top: 18px;
        }

        footer {
            color: var(--muted);
            font-size: 13px;
            padding: 0 20px 34px;
            text-align: center;
        }

        @media (max-width: 640px) {
            .content {
                border-left: 0;
                border-radius: 12px;
                border-right: 0;
                margin-left: 12px;
                margin-right: 12px;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <div class="brand">Med Bangladesh</div>
            <h1>Delete your account</h1>
            <p class="subtitle">Request deletion of your account and associated personal data.</p>
        </div>
    </header>

    <main class="content">
        @if (session('deletion_status'))
            <div class="notice" role="status">{{ session('deletion_status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>We could not complete the request.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p>
            This page is for users of the <strong>Med Bangladesh</strong> mobile application.
            You can permanently delete your account without reinstalling or opening the app.
        </p>

        <h2>How to request deletion</h2>
        <ol>
            <li>Enter the phone number or email address used for your account.</li>
            <li>Enter your account password so we can verify that the account belongs to you.</li>
            <li>Confirm the request and select <strong>Delete account and data</strong>.</li>
        </ol>

        <h2>Data that will be deleted</h2>
        <ul>
            <li>Your account name, phone number, optional email address, password, and access token.</li>
            <li>Personal delivery details stored with orders, including name, phone number, address, and notes.</li>
            <li>The connection between your customer account and previous orders.</li>
        </ul>

        <h2>Data that may be kept</h2>
        <p>
            We keep anonymized transaction records such as order number, ordered products,
            quantities, prices, status, and transaction dates for business, security, dispute,
            and legal recordkeeping. These records no longer contain your account identifiers,
            delivery address, phone number, or notes and may be retained indefinitely in
            anonymized form.
        </p>

        <section class="form-card" aria-labelledby="deletion-form-title">
            <h2 id="deletion-form-title">Verify and delete your account</h2>

            <form method="POST" action="{{ route('account-deletion.destroy') }}">
                @csrf

                <div class="field">
                    <label for="login">Account phone number or email</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <label class="confirmation">
                    <input name="confirm_deletion" type="checkbox" value="1" required>
                    <span>
                        I understand that this permanently deletes my Med Bangladesh account and
                        that the account cannot be recovered.
                    </span>
                </label>

                <button class="delete-button" type="submit">Delete account and data</button>
            </form>

            <p class="fine-print">
                If you cannot access your account, contact
                <a href="mailto:{{ config('app.privacy_email') }}">{{ config('app.privacy_email') }}</a>.
                Review our <a href="{{ route('privacy-policy') }}">Privacy Policy</a> for more
                information.
            </p>
        </section>
    </main>

    <footer>
        &copy; {{ date('Y') }} Med Bangladesh. All rights reserved.
    </footer>
</body>
</html>
