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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-indigo-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-4">
                <span class="text-gray-300">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-300">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-300">Services</a>
                <a href="cart.php" class="hover:text-indigo-300">Cart</a>
                <a href="orders.php" class="hover:text-indigo-300">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-300">Feedback</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-300">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-300">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-300">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Profile</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-4 bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <div>
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">Current Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">New Password</label>
                <input type="password" name="new_password" class="w-full p-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Update Profile</button>
        </form>
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-700 mb-4">Your Orders</h3>
            <?php if (empty($orders)): ?>
                <p class="text-gray-600">No orders found. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
            <?php else: ?>
                <table class="w-full">
                    <thead class="bg-indigo-100">
                        <tr>
                            <th class="p-4 text-left text-gray-700">Order ID</th>
                            <th class="p-4 text-left text-gray-700">Total</th>
                            <th class="p-4 text-left text-gray-700">Status</th>
                            <th class="p-4 text-left text-gray-700">Order Date</th>
                            <th class="p-4 text-left text-gray-700">Estimated Delivery</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t">
                                <td class="p-4"><?php echo htmlspecialchars($order['id']); ?></td>
                                <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['estimated_delivery']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>