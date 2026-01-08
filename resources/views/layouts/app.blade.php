<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rizki Laundry')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #8b5cf6;
            --accent: #10b981;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }
        
        .mobile-menu.active {
            max-height: 500px;
        }
        
        @stack('styles')
    </style>
</head>
<body>s
    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-md shadow-sm fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tshirt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800">
                        Rizki<span class="gradient-text">Laundry</span>
                    </span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="nav-link text-gray-700 hover:text-blue-600 font-medium active text-blue-600">
                        Home
                    </a>
                    <a href="/tracking" class="nav-link text-gray-700 hover:text-blue-600 font-medium">
                        Tracking
                    </a>
                </div>
                
                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center space-x-3">
                    @auth('customer')
                        <a href="{{ url('/booking') }}" class="btn-primary px-5 py-2 rounded-lg font-medium text-white">
                            Schedule Pickup
                        </a>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ substr(auth('customer')->user()->name, 0, 1) }}
                                </div>
                                <span class="text-gray-700 font-medium">{{ auth('customer')->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            
                            <!-- Dropdown -->
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="font-semibold text-gray-800">{{ auth('customer')->user()->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ auth('customer')->user()->email ?? auth('customer')->user()->phone }}</p>
                                </div>
                                <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition">
                                    <span>My Profile</span>
                                </a>
                                <a href="{{ route('orders') }}" class="flex items-center px-4 py-2.5 hover:bg-gray-50 text-gray-700 transition">
                                    <span>My Orders</span>
                                </a>
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2.5 hover:bg-red-50 text-red-600 transition">
                                            <span>Logout</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary px-5 py-2 rounded-lg font-medium text-white">
                            Login
                        </a>
                    @endauth
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-xl text-gray-700"></i>
                </button>
            </div>
        </div>

        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu md:hidden bg-white border-t border-gray-100">
            <div class="px-4 py-4 space-y-1">
                <!-- Menu Items - Same as Desktop -->
                <a href="/" class="block px-4 py-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600 font-medium bg-blue-50 text-blue-600">
                    Home
                </a>
                <a href="/tracking" class="block px-4 py-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600 font-medium">
                    Tracking
                </a>
                
                @auth('customer')
                    <div class="pt-4 border-t border-gray-200 space-y-1">
                        <a href="{{ url('/booking') }}" class="block btn-primary py-3 rounded-lg font-medium text-white text-center mb-3">
                            Schedule Pickup
                        </a>
                        <div class="flex items-center px-4 py-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold mr-3">
                                {{ substr(auth('customer')->user()->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ auth('customer')->user()->name }}</p>
                                <p class="text-sm text-gray-500">{{ auth('customer')->user()->email ?? auth('customer')->user()->phone }}</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.index') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-50 text-gray-700">
                            My Profile
                        </a>
                        <a href="{{ route('orders') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-50 text-gray-700">
                            My Orders
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-50 text-red-600">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('login') }}" class="block btn-primary py-3 rounded-lg font-medium text-white text-center">
                            Login
                        </a>
                    </div>
                @endauth
                
                <!-- Guest Section (if not logged in) - Uncomment if needed
                <div class="pt-4 border-t border-gray-200">
                    <a href="/login" class="block btn-primary py-3 rounded-lg font-medium text-white text-center">
                        Login
                    </a>
                </div>
                -->
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Brand -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tshirt text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold">Rizki<span class="text-blue-400">Laundry</span></span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4">Professional laundry service at your doorstep. Making laundry day the easiest day of your week.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-blue-600 transition">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-blue-400 transition">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-pink-600 transition">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-green-600 transition">
                            <i class="fab fa-whatsapp text-sm"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Services -->
                <div>
                    <h4 class="font-semibold mb-4">Services</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Wash & Fold</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Dry Cleaning</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Bedding & Linens</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Alterations</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Stain Removal</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-400 mr-2 mt-1"></i>
                            <span class="text-gray-400">123 Laundry St, Clean City</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-blue-400 mr-2"></i>
                            <span class="text-gray-400">(555) 123-4567</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-blue-400 mr-2"></i>
                            <span class="text-gray-400">hello@rizkilaundry.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-6 border-t border-gray-800 text-center text-sm text-gray-400">
                <p>&copy; 2024 Rizki Laundry. All rights reserved. | 
                   <a href="#" class="hover:text-white transition">Privacy Policy</a> | 
                   <a href="#" class="hover:text-white transition">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            });
        }
        
        // Close mobile menu on link click
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            });
        });
        
        // Add scroll effect
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
            
            lastScroll = currentScroll;
        });
    </script>
    
    @stack('scripts')
</body>
</html>