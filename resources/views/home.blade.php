@extends('layouts.app')
@section('title', 'Home - Rizki Laundry')
@section('content')
    <!-- Floating Action Button -->
    <div class="floating-action" id="fab">
        <i class="fas fa-comment"></i>
    </div>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg text-white overflow-hidden -mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 pt-32">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-12 lg:mb-0">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-white bg-opacity-20 backdrop-blur-sm mb-6">
                        <i class="fas fa-bolt text-yellow-300 mr-2"></i>
                        <span class="text-sm font-medium">24/7 Pickup & Delivery Available</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">Professional <span class="text-yellow-300">Laundry</span> Service At Your Doorstep</h1>
                    <p class="text-xl mb-8 opacity-90 max-w-2xl">We pick up, clean, and deliver your clothes with care. Save time and enjoy fresh, clean laundry without the hassle. 100% satisfaction guaranteed.</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="btn-primary px-8 py-4 rounded-xl font-semibold text-lg flex items-center justify-center text-white">
                            <i class="fas fa-calendar-alt mr-3"></i> Schedule Pickup Now
                        </button>
                        <button class="btn-secondary px-8 py-4 rounded-xl font-semibold text-lg border-2 border-white flex items-center justify-center text-white hover:bg-white hover:text-blue-600">
                            <i class="fas fa-play-circle mr-3"></i> Watch How It Works
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-6 mt-12">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Eco-Friendly Detergents</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Free Pickup & Delivery</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-300 text-xl mr-3"></i>
                            <span>Same-Day Service</span>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 flex justify-center">
                    <div class="relative">
                    <img src="https://cdn-icons-png.flaticon.com/512/2553/2553642.png" alt="Laundry Service" class="hero-image w-full max-w-lg">
                        <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl p-6 shadow-2xl w-64">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">10,000+</p>
                                    <p class="text-sm text-gray-600">Happy Customers</p>
                                </div>
                            </div>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="text-gray-700 font-medium ml-2">4.8/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="stats-counter mb-2">25,000+</div>
                        <p class="text-gray-600 text-base">Items Cleaned</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">98%</div>
                        <p class="text-gray-600 text-base">Satisfaction Rate</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">45 min</div>
                        <p class="text-gray-600 text-base">Avg. Turnaround</p>
                    </div>
                    <div class="text-center">
                        <div class="stats-counter mb-2">24/7</div>
                        <p class="text-gray-600 text-base">Service Available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">How <span class="gradient-text">Rizki Laundry</span> Works</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Pilih layanan yang paling sesuai dengan kebutuhan Anda</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex rounded-full bg-white p-2 shadow-lg">
                    <button onclick="switchTab('pickup')" id="pickupTab" class="tab-button active px-8 py-3 rounded-full font-semibold text-lg transition-all">
                        <i class="fas fa-truck mr-2"></i>Antar-Jemput
                    </button>
                    <button onclick="switchTab('walkin')" id="walkinTab" class="tab-button px-8 py-3 rounded-full font-semibold text-lg text-gray-600">
                        <i class="fas fa-store mr-2"></i>Datang Langsung
                    </button>
                </div>
            </div>

            <!-- Pickup Service Content -->
            <div id="pickupContent" class="service-content active grid-cols-1 md:grid-cols-3 gap-10">
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">1</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Jadwalkan Penjemputan</h3>
                    <p class="text-gray-600">Booking waktu penjemputan melalui WhatsApp, website, atau telepon. Pilih layanan yang Anda butuhkan.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-hands-wash text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">2</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Kami Jemput & Cuci</h3>
                    <p class="text-gray-600">Tim profesional kami menjemput cucian Anda dan mencucinya dengan peralatan modern dan produk ramah lingkungan.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-tshirt text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">3</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Antar ke Rumah Anda</h3>
                    <p class="text-gray-600">Pakaian bersih dan wangi diantar ke rumah sesuai jadwal, rapi terlipat dan siap dipakai.</p>
                </div>
            </div>

            <!-- Walk-in Service Content -->
            <div id="walkinContent" class="service-content grid-cols-1 md:grid-cols-3 gap-10">
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">1</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Kunjungi Toko Kami</h3>
                    <p class="text-gray-600">Datang langsung ke outlet Rizki Laundry terdekat. Buka setiap hari pukul 08.00 - 20.00 WIB.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">2</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Serahkan Cucian</h3>
                    <p class="text-gray-600">Serahkan cucian Anda, pilih layanan yang diinginkan, dan dapatkan struk sebagai bukti transaksi.</p>
                </div>
                <div class="step-card text-center p-8 bg-white rounded-2xl shadow-lg">
                    <div class="feature-icon">
                        <i class="fas fa-clock text-white text-3xl"></i>
                    </div>
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-purple-600 font-bold mb-4 text-xl">3</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-800">Ambil Saat Siap</h3>
                    <p class="text-gray-600">Cucian siap dalam 1-3 hari. Kami akan menginformasikan via WhatsApp, lalu Anda bisa mengambilnya kapan saja.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
<section id="services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gray-800">Our <span class="gradient-text">Premium</span> Services</h2>
            <p class="text-gray-600 max-w-3xl mx-auto text-lg">We offer a wide range of laundry services tailored to meet your specific needs</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Wash & Fold -->
            <div class="service-card bg-white rounded-2xl overflow-hidden shadow-xl">
                <div class="h-56 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-blue-600/10"></div>
                    <i class="fas fa-washing-machine text-blue-600 text-7xl relative z-10 transform hover:scale-110 transition-transform duration-300"></i>
                    <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-lg">
                        <i class="fas fa-star mr-1"></i>Most Popular
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Wash & Fold</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Professional washing, drying, and folding of your everyday clothes. We use eco-friendly detergents and fabric softeners.</p>
                    
                    <!-- Features List -->
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                            <span>Same-day service available</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                            <span>Eco-friendly products</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                            <span>Neatly folded & packaged</span>
                        </li>
                    </ul>
                    
                    <div class="flex justify-between items-center mb-6 p-4 bg-blue-50 rounded-xl">
                        <div>
                            <span class="font-bold text-blue-600 text-3xl">$1.50</span>
                            <span class="text-gray-600 text-lg">/lb</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">Minimum</div>
                            <div class="text-sm text-gray-500">5 lbs</div>
                        </div>
                    </div>
                    <button class="w-full btn-primary py-3.5 rounded-xl font-semibold text-white shadow-lg hover:shadow-xl transition-all">
                        <i class="fas fa-shopping-cart mr-2"></i> Add Service
                    </button>
                </div>
            </div>

            <!-- Dry Cleaning -->
            <div class="service-card bg-white rounded-2xl overflow-hidden shadow-xl">
                <div class="h-56 bg-gradient-to-r from-green-50 to-green-100 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-400/10 to-green-600/10"></div>
                    <i class="fas fa-tshirt text-green-600 text-7xl relative z-10 transform hover:scale-110 transition-transform duration-300"></i>
                    <div class="absolute top-4 right-4 bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-lg">
                        <i class="fas fa-leaf mr-1"></i>Premium
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Dry Cleaning</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Expert dry cleaning for your delicate and special occasion garments. We handle suits, dresses, and delicate fabrics with care.</p>
                    
                    <!-- Features List -->
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Professional pressing included</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Stain treatment expertise</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span>Garment protection bags</span>
                        </li>
                    </ul>
                    
                    <div class="flex justify-between items-center mb-6 p-4 bg-green-50 rounded-xl">
                        <div>
                            <span class="font-bold text-green-600 text-3xl">$5.99</span>
                            <span class="text-gray-600 text-lg">/item</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">Includes</div>
                            <div class="text-sm text-gray-500">Free Pressing</div>
                        </div>
                    </div>
                    <button class="w-full btn-primary py-3.5 rounded-xl font-semibold text-white shadow-lg hover:shadow-xl transition-all" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-shopping-cart mr-2"></i> Add Service
                    </button>
                </div>
            </div>

            <!-- Bedding & Linens -->
            <div class="service-card bg-white rounded-2xl overflow-hidden shadow-xl">
                <div class="h-56 bg-gradient-to-r from-purple-50 to-purple-100 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400/10 to-purple-600/10"></div>
                    <i class="fas fa-bed text-purple-600 text-7xl relative z-10 transform hover:scale-110 transition-transform duration-300"></i>
                    <div class="absolute top-4 right-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-1.5 rounded-full text-sm font-semibold shadow-lg">
                        <i class="fas fa-home mr-1"></i>Home Care
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-bold mb-3 text-gray-800">Bedding & Linens</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Special care for your comforters, sheets, blankets, and other linens. Perfect for deep cleaning of larger items.</p>
                    
                    <!-- Features List -->
                    <ul class="mb-6 space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-purple-600 mr-2"></i>
                            <span>Deep cleaning process</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-purple-600 mr-2"></i>
                            <span>Allergen removal treatment</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check-circle text-purple-600 mr-2"></i>
                            <span>Fresh & sanitized</span>
                        </li>
                    </ul>
                    
                    <div class="flex justify-between items-center mb-6 p-4 bg-purple-50 rounded-xl">
                        <div>
                            <span class="font-bold text-purple-600 text-3xl">$12.99</span>
                            <span class="text-gray-600 text-lg">/item</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">Size</div>
                            <div class="text-sm text-gray-500">King/Queen</div>
                        </div>
                    </div>
                    <button class="w-full btn-primary py-3.5 rounded-xl font-semibold text-white shadow-lg hover:shadow-xl transition-all" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        <i class="fas fa-shopping-cart mr-2"></i> Add Service
                    </button>
                </div>
            </div>
        </div>
</section>

    <!-- Tracking Section -->
<section id="tracking" class="py-20 bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12 items-center">
            <div class="lg:w-1/2">
                <h2 class="text-4xl font-bold mb-6 text-gray-800">Real-Time <span class="gradient-text">Order Tracking</span></h2>
                <p class="text-gray-600 mb-8 text-lg">Track your laundry from pickup to delivery with our live tracking system. Get notifications at every step.</p>
                
                <div class="bg-white rounded-2xl p-8 shadow-xl mb-8">
                    <div class="flex mb-6">
                        <input type="text" id="trackingInput" placeholder="Enter your tracking number" class="flex-grow px-6 py-4 border border-gray-300 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button onclick="trackOrder()" class="btn-primary px-8 rounded-r-2xl font-medium text-white">
                            <i class="fas fa-search mr-2"></i> Track
                        </button>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-600">Example tracking number: <span class="font-mono font-bold text-blue-600 cursor-pointer hover:underline" onclick="fillExample()">FC-789456123</span></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
                                <i class="fas fa-bell text-blue-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-lg">Live Notifications</h4>
                        </div>
                        <p class="text-gray-600">Get real-time updates on your phone for every step of the process.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                                <i class="fas fa-map-marker-alt text-green-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-lg">Driver Tracking</h4>
                        </div>
                        <p class="text-gray-600">See exactly where your laundry is with our live driver tracking.</p>
                    </div>
                </div>
            </div>
            
            <!-- Initial State - Hidden -->
            <div id="trackingPlaceholder" class="lg:w-1/2 bg-white rounded-2xl p-12 shadow-2xl text-center">
                <div class="mb-6">
                    <i class="fas fa-box-open text-gray-300 text-8xl mb-6"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Enter Your Tracking Number</h3>
                <p class="text-gray-600 text-lg mb-6">Track your order status in real-time by entering your tracking number above.</p>
                <div class="flex items-center justify-center gap-4 text-gray-500">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                        <span class="text-sm">Secure</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                        <span class="text-sm">Real-time</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-mobile-alt text-blue-600 mr-2"></i>
                        <span class="text-sm">Mobile Alerts</span>
                    </div>
                </div>
            </div>
            
            <!-- Tracking Result - Initially Hidden -->
            <div id="trackingResult" class="lg:w-1/2 bg-white rounded-2xl p-8 shadow-2xl" style="display: none;">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Order #<span id="orderNumber"></span></h3>
                        <p class="text-gray-600 text-sm mt-1">Customer: <span class="font-medium">John Doe</span></p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="px-4 py-2 bg-blue-100 text-blue-600 rounded-full font-medium text-sm">In Progress</span>
                        <button onclick="closeTracking()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="space-y-8">
                    <div class="tracking-step completed">
                        <h4 class="font-bold text-lg text-gray-800 mb-1">Order Received</h4>
                        <p class="text-gray-600">Today, 10:30 AM</p>
                        <p class="text-gray-500 text-sm mt-1">We've received your laundry order and it's being processed.</p>
                    </div>
                    
                    <div class="tracking-step active">
                        <h4 class="font-bold text-lg text-gray-800 mb-1">Processing</h4>
                        <p class="text-gray-600">Today, 11:45 AM</p>
                        <p class="text-gray-500 text-sm mt-1">Your clothes are being washed with eco-friendly detergents.</p>
                    </div>
                    
                    <div class="tracking-step">
                        <h4 class="font-bold text-lg text-gray-800 mb-1 opacity-70">Quality Check</h4>
                        <p class="text-gray-600 opacity-70">Estimated: Today, 2:00 PM</p>
                        <p class="text-gray-500 text-sm mt-1">Each item will be inspected for quality before packaging.</p>
                    </div>
                    
                    <div class="tracking-step">
                        <h4 class="font-bold text-lg text-gray-800 mb-1 opacity-70">Out for Delivery</h4>
                        <p class="text-gray-600 opacity-70">Estimated: Today, 4:30 PM</p>
                        <p class="text-gray-500 text-sm mt-1">Your clean laundry will be delivered to your doorstep.</p>
                    </div>
                </div>
                
                <div class="mt-10 pt-8 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <p class="text-gray-600">Estimated Delivery</p>
                            <p class="text-2xl font-bold text-gray-800">Today, 5:00 - 6:00 PM</p>
                        </div>
                        <button class="btn-primary px-6 py-3 rounded-xl font-medium text-white whitespace-nowrap">
                            <i class="fas fa-phone-alt mr-2"></i> Contact Driver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Pricing Section -->
<section id="pricing" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4 text-gray-800">Affordable <span class="gradient-text">Pricing</span> for Everyone</h2>
            <p class="text-gray-600 max-w-3xl mx-auto text-lg">Pay as you go with no commitment, or save more with our membership plans</p>
        </div>

        <!-- Toggle Switch -->
        <div class="flex justify-center mb-12">
            <div class="inline-flex items-center bg-white rounded-full p-2 shadow-lg">
                <button onclick="switchPricing('payg')" id="paygBtn" class="pricing-toggle active px-8 py-3 rounded-full font-semibold transition-all">
                    Pay as You Go
                </button>
                <button onclick="switchPricing('membership')" id="memberBtn" class="pricing-toggle px-8 py-3 rounded-full font-semibold transition-all">
                    Membership Plans
                    <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Save 20%</span>
                </button>
            </div>
        </div>

        <!-- Pay as You Go Content -->
        <div id="paygContent" class="pricing-content active">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Wash & Fold Card -->
                <div class="pricing-card-modern group">
                    <div class="icon-wrapper bg-gradient-to-br from-blue-400 to-blue-600">
                        <i class="fas fa-tshirt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Wash & Fold</h3>
                    <p class="text-gray-600 text-sm mb-4">Everyday laundry service</p>
                    <div class="price-tag">
                        <span class="text-4xl font-bold text-gray-800">Rp 7K</span>
                        <span class="text-gray-600">/kg</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Min. 3kg</p>
                    <ul class="space-y-2 mb-6 text-sm">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-blue-600 mr-2 text-xs"></i>
                            <span>24-48 jam selesai</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-blue-600 mr-2 text-xs"></i>
                            <span>Wangi & bersih</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-blue-600 mr-2 text-xs"></i>
                            <span>Dilipat rapi</span>
                        </li>
                    </ul>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition-all transform group-hover:scale-105">
                        Pilih Layanan
                    </button>
                </div>

                <!-- Dry Clean Card -->
                <div class="pricing-card-modern group">
                    <div class="icon-wrapper bg-gradient-to-br from-purple-400 to-purple-600">
                        <i class="fas fa-spray-can text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Dry Clean</h3>
                    <p class="text-gray-600 text-sm mb-4">Pakaian formal & delicate</p>
                    <div class="price-tag">
                        <span class="text-4xl font-bold text-gray-800">Rp 25K</span>
                        <span class="text-gray-600">/pcs</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Mulai dari</p>
                    <ul class="space-y-2 mb-6 text-sm">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-purple-600 mr-2 text-xs"></i>
                            <span>Tanpa air</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-purple-600 mr-2 text-xs"></i>
                            <span>Free pressing</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-purple-600 mr-2 text-xs"></i>
                            <span>Aman untuk bahan sensitif</span>
                        </li>
                    </ul>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-xl font-semibold transition-all transform group-hover:scale-105">
                        Pilih Layanan
                    </button>
                </div>

                <!-- Express Card -->
                <div class="pricing-card-modern group">
                    <div class="icon-wrapper bg-gradient-to-br from-orange-400 to-orange-600">
                        <i class="fas fa-bolt text-white text-3xl"></i>
                    </div>
                    <div class="absolute top-4 right-4 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                        CEPAT
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Express 6 Jam</h3>
                    <p class="text-gray-600 text-sm mb-4">Butuh cepat? Kami siap!</p>
                    <div class="price-tag">
                        <span class="text-4xl font-bold text-gray-800">Rp 12K</span>
                        <span class="text-gray-600">/kg</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Min. 3kg</p>
                    <ul class="space-y-2 mb-6 text-sm">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-orange-600 mr-2 text-xs"></i>
                            <span>Selesai 6 jam</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-orange-600 mr-2 text-xs"></i>
                            <span>Prioritas tinggi</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-orange-600 mr-2 text-xs"></i>
                            <span>Same-day delivery</span>
                        </li>
                    </ul>
                    <button class="w-full bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-xl font-semibold transition-all transform group-hover:scale-105">
                        Pilih Layanan
                    </button>
                </div>

                <!-- Premium Care Card -->
                <div class="pricing-card-modern group">
                    <div class="icon-wrapper bg-gradient-to-br from-pink-400 to-pink-600">
                        <i class="fas fa-gem text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Premium Care</h3>
                    <p class="text-gray-600 text-sm mb-4">Sepatu, tas & bedding</p>
                    <div class="price-tag">
                        <span class="text-4xl font-bold text-gray-800">Rp 35K</span>
                        <span class="text-gray-600">/pcs</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Mulai dari</p>
                    <ul class="space-y-2 mb-6 text-sm">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-pink-600 mr-2 text-xs"></i>
                            <span>Deep cleaning</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-pink-600 mr-2 text-xs"></i>
                            <span>Perawatan khusus</span>
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-pink-600 mr-2 text-xs"></i>
                            <span>Treatment profesional</span>
                        </li>
                    </ul>
                    <button class="w-full bg-pink-600 hover:bg-pink-700 text-white py-3 rounded-xl font-semibold transition-all transform group-hover:scale-105">
                        Pilih Layanan
                    </button>
                </div>
            </div>

            <!-- Additional Services -->
            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">Layanan Tambahan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Antar Jemput</h4>
                            <p class="text-sm text-gray-600">Gratis radius 5km</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Garansi 100%</h4>
                            <p class="text-sm text-gray-600">Puas atau uang kembali</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-star text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Poin Reward</h4>
                            <p class="text-sm text-gray-600">Setiap transaksi dapat poin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Membership Plans Content -->
        <div id="memberContent" class="pricing-content">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Basic Plan -->
                <div class="membership-card">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Basic</h3>
                        <p class="text-gray-600 mb-6">Untuk pengguna reguler</p>
                        <div class="mb-6">
                            <span class="text-5xl font-bold text-gray-800">Rp 199K</span>
                            <span class="text-gray-600">/bulan</span>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-4 mb-6">
                            <p class="text-sm text-blue-800 font-semibold">Hemat Rp 70K per bulan!</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">20kg Wash & Fold</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Gratis antar jemput unlimited</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Diskon 10% layanan lain</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Priority customer service</span>
                            </li>
                        </ul>
                        <button class="w-full bg-white border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white py-4 rounded-xl font-semibold transition-all">
                            Pilih Paket
                        </button>
                    </div>
                </div>

                <!-- Premium Plan - Featured -->
                <div class="membership-card featured">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-yellow-400 to-orange-400 text-white px-6 py-2 rounded-full font-bold text-sm shadow-lg">
                        PALING POPULER
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-white mb-2">Premium</h3>
                        <p class="text-blue-100 mb-6">Best value for families</p>
                        <div class="mb-6">
                            <span class="text-5xl font-bold text-white">Rp 349K</span>
                            <span class="text-blue-100">/bulan</span>
                        </div>
                        <div class="bg-white/20 backdrop-blur rounded-xl p-4 mb-6">
                            <p class="text-sm text-white font-semibold">Hemat Rp 150K per bulan!</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-yellow-300 mr-3 mt-1"></i>
                                <span class="text-white">40kg Wash & Fold</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-yellow-300 mr-3 mt-1"></i>
                                <span class="text-white">5 Dry Clean items gratis</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-yellow-300 mr-3 mt-1"></i>
                                <span class="text-white">Express service 2x gratis</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-yellow-300 mr-3 mt-1"></i>
                                <span class="text-white">Diskon 20% layanan lain</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-yellow-300 mr-3 mt-1"></i>
                                <span class="text-white">Priority scheduling</span>
                            </li>
                        </ul>
                        <button class="w-full bg-white text-blue-600 hover:bg-gray-100 py-4 rounded-xl font-semibold transition-all transform hover:scale-105">
                            Pilih Paket
                        </button>
                    </div>
                </div>

                <!-- Business Plan -->
                <div class="membership-card">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Business</h3>
                        <p class="text-gray-600 mb-6">Untuk bisnis & kos-kosan</p>
                        <div class="mb-6">
                            <span class="text-5xl font-bold text-gray-800">Rp 599K</span>
                            <span class="text-gray-600">/bulan</span>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-4 mb-6">
                            <p class="text-sm text-purple-800 font-semibold">Hemat Rp 250K per bulan!</p>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">80kg Wash & Fold</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">10 Dry Clean items gratis</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Unlimited express service</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Dedicated account manager</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-purple-600 mr-3 mt-1"></i>
                                <span class="text-gray-700">Invoice bulanan</span>
                            </li>
                        </ul>
                        <button class="w-full bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white py-4 rounded-xl font-semibold transition-all">
                            Pilih Paket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-10 text-white text-center shadow-2xl">
            <h3 class="text-3xl font-bold mb-4">Masih bingung pilih yang mana?</h3>
            <p class="text-lg mb-6 opacity-90 max-w-2xl mx-auto">Chat dengan tim kami dan dapatkan rekomendasi paket yang paling sesuai dengan kebutuhan Anda</p>
            <button class="inline-flex items-center px-8 py-4 bg-white text-blue-600 rounded-full font-semibold hover:shadow-lg transition-all hover:scale-105">
                <i class="fab fa-whatsapp mr-2 text-xl"></i> Chat di WhatsApp
            </button>
        </div>
    </div>
</section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">What Our <span class="gradient-text">Customers</span> Say</h2>
                <p class="text-gray-600 max-w-3xl mx-auto text-lg">Join thousands of satisfied customers who trust us with their laundry needs</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            SJ
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Sarah Johnson</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Rizki Laundry has saved me so much time! The clothes always come back perfectly folded and smelling amazing. I love their eco-friendly approach too!"</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Customer for 2 years
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-blue-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            MC
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Michael Chen</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"The tracking feature is fantastic. I always know exactly where my laundry is in the process. The premium bundle is worth every penny for our family of four!"</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Customer for 1 year
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl mr-4">
                            DM
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">David Martinez</h4>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"I was skeptical at first, but after trying Rizki Laundry, I'll never go back to doing laundry myself. Their dry cleaning service is exceptional for my work suits."</p>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt mr-2"></i> Customer for 8 months
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <div class="inline-flex items-center bg-white rounded-full px-6 py-3 shadow-lg">
                    <i class="fas fa-star text-yellow-400 text-2xl mr-3"></i>
                    <div>
                        <p class="font-bold text-gray-800">Rated 4.8/5</p>
                        <p class="text-sm text-gray-600">Based on 2,500+ reviews</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-6">Ready to Experience Hassle-Free Laundry?</h2>
            <p class="text-xl mb-10 max-w-3xl mx-auto opacity-90">Join thousands of satisfied customers who save 5+ hours every week by letting us handle their laundry. Download our app today and get 20% off your first order!</p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-6 mb-12">
                <button class="bg-white text-blue-600 hover:bg-gray-100 px-10 py-4 rounded-xl font-bold text-lg transition duration-300 shadow-lg flex items-center justify-center">
                    <i class="fab fa-apple text-2xl mr-3"></i>
                    <div class="text-left">
                        <div class="text-xs">Download on the</div>
                        <div class="text-lg">App Store</div>
                    </div>
                </button>
                <button class="bg-gray-900 text-white hover:bg-gray-800 px-10 py-4 rounded-xl font-bold text-lg transition duration-300 shadow-lg flex items-center justify-center">
                    <i class="fab fa-google-play text-2xl mr-3"></i>
                    <div class="text-left">
                        <div class="text-xs">Get it on</div>
                        <div class="text-lg">Google Play</div>
                    </div>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="stats-card rounded-2xl p-6">
                    <div class="text-3xl font-bold mb-2">20% OFF</div>
                    <p class="opacity-90">First order discount</p>
                </div>
                <div class="stats-card rounded-2xl p-6">
                    <div class="text-3xl font-bold mb-2">Free</div>
                    <p class="opacity-90">Pickup & Delivery</p>
                </div>
                <div class="stats-card rounded-2xl p-6">
                    <div class="text-3xl font-bold mb-2">24/7</div>
                    <p class="opacity-90">Customer Support</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
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
        overflow-x: hidden;
    }
    
    .gradient-bg {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .hero-image {
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(2deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    
    .service-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .service-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .service-card:hover::after {
        transform: scaleX(1);
    }
    
    .pricing-card {
        transition: all 0.3s ease;
    }
    
    .pricing-card:hover {
        transform: scale(1.03);
    }
    
    .feature-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    }
    
    .stats-card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    /* Tracking Step Styles - Updated */
    .tracking-step {
        position: relative;
        padding-left: 40px;
        transition: all 0.3s ease;
    }
    
    .tracking-step::before {
        content: '';
        position: absolute;
        left: 0;
        top: 5px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e5e7eb;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .tracking-step::after {
        content: '';
        position: absolute;
        left: 11px;
        top: 29px;
        width: 2px;
        height: calc(100% + 20px);
        background-color: #e5e7eb;
    }
    
    .tracking-step:last-child::after {
        display: none;
    }
    
    .tracking-step.active::before {
        background-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2), 0 0 0 5px rgba(59, 130, 246, 0.1);
        animation: pulse 2s ease-in-out infinite;
    }
    
    .tracking-step.completed::before {
        background-color: var(--accent);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    
    .tracking-step.completed::after {
        background-color: var(--accent);
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2), 0 0 0 5px rgba(59, 130, 246, 0.1);
        }
        50% {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3), 0 0 0 8px rgba(59, 130, 246, 0.15);
        }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .slide-in {
        animation: slideIn 0.5s ease-out;
    }
    
    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .testimonial-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
    }
    
    .btn-primary::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.7s ease;
    }
    
    .btn-primary:hover::after {
        left: 100%;
    }
    
    .btn-secondary {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: var(--primary);
        color: white;
    }
    
    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        z-index: 100;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .floating-action:hover {
        transform: scale(1.1) rotate(90deg);
    }
    
    .stats-counter {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Tab Styles for How It Works */
    .tab-button {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .tab-button.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }
    
    .tab-button::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.7s ease;
    }
    
    .tab-button:hover::after {
        left: 100%;
    }
    
    .service-content {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }
    
    .service-content.active {
        display: grid;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .step-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }
    
    .step-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .step-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .step-card:hover::after {
        transform: scaleX(1);
    }
    
    /* Pricing Section Styles */
    .pricing-toggle {
        color: #6b7280;
        transition: all 0.3s ease;
    }
    
    .pricing-toggle.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .pricing-content {
        display: none;
        animation: fadeIn 0.5s ease-in;
    }
    
    .pricing-content.active {
        display: block;
    }
    
    .pricing-card-modern {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .pricing-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .price-tag {
        margin: 16px 0;
    }
    
    .membership-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .membership-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }
    
    .membership-card.featured {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        transform: scale(1.05);
    }
    
    .membership-card.featured:hover {
        transform: scale(1.08) translateY(-8px);
    }
    
    @media (max-width: 768px) {
        .hero-image {
            width: 70%;
        }
        
        .stats-counter {
            font-size: 2rem;
        }
    }
</style>
@endpush
@push('scripts')
<script>
    // FAB click handler
    document.getElementById('fab').addEventListener('click', () => {
        alert("Hello! How can we help you today? You can reach us at (555) 123-4567 or hello@rizkilaundry.com");
    });
    
    // Counter animation
    function animateCounter(element, target, duration) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            const currentValue = Math.floor(start);
            if (element.textContent.includes('%')) {
                element.textContent = currentValue + '%';
            } else if (element.textContent.includes('min')) {
                element.textContent = currentValue + ' min';
            } else if (element.textContent.includes('24/7')) {
                element.textContent = '24/7';
                clearInterval(timer);
            } else {
                element.textContent = currentValue.toLocaleString() + '+';
            }
            
            if (start >= target) {
                clearInterval(timer);
            }
        }, 16);
    }
    
    // Initialize counters when in viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = document.querySelectorAll('.stats-counter');
                counters.forEach(counter => {
                    const text = counter.textContent;
                    if (text.includes('%')) {
                        animateCounter(counter, 98, 2000);
                    } else if (text.includes('min')) {
                        animateCounter(counter, 45, 2000);
                    } else if (text.includes('24/7')) {
                        counter.textContent = '24/7';
                    } else {
                        const target = parseInt(text.replace(/[^0-9]/g, ''));
                        if (!isNaN(target)) {
                            animateCounter(counter, target, 2000);
                        }
                    }
                });
                observer.disconnect();
            }
        });
    }, { threshold: 0.5 });
    
    const statsSection = document.querySelector('.stats-counter');
    if (statsSection) {
        observer.observe(statsSection);
    }
    
    // Tab switching function for How It Works
    function switchTab(service) {
        const pickupTab = document.getElementById('pickupTab');
        const walkinTab = document.getElementById('walkinTab');
        const pickupContent = document.getElementById('pickupContent');
        const walkinContent = document.getElementById('walkinContent');
        
        if (service === 'pickup') {
            pickupTab.classList.add('active');
            walkinTab.classList.remove('active');
            pickupContent.classList.add('active');
            walkinContent.classList.remove('active');
        } else {
            walkinTab.classList.add('active');
            pickupTab.classList.remove('active');
            walkinContent.classList.add('active');
            pickupContent.classList.remove('active');
        }
    }
    
    // Pricing toggle function
    function switchPricing(type) {
        const paygBtn = document.getElementById('paygBtn');
        const memberBtn = document.getElementById('memberBtn');
        const paygContent = document.getElementById('paygContent');
        const memberContent = document.getElementById('memberContent');
        
        if (type === 'payg') {
            paygBtn.classList.add('active');
            memberBtn.classList.remove('active');
            paygContent.classList.add('active');
            memberContent.classList.remove('active');
        } else {
            memberBtn.classList.add('active');
            paygBtn.classList.remove('active');
            memberContent.classList.add('active');
            paygContent.classList.remove('active');
        }
    }
    
    // Tracking Order Functions
    function trackOrder() {
        const trackingNumber = document.getElementById('trackingInput').value.trim();
        
        if (!trackingNumber) {
            alert('Please enter a tracking number');
            return;
        }
        
        // Validate format (simple validation)
        if (!trackingNumber.match(/^[A-Z]{2}-\d{9}$/i)) {
            alert('Invalid tracking number format. Please use format: XX-123456789');
            return;
        }
        
        // Hide placeholder, show result
        document.getElementById('trackingPlaceholder').style.display = 'none';
        const resultDiv = document.getElementById('trackingResult');
        resultDiv.style.display = 'block';
        resultDiv.classList.add('slide-in');
        
        // Update order number
        document.getElementById('orderNumber').textContent = trackingNumber.toUpperCase();
        
        // Scroll to result (optional)
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function closeTracking() {
        document.getElementById('trackingResult').style.display = 'none';
        document.getElementById('trackingPlaceholder').style.display = 'block';
        document.getElementById('trackingInput').value = '';
    }
    
    function fillExample() {
        document.getElementById('trackingInput').value = 'FC-789456123';
    }
    
    // Allow Enter key to submit tracking
    document.addEventListener('DOMContentLoaded', function() {
        const trackingInput = document.getElementById('trackingInput');
        if (trackingInput) {
            trackingInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    trackOrder();
                }
            });
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
@endpush