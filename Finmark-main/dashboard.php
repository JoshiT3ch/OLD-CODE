<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables to avoid undefined errors
$user = null;
$order_count = 0;
$cart_total = 0;
$orders = [];
$chart_labels = [];
$chart_values = [];
$error = null;

try {
    // Fetch user data
    $stmt = $pdo->prepare("SELECT username, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // Fetch order count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM orders WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $order_count = $stmt->fetchColumn();

    // Fetch cart total
    $stmt = $pdo->prepare("SELECT SUM(ci.quantity * p.price) AS total FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_total = $stmt->fetchColumn() ?: 0;

    // Fetch recent orders
    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();

    // Fetch chart data
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS total FROM orders WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
    $stmt->execute([$_SESSION['user_id']]);
    $chart_data = $stmt->fetchAll();
    
    // Ensure chart data is valid
    $chart_labels = array_column($chart_data, 'month') ?: ['No Data'];
    $chart_values = array_column($chart_data, 'total') ?: [0];

    // Validate JSON encoding
    if (json_encode($chart_labels) === false || json_encode($chart_values) === false) {
        $error = "Error: Invalid chart data format.";
        $chart_labels = ['No Data'];
        $chart_values = [0];
    }
} catch (PDOException $e) {
    $error = "Database Error: " . htmlspecialchars($e->getMessage());
    // Log the error to a file instead of outputting it
    error_log($e->getMessage(), 3, 'errors.log');
    // Set fallback chart data
    $chart_labels = ['No Data'];
    $chart_values = [0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        nav a {
            transition: color 0.3s ease, transform 0.3s ease;
        }
        nav a:hover {
            transform: scale(1.1);
        }
        .table-row {
            transition: background-color 0.3s ease;
        }
        .table-row:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 font-sans">
    <header class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white sticky top-0 z-50 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-6">
                <span class="text-gray-200 font-medium">Hello, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-200">Services</a>
                <a href="cart.php" class="hover:text-indigo-200">Cart</a>
                <a href="orders.php" class="hover:text-indigo-200">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-200">Feedback</a>
                <a href="profile.php" class="hover:text-indigo-200">Profile</a>
                <?php if ($user['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-200">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-200">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8 animate-fade-in">
        <h2 class="text-4xl font-extrabold text-gray-800 mb-8">Dashboard</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-lg card">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Account Overview</h3>
                <p class="text-gray-600">Username: <?php echo htmlspecialchars($user['username']); ?></p>
                <p class="text-gray-600">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-gray-600">Role: <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Order Summary</h3>
                <p class="text-gray-600">Total Orders: <?php echo htmlspecialchars($order_count); ?></p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg card">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Cart Total</h3>
                <p class="text-gray-600">$<?php echo number_format($cart_total, 2); ?></p>
            </div>
        </div>
        <div class="mt-6 bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Orders</h3>
            <?php if (empty($orders)): ?>
                <p class="text-gray-600">No recent orders. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
            <?php else: ?>
                <table class="w-full">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="p-4 text-left text-gray-700">Order ID</th>
                            <th class="p-4 text-left text-gray-700">Total</th>
                            <th class="p-4 text-left text-gray-700">Status</th>
                            <th class="p-4 text-left text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t table-row">
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
        <div class="mt-6 bg-white p-6 rounded-2xl shadow-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Spending Trends (Last 6 Months)</h3>
            <canvas id="spendingChart"></canvas>
            <script>
                // Ensure the chart renders even if data is empty
                const ctx = document.getElementById('spendingChart').getContext('2d');
                try {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($chart_labels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                            datasets: [{
                                label: 'Total Spent ($)',
                                data: <?php echo json_encode($chart_values, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
                                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                                borderColor: 'rgba(79, 70, 229, 1)',
                                borderWidth: 1,
                                borderRadius: 8
                            }]
                        },
                        options: {
                            animation: {
                                duration: 1000,
                                easing: 'easeOutQuart'
                            },
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    title: { display: true, text: 'Amount ($)' },
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                                },
                                x: { grid: { display: false } }
                            },
                            plugins: {
                                legend: { display: true, position: 'top' }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Chart.js error:', e);
                }
            </script>
        </div>
    </main>
    <footer class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
</body>
</html>