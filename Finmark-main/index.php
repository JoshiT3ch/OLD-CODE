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
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .btn:active {
            animation: pulse 0.2s ease-in-out;
        }
        .payroll-image {
            transition: transform 0.3s ease;
        }
        .payroll-image:hover {
            transform: scale(1.05);
        }
        body {
            background: url('images/finmark-background.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Dark overlay for contrast */
            z-index: -1;
        }
        .logo-svg {
            width: 40px;
            height: 40px;
            margin-right: 8px;
        }
    </style>
</head>
<body class="font-sans">
    <header class="bg-gradient-to-r from-indigo-800 to-indigo-600 text-white sticky top-0 z-50 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <svg class="logo-svg" viewBox="0 0 100 100" xmlns="images/finmark-logo.png">
                    <circle cx="50" cy="50" r="45" fill="#FBBF24" />
                    <path d="M30 70 L50 30 L70 70" stroke="#4C51BF" stroke-width="10" fill="none" />
                    <path d="M40 50 H60" stroke="#FFFFFF" stroke-width="8" fill="none" />
                </svg>
                <h1 class="text-2xl font-bold">FinMark</h1>
            </div>
            <nav class="flex space-x-6">
                <a href="#" class="hover:text-indigo-200">Home</a>
                <a href="#services" class="hover:text-indigo-200">Services</a>
                <a href="#clients" class="hover:text-indigo-200">Clients</a>
                <a href="#about" class="hover:text-indigo-200">About</a>
                <a href="#contact" class="hover:text-indigo-200">Contact</a>
                <a href="login.php" class="hover:text-indigo-200">Log In</a>
                <a href="register.php" class="bg-transparent border border-white px-4 py-2 rounded-xl hover:bg-white hover:text-indigo-900 btn">Get Started</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <section id="hero" class="text-center py-16 animate-scroll">
            <h2 class="text-5xl font-extrabold text-white mb-6">Financial Planning for Startups and SMBs</h2>
            <p class="text-xl text-gray-200 mb-8">Empower your business with data-driven insights to optimize financial health, marketing strategies, and operational efficiency.</p>
            <a href="register.php" class="bg-yellow-400 text-indigo-900 px-6 py-3 rounded-xl hover:bg-yellow-500 btn">Try It Free</a>
            <a href="#contact" class="bg-yellow-400 text-indigo-900 px-6 py-3 rounded-xl hover:bg-yellow-500 btn">Get in Touch</a>
        </section>
        <section id="services" class="mt-16 animate-scroll">
            <h2 class="text-3xl font-extrabold text-white mb-6 text-center">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Financial Analysis</h3>
                    <p class="text-gray-600">Assess financial health and uncover growth opportunities with actionable insights.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Marketing Analytics</h3>
                    <p class="text-gray-600">Optimize campaigns and boost ROI with data-driven customer behavior analysis.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Business Intelligence</h3>
                    <p class="text-gray-600">Transform raw data into customized dashboards for data-driven decisions.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Payroll Services</h3>
                    <div class="mt-2">
                    <p class="text-gray-600 mt-2">Streamline payroll processes with accurate and timely solutions.</p>
                </div>
            </div>
        </section>
        <section id="clients" class="mt-16 animate-scroll">
            <h2 class="text-3xl font-extrabold text-white mb-6 text-center">Our Clients</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Retail Businesses</h3>
                    <p class="text-gray-600">Expanding market reach and optimizing financial strategies.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">E-commerce Companies</h3>
                    <p class="text-gray-600">Driving sales with marketing analytics and business intelligence.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Healthcare Providers</h3>
                    <p class="text-gray-600">Optimizing resources with financial analysis.</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                    <h3 class="text-xl font-semibold text-gray-700">Manufacturing Firms</h3>
                    <p class="text-gray-600">Improving operations and profitability through consulting.</p>
                </div>
            </div>
        </section>
        <section id="about" class="mt-16 animate-scroll">
            <h2 class="text-3xl font-extrabold text-white mb-6 text-center">About FinMark</h2>
            <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
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
            </div>
        </section>
        <section id="contact" class="mt-16 animate-scroll">
            <h2 class="text-3xl font-extrabold text-white mb-6 text-center">Contact Us</h2>
            <div class="bg-white p-6 rounded-2xl shadow-lg card animate-scroll">
                <p class="text-gray-600 mb-4">Address: 123 Makati Avenue, Makati City, Manila, Philippines</p>
                <p class="text-gray-600 mb-4">Phone: +63 2 1234 5678</p>
                <p class="text-gray-600 mb-4">Email: <a href="mailto:info@finmarksolutions.ph" class="text-indigo-600 hover:text-indigo-800">info@finmarksolutions.ph</a></p>
                <p class="text-gray-600">Website: <a href="http://www.finmarksolutions.ph" class="text-indigo-600 hover:text-indigo-800">www.finmarksolutions.ph</a></p>
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