<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <!-- Google Font -->
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            background: aliceblue;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            background-color: #f8d7da;
            color: #721c24;
            font-size: 14px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="email"],
        input[type="password"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        button {
            background-color: black;
            color: azure;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #333;
        }

        .text-danger {
            font-size: 14px;
            color: #660000;
        }

        .link-primary {
            color: black;
            text-decoration: none;
            text-align: center;
            display: block;
            margin-top: 10px;
        }

        .link-primary:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Login</h2>

        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <!-- Display error message if any -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <!-- Display validation error for email if any -->
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            <input type="email" name="email" placeholder="Email">


            <!-- Display validation error for password if any -->
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
            <input type="password" name="password" placeholder="Password">


            <button type="submit">Login</button>
        </form>
        <div class="mt-3">
            <a href="{{ route('forget.password.get') }}"
                class="link-primary text-decoration-none">{{ __('forgot password?') }}</a>
        </div>
        <div class="mt-3">
            <a href="{{ route('register') }}" class="link-primary text-decoration-none">{{ __('Register') }}</a>
        </div>
    </div>

</body>

</html>
