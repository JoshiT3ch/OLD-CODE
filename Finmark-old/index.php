<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinMark - Financial Planning for Startups and SMBs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100 font-sans">
    <header class="bg-indigo-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">FinMark</h1>
            <nav class="flex space-x-4">
                <a href="#" class="hover:text-indigo-300">Home</a>
                <a href="#services" class="hover:text-indigo-300">Services</a>
                <a href="#clients" class="hover:text-indigo-300">Clients</a>
                <a href="#about" class="hover:text-indigo-300">About</a>
                <a href="#contact" class="hover:text-indigo-300">Contact</a>
                <a href="login.php" class="hover:text-indigo-300">Log In</a>
                <a href="register.php" class="bg-transparent border border-white px-4 py-2 rounded hover:bg-white hover:text-indigo-900">Get Started</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <section id="hero" class="text-center py-16">
            <h2 class="text-5xl font-bold text-gray-800 mb-6">Financial Planning for Startups and SMBs</h2>
            <p class="text-xl text-gray-600 mb-8">Empower your business with data-driven insights to optimize financial health, marketing strategies, and operational efficiency.</p>
            <a href="register.php" class="bg-yellow-400 text-indigo-900 px-6 py-3 rounded hover:bg-yellow-500">Try It Free →</a>
            <a href="#contact" class="block mt-4 text-indigo-600 hover:underline">Get in Touch</a>
        </section>
        <section id="services" class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Financial Analysis</h3>
                    <p class="text-gray-600">Assess financial health and uncover growth opportunities with actionable insights.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Marketing Analytics</h3>
                    <p class="text-gray-600">Optimize campaigns and boost ROI with data-driven customer behavior analysis.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Business Intelligence</h3>
                    <p class="text-gray-600">Transform raw data into customized dashboards for data-driven decisions.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Consulting Services</h3>
                    <p class="text-gray-600">Tailored strategies for SMEs in retail, e-commerce, healthcare, and manufacturing.</p>
                </div>
            </div>
        </section>
        <section id="clients" class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Clients</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Retail Businesses</h3>
                    <p class="text-gray-600">Expanding market reach and optimizing financial strategies.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">E-commerce Companies</h3>
                    <p class="text-gray-600">Driving sales with marketing analytics and business intelligence.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Healthcare Providers</h3>
                    <p class="text-gray-600">Optimizing resources with financial analysis.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-gray-700">Manufacturing Firms</h3>
                    <p class="text-gray-600">Improving operations and profitability through consulting.</p>
                </div>
            </div>
        </section>
        <section id="about" class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">About FinMark</h2>
            <p class="text-gray-600 mb-4"><strong>Problem Statement:</strong> Many SMEs struggle with financial inefficiencies, fragmented data, and ineffective marketing strategies, hindering growth and competitiveness.</p>
            <p class="text-gray-600 mb-4"><strong>Our Mission:</strong> To deliver cutting-edge financial and marketing solutions that enable data-driven decisions, driving growth and efficiency for SMEs across Southeast Asia.</p>
            <p class="text-gray-600 mb-4"><strong>Our Vision:</strong> To be Southeast Asia's leading provider of innovative financial and marketing analytics solutions.</p>
            <p class="text-gray-600"><strong>Our Values:</strong></p>
            <ul class="list-disc pl-5 text-gray-600">
                <li><strong>Innovation:</strong> Leveraging advanced technologies for cutting-edge solutions.</li>
                <li><strong>Integrity:</strong> Building trust through transparent practices.</li>
                <li><strong>Excellence:</strong> Committing to high-quality insights and service.</li>
                <li><strong>Collaboration:</strong> Partnering closely with clients for tailored success.</li>
            </ul>
        </section>
        <section id="contact" class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Contact Us</h2>
            <p class="text-gray-600 mb-4">Address: 123 Makati Avenue, Makati City, Manila, Philippines</p>
            <p class="text-gray-600 mb-4">Phone: +63 2 1234 5678</p>
            <p class="text-gray-600 mb-4">Email: <a href="mailto:info@finmarksolutions.ph" class="text-indigo-600 hover:underline">info@finmarksolutions.ph</a></p>
            <p class="text-gray-600">Website: <a href="http://www.finmarksolutions.ph" class="text-indigo-600 hover:underline">www.finmarksolutions.ph</a></p>
        </section>
    </main>
    <footer class="bg-indigo-900 text-white text-center py-4 mt-8">
        <p>© <?php echo date('Y'); ?> FinMark. All rights reserved.</p>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>