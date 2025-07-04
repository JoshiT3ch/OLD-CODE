<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request.";
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $error = "All fields are required.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                $error = "Error: " . htmlspecialchars($e->getMessage());
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        a {
            transition: color 0.3s ease;
        }
        body {
            background: url('images/finmark-background.png') no-repeat center center/cover, linear-gradient(to bottom right, #f3f4f6, #e5e7eb); /* Fallback gradient */
            position: relative;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Semi-transparent overlay for readability */
            z-index: -1;
        }
    </style>
</head>
<body class="font-sans flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md animate-fade-in">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Login</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <div>
                <label for="email" class="block text-gray-700 font-medium">Email</label>
                <input type="email" id="email" name="email" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <div>
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 btn">Login</button>
        </form>
        <p class="mt-4 text-center text-gray-600">Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-800">Register</a></p>
        <a href="index.php" class="mt-2 block text-center text-indigo-600 hover:text-indigo-800">Back to Home</a>
    </div>
</body>
</html>