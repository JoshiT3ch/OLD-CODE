<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request.";
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$username || !$email || !$password || !$confirm_password) {
            $error = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $error = "Password must be 8+ characters, with an uppercase letter and a number.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$email, $username]);
                if ($stmt->fetchColumn()) {
                    $error = "Email or username already exists.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password]);
                    $success = "Registration successful! <a href='login.php' class='text-indigo-600 hover:underline'>Login now</a>.";
                }
            } catch (PDOException $e) {
                $error = "Error: " . htmlspecialchars($e->getMessage());
                error_log("Registration error: " . $e->getMessage());
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
    <title>Register - FinMark</title>
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
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/finmark-background.png') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body class="font-sans flex items-center justify-center min-h-screen">
    <div class="bg-white bg-opacity-90 p-8 rounded-2xl shadow-lg w-full max-w-md animate-fade-in">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Register</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <div>
                <label for="username" class="block text-gray-700 font-medium">Username</label>
                <input type="text" id="username" name="username" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <div>
                <label for="email" class="block text-gray-700 font-medium">Email</label>
                <input type="email" id="email" name="email" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <div>
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <div>
                <label for="confirm_password" class="block text-gray-700 font-medium">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 btn">Register</button>
        </form>
        <p class="mt-4 text-center text-gray-600">Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-800">Login</a></p>
        <a href="index.php" class="mt-2 block text-center text-indigo-600 hover:text-indigo-800">Back to Home</a>
    </div>
</body>
</html>