<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT username, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM orders WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $order_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(ci.quantity * p.price) AS total FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_total = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS total FROM orders WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
    $stmt->execute([$_SESSION['user_id']]);
    $chart_data = $stmt->fetchAll();
    $chart_labels = array_column($chart_data, 'month');
    $chart_values = array_column($chart_data, 'total');
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-indigo-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-4">
                <span class="text-gray-300">Hello, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="catalog.php" class="hover:text-indigo-300">Services</a>
                <a href="cart.php" class="hover:text-indigo-300">Cart</a>
                <a href="orders.php" class="hover:text-indigo-300">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-300">Feedback</a>
                <a href="profile.php" class="hover:text-indigo-300">Profile</a>
                <?php if ($user['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-300">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-300">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-300">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-700">Account Overview</h3>
                <p class="text-gray-600">Username: <?php echo htmlspecialchars($user['username']); ?></p>
                <p class="text-gray-600">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-gray-600">Role: <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-700">Order Summary</h3>
                <p class="text-gray-600">Total Orders: <?php echo htmlspecialchars($order_count); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-700">Cart Total</h3>
                <p class="text-gray-600">$<?php echo number_format($cart_total, 2); ?></p>
            </div>
        </div>
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Orders</h3>
            <?php if (empty($orders)): ?>
                <p class="text-gray-600">No recent orders. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
            <?php else: ?>
                <table class="w-full">
                    <thead class="bg-indigo-100">
                        <tr>
                            <th class="p-4 text-left text-gray-700">Order ID</th>
                            <th class="p-4 text-left text-gray-700">Total</th>
                            <th class="p-4 text-left text-gray-700">Status</th>
                            <th class="p-4 text-left text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t">
                                <td class="p-4"><?php echo htmlspecialchars($order['id']); ?></td>
                                <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Spending Trends (Last 6 Months)</h3>
            <canvas id="spendingChart"></canvas>
            <script>
                const ctx = document.getElementById('spendingChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($chart_labels); ?>,
                        datasets: [{
                            label: 'Total Spent ($)',
                            data: <?php echo json_encode($chart_values); ?>,
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Amount ($)' } }
                        }
                    }
                });
            </script>
        </div>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>