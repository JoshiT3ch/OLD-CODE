<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders");
    $sales_summary = $stmt->fetch();

    $stmt = $pdo->query("SELECT COUNT(*) AS total_products FROM products");
    $inventory_summary = $stmt->fetch();

    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS total FROM orders GROUP BY month ORDER BY month DESC LIMIT 6");
    $sales_trends = $stmt->fetchAll();
    $trend_labels = array_column($sales_trends, 'month');
    $trend_values = array_column($sales_trends, 'total');
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-indigo-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark Admin</h1>
            <nav class="flex space-x-4">
                <span class="text-gray-300">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="dashboard.php" class="hover:text-indigo-300">Dashboard</a>
                <a href="catalog.php" class="hover:text-indigo-300">Services</a>
                <a href="cart.php" class="hover:text-indigo-300">Cart</a>
                <a href="orders.php" class="hover:text-indigo-300">Orders</a>
                <a href="feedback.php" class="hover:text-indigo-300">Feedback</a>
                <a href="profile.php" class="hover:text-indigo-300">Profile</a>
                <a href="admin.php" class="hover:text-indigo-300">Admin</a>
                <a href="logout.php" class="hover:text-indigo-300">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Reports</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Sales Summary</h3>
                <p class="text-gray-600">Total Orders: <?php echo htmlspecialchars($sales_summary['total_orders'] ?? 0); ?></p>
                <p class="text-gray-600">Total Revenue: $<?php echo number_format($sales_summary['total_revenue'] ?? 0, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Inventory Summary</h3>
                <p class="text-gray-600">Total Products: <?php echo htmlspecialchars($inventory_summary['total_products'] ?? 0); ?></p>
            </div>
        </div>
        <div class="mt-6 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-700 mb-4">Sales Trends (Last 6 Months)</h3>
            <canvas id="salesTrendChart"></canvas>
            <script>
                const ctx = document.getElementById('salesTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_reverse($trend_labels)); ?>,
                        datasets: [{
                            label: 'Revenue ($)',
                            data: <?php echo json_encode(array_reverse($trend_values)); ?>,
                            borderColor: 'rgba(79, 70, 229, 1)',
                            backgroundColor: 'rgba(79, 70, 229, 0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Revenue ($)' } }
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