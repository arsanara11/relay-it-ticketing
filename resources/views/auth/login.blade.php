<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Relay</title>
    <style>
        body {
            margin: 0;
            background: #222222;
            color: #f5f5f5;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        .login {
            width: min(360px, calc(100% - 48px));
            background: #343434;
            border-radius: 24px;
            padding: 32px;
        }

        h1 { margin-top: 0; }

        .accent { color: #c9f34d; }

        label {
            display: block;
            margin: 18px 0 8px;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 13px;
            border: 1px solid #52646b;
            border-radius: 10px;
            background: #222;
            color: white;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 14px;
            border: 0;
            border-radius: 10px;
            background: #c9f34d;
            color: #222;
            font-weight: bold;
            cursor: pointer;
        }

        .error { color: #ff9b9b; }
    </style>
</head>
<body>
    <main class="login">
        <h1>relay<span class="accent">.</span></h1>
        <p>Sign in to your workspace.</p>

        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Sign In</button>
        </form>
    </main>
</body>
</html>
