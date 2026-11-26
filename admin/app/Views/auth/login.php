<?php

use App\Core\Csrf;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Login | Nature Holidays</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            background: #f3f5ef;
            color: #24352b;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 36px;
            background: #fff;
            border: 1px solid #e2e8df;
            border-radius: 18px;
        }

        h1 { margin: 0 0 8px; font-size: 27px; }
        p { color: #68756c; line-height: 1.6; }

        label {
            display: block;
            margin: 20px 0 8px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce3dc;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            background: #244b35;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .error {
            padding: 12px;
            border-radius: 8px;
            background: #fff0ee;
            color: #a12c20;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <main class="login-card">
        <h1>Nature Holidays</h1>
        <p>Staff administration portal</p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_BASE_PATH ?>/login">
            <input
                type="hidden"
                name="_csrf"
                value="<?= htmlspecialchars(Csrf::token()) ?>"
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Sign in</button>
            
        </form>
    </main>
</body>
</html>