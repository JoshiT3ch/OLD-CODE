<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, total_amount, status, created_at, estimated_delivery FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error: " . htmlspecialchars($e->getMessage());
    error_log("Fetch orders error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - FinMark</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
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
                <a href="profile.php" class="hover:text-indigo-200">Profile</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="hover:text-indigo-200">Admin</a>
                    <a href="reports.php" class="hover:text-indigo-200">Reports</a>
                <?php endif; ?>
                <a href="logout.php" class="hover:text-indigo-200">Logout</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8 animate-fade-in">
        <h2 class="text-4xl font-extrabold text-gray-800 mb-8">Your Orders</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-2xl mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (empty($orders)): ?>
            <p class="text-gray-600 text-center">No orders found. <a href="catalog.php" class="text-indigo-600 hover:underline">Browse services</a>.</p>
        <?php else: ?>
            <div class="bg-white p-6 rounded-2xl shadow-lg">
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
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t table-row">
                                <td class="p-4"><?php echo htmlspecialchars($order['id']); ?></td>
                                <td class="p-4">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['status']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                                <td class="p-4"><?php echo htmlspecialchars($order['estimated_delivery'] ?: 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <footer class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
</body>
</html>