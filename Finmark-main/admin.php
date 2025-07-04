<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                $error = "Current password is incorrect.";
            } elseif ($new_password && ($new_password !== $confirm_password || strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password))) {
                $error = "New password must match, be 8+ characters, and include an uppercase letter and a number.";
            } else {
                $update_data = [];
                $params = [$_SESSION['user_id']];
                if ($username) {
                    $update_data[] = "username = ?";
                    $params[] = $username;
                }
                if ($email) {
                    $update_data[] = "email = ?";
                    $params[] = $email;
                }
                if ($new_password) {
                    $update_data[] = "password = ?";
                    $params[] = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                }
                if (!empty($update_data)) {
                    $sql = "UPDATE users SET " . implode(", ", $update_data) . ", updated_at = NOW() WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $success = "Profile updated successfully.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error: " . htmlspecialchars($e->getMessage());
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at, estimated_delivery FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
    error_log("Fetch user/orders error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        .animate-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }
        .animate-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .table-row {
            transition: background-color 0.3s ease;
        }
        .table-row:hover {
            background-color: #f1f5f9;
        }
        nav a {
            transition: color 0.3s ease, transform 0.3s ease;
        }
        nav a:hover {
            transform: scale(1.1);
        }
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .btn:active {
            animation: pulse 0.2s ease-in-out;
        }
        input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .payroll-image {
            transition: transform 0.3s ease;
        }
        .payroll-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 font-sans">
    <header class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white sticky top-0 z-50 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-6">
                <span class="text-gray-200 font-medium">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-200">Services</a>
                <a href="cart.php" class="hover:text-indigo-200">Cart</a>
                <a href="orders.php" class="hover:text-indigo-200">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-200">Feedback</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-200">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-200">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <section id="profile" class="animate-scroll">
            <h2 class="text-4xl font-extrabold text-gray-800 mb-8">Profile</h2>
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6 animate-scroll"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 animate-scroll"><?php echo $success; ?></div>
            <?php endif; ?>
            <div class="max-w-md mx-auto bg-white p-6 rounded-2xl shadow-lg animate-scroll">
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16 payroll-image" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="12" width="48" height="40" rx="8" fill="#E0E7FF"/>
                        <path d="M32 18V46M20 32H44" stroke="#4F46E5" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="20" cy="24" r="3" fill="#4F46E5"/>
                        <circle cx="44" cy="24" r="3" fill="#4F46E5"/>
                        <circle cx="20" cy="40" r="3" fill="#4F46E5"/>
                        <circle cx="44" cy="40" r="3" fill="#4F46E5"/>
                    </svg>
                </div>
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div>
                        <label class="block text-gray-700 font-medium">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Current Password</label>
                        <input type="password" name="password" class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">New Password</label>
                        <input type="password" name="new_password" class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="w-full p-3 border border-gray-300 rounded-2xl focus:ring focus:border-indigo-600">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 btn">Update Profile</button>
                </form>
            </div>
        </section>
        <section id="orders" class="mt-8 animate-scroll">
            <div class="bg-white p-6 rounded-2xl shadow-lg">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Your Orders</h3>
                <?php if (empty($orders)): ?>
                    <p class="text-gray-600">No orders found. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
                <?php else: ?>
                    <table class="w-full">
                        <thead class="bg-indigo-50">
                            <tr>
                                <th class="p-4 text-left text-gray-700 rounded-tl-2xl">Order ID</th>
                                <th class="p-4 text-left text-gray-700">Total</th>
                                <th class="p-4 text-left text-gray-700">Status</th>
                                <th class="p-4 text-left text-gray-700">Order Date</th>
                                <th class="p-4 text-left text-gray-700 rounded-tr-2xl">Estimated Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $index => $order): ?>
                                <tr class="border-t table-row animate-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s">
                                    <td class="p-4"><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($order['estimated_delivery'] ?: 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script>
        // Scroll-triggered animations
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.animate-scroll');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            elements.forEach(element => observer.observe(element));
        });
    </script>
</body>
</html>