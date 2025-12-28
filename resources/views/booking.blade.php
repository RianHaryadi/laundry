@extends('layouts.app')

@section('title', 'Booking - LaundryKu')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 mb-6">
                <i class="fas fa-calendar-alt text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Booking Layanan</h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Pesan layanan laundry Anda dengan mudah. Isi form di bawah ini dan kami akan segera menghubungi Anda.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white">Form Pemesanan</h2>
                        <p class="text-blue-100">Isi data dengan benar untuk proses yang lebih cepat</p>
                    </div>
                    
                    <form action="{{ route('booking.store') }}" method="POST" class="p-8">
                        @csrf
                        
                        <!-- Progress Steps -->
                        <div class="mb-10">
                            <div class="flex justify-between items-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold mb-2">1</div>
                                    <span class="text-sm font-medium text-gray-700">Pilih Layanan</span>
                                </div>
                                <div class="h-1 w-1/4 bg-blue-200"></div>
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-blue-200 text-gray-600 flex items-center justify-center font-bold mb-2">2</div>
                                    <span class="text-sm font-medium text-gray-500">Detail Pesanan</span>
                                </div>
                                <div class="h-1 w-1/4 bg-blue-200"></div>
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-blue-200 text-gray-600 flex items-center justify-center font-bold mb-2">3</div>
                                    <span class="text-sm font-medium text-gray-500">Konfirmasi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Service Selection -->
                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">Pilih Jenis Layanan</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($services as $service)
                                <div class="service-option relative">
                                    <input type="radio" name="service" id="service_{{ $loop->index }}" value="{{ $service['name'] }}" class="hidden peer" required>
                                    <label for="service_{{ $loop->index }}" class="block p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-14 h-14 rounded-lg bg-gradient-to-br {{ $service['color'] }} flex items-center justify-center mr-4">
                                                <i class="{{ $service['icon'] }} text-white text-xl"></i>
                                            </div>
                                            <div class="flex-grow">
                                                <h3 class="font-bold text-gray-800">{{ $service['name'] }}</h3>
                                                <p class="text-sm text-gray-600">{{ $service['description'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-blue-600 font-bold text-lg">Rp {{ number_format($service['price']) }}</div>
                                                <div class="text-gray-500 text-sm">/{{ $service['unit'] }}</div>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex items-center text-sm text-gray-600">
                                            <i class="fas fa-clock mr-2"></i>
                                            <span>Estimasi: {{ $service['eta'] }}</span>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('service')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">Jumlah Laundry</label>
                            <div class="flex items-center bg-gray-50 rounded-xl p-4">
                                <button type="button" id="decrease" class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="quantity" id="quantity" min="1" step="0.5" value="1" class="flex-grow mx-4 text-center text-2xl font-bold bg-transparent focus:outline-none">
                                <button type="button" id="increase" class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <span class="ml-4 text-gray-600 font-medium">kg</span>
                            </div>
                            <div class="mt-4 grid grid-cols-4 gap-2">
                                <button type="button" class="quick-weight py-2 bg-gray-100 rounded-lg hover:bg-blue-100" data-weight="2">2 kg</button>
                                <button type="button" class="quick-weight py-2 bg-gray-100 rounded-lg hover:bg-blue-100" data-weight="5">5 kg</button>
                                <button type="button" class="quick-weight py-2 bg-gray-100 rounded-lg hover:bg-blue-100" data-weight="10">10 kg</button>
                                <button type="button" class="quick-weight py-2 bg-gray-100 rounded-lg hover:bg-blue-100" data-weight="15">15 kg</button>
                            </div>
                            @error('quantity')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pickup Date & Time -->
                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">Jadwal Pickup</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-600 mb-2">Tanggal Pickup</label>
                                    <div class="relative">
                                        <input type="date" name="pickup_date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="{{ date('Y-m-d') }}" required>
                                        <i class="fas fa-calendar absolute right-4 top-4 text-gray-400"></i>
                                    </div>
                                    @error('pickup_date')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-600 mb-2">Waktu Pickup</label>
                                    <select name="pickup_time" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        <option value="">Pilih Waktu</option>
                                        <option value="08:00-10:00">08:00 - 10:00</option>
                                        <option value="10:00-12:00">10:00 - 12:00</option>
                                        <option value="13:00-15:00">13:00 - 15:00</option>
                                        <option value="15:00-17:00">15:00 - 17:00</option>
                                        <option value="17:00-19:00">17:00 - 19:00</option>
                                    </select>
                                    @error('pickup_time')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">Informasi Kontak</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-600 mb-2">Nomor Telepon</label>
                                    <div class="relative">
                                        <div class="absolute left-4 top-4 text-gray-500">+62</div>
                                        <input type="tel" name="phone" class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="812-3456-7890" required>
                                        <i class="fas fa-phone absolute right-4 top-4 text-gray-400"></i>
                                    </div>
                                    @error('phone')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-600 mb-2">Email</label>
                                    <div class="relative">
                                        <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="nama@email.com">
                                        <i class="fas fa-envelope absolute right-4 top-4 text-gray-400"></i>
                                    </div>
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">Alamat Pickup</label>
                            <div class="relative">
                                <textarea name="address" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan alamat lengkap untuk pickup" required></textarea>
                                <i class="fas fa-map-marker-alt absolute right-4 top-4 text-gray-400"></i>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-2"></i>
                                Pastikan alamat lengkap dan jelas untuk mempermudah driver
                            </div>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-10">
                            <label class="block text-gray-600 mb-2">Catatan Khusus (Opsional)</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Ada noda membandel, pakai pewangi lavender, dll."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 rounded-xl hover:from-blue-700 hover:to-indigo-800 font-bold text-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i> Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan Pesanan</h2>
                    
                    <!-- Order Details -->
                    <div class="space-y-6 mb-8">
                        <div class="flex justify-between items-center pb-4 border-b">
                            <div>
                                <h3 class="font-medium text-gray-600">Layanan</h3>
                                <p class="text-gray-800 font-medium mt-1" id="service-display">-</p>
                            </div>
                            <div class="text-right">
                                <h3 class="font-medium text-gray-600">Harga</h3>
                                <p class="text-blue-600 font-bold mt-1" id="price-display">Rp 0</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center pb-4 border-b">
                            <div>
                                <h3 class="font-medium text-gray-600">Jumlah</h3>
                                <p class="text-gray-800 font-medium mt-1"><span id="quantity-display">0</span> kg</p>
                            </div>
                            <div class="text-right">
                                <h3 class="font-medium text-gray-600">Subtotal</h3>
                                <p class="text-blue-600 font-bold mt-1" id="subtotal-display">Rp 0</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center pb-4 border-b">
                            <div>
                                <h3 class="font-medium text-gray-600">Pickup</h3>
                                <p class="text-gray-800 font-medium mt-1" id="pickup-display">-</p>
                            </div>
                            <div class="text-right">
                                <h3 class="font-medium text-gray-600">Biaya</h3>
                                <p class="text-green-600 font-bold mt-1">Gratis</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Total Pembayaran</h3>
                                <p class="text-gray-600 text-sm">Sudah termasuk pajak</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-blue-600" id="total-display">Rp 0</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estimated Delivery -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <i class="fas fa-shipping-fast text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Estimasi Pengiriman</h3>
                                <p class="text-gray-600 text-sm" id="delivery-display">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Benefits -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-600">Garansi kualitas 100%</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-600">Free pickup & delivery</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-600">Pembayaran saat barang diterima</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-600">Customer support 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .service-option input:checked + label {
        border-color: #3b82f6;
        background-color: #eff6ff;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1);
    }
    
    .service-option label:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        cursor: pointer;
    }
    
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        opacity: 1;
        height: 40px;
    }
</style>

<script>
    // Sample services data - replace with actual PHP data
    const services = {
        @foreach($services as $service)
            "{{ $service['name'] }}": {
                price: {{ $service['price'] }},
                unit: "{{ $service['unit'] }}",
                eta: "{{ $service['eta'] ?? '1-2 hari' }}"
            },
        @endforeach
    };

    // Quantity controls
    document.getElementById('increase').addEventListener('click', function() {
        const quantityInput = document.getElementById('quantity');
        let value = parseFloat(quantityInput.value) || 1;
        quantityInput.value = (value + 0.5).toFixed(1);
        updateOrderSummary();
    });

    document.getElementById('decrease').addEventListener('click', function() {
        const quantityInput = document.getElementById('quantity');
        let value = parseFloat(quantityInput.value) || 1;
        if (value > 0.5) {
            quantityInput.value = (value - 0.5).toFixed(1);
            updateOrderSummary();
        }
    });

    // Quick weight buttons
    document.querySelectorAll('.quick-weight').forEach(button => {
        button.addEventListener('click', function() {
            const weight = this.getAttribute('data-weight');
            document.getElementById('quantity').value = weight;
            updateOrderSummary();
        });
    });

    // Service selection
    document.querySelectorAll('input[name="service"]').forEach(radio => {
        radio.addEventListener('change', updateOrderSummary);
    });

    // Date and time inputs
    document.querySelector('input[name="pickup_date"]').addEventListener('change', updateOrderSummary);
    document.querySelector('select[name="pickup_time"]').addEventListener('change', updateOrderSummary);

    // Quantity input direct update
    document.getElementById('quantity').addEventListener('input', updateOrderSummary);

    function updateOrderSummary() {
        // Get selected service
        const selectedService = document.querySelector('input[name="service"]:checked');
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const pickupDate = document.querySelector('input[name="pickup_date"]').value;
        const pickupTime = document.querySelector('select[name="pickup_time"]').value;
        
        // Update displays
        if (selectedService) {
            const serviceName = selectedService.value;
            const service = services[serviceName];
            
            // Service display
            document.getElementById('service-display').textContent = serviceName;
            document.getElementById('price-display').textContent = formatCurrency(service.price) + '/' + service.unit;
            
            // Quantity display
            document.getElementById('quantity-display').textContent = quantity;
            
            // Subtotal
            const subtotal = service.price * quantity;
            document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
            
            // Total
            document.getElementById('total-display').textContent = formatCurrency(subtotal);
            
            // Delivery estimation
            if (pickupDate) {
                const date = new Date(pickupDate);
                date.setDate(date.getDate() + 1); // Add 1 day for delivery
                const deliveryDate = date.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                document.getElementById('delivery-display').textContent = deliveryDate;
            }
        } else {
            document.getElementById('service-display').textContent = '-';
            document.getElementById('price-display').textContent = 'Rp 0';
            document.getElementById('quantity-display').textContent = '0';
            document.getElementById('subtotal-display').textContent = 'Rp 0';
            document.getElementById('total-display').textContent = 'Rp 0';
            document.getElementById('delivery-display').textContent = '-';
        }
        
        // Pickup display
        let pickupDisplay = '-';
        if (pickupDate && pickupTime) {
            const date = new Date(pickupDate);
            const formattedDate = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            pickupDisplay = `${formattedDate}, ${pickupTime}`;
        } else if (pickupDate) {
            const date = new Date(pickupDate);
            pickupDisplay = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        document.getElementById('pickup-display').textContent = pickupDisplay;
    }

    function formatCurrency(amount) {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    // Initialize summary on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="pickup_date"]').min = today;
        
        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.querySelector('input[name="pickup_date"]').value = tomorrow.toISOString().split('T')[0];
        
        // Set default time
        document.querySelector('select[name="pickup_time"]').value = '10:00-12:00';
        
        // Auto-select first service
        const firstService = document.querySelector('input[name="service"]');
        if (firstService) {
            firstService.checked = true;
        }
        
        updateOrderSummary();
    });

    // Form validation and submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedService = document.querySelector('input[name="service"]:checked');
        const quantity = document.getElementById('quantity').value;
        const pickupDate = document.querySelector('input[name="pickup_date"]').value;
        const phone = document.querySelector('input[name="phone"]').value;
        const address = document.querySelector('textarea[name="address"]').value;
        
        if (!selectedService || !quantity || !pickupDate || !phone || !address) {
            e.preventDefault();
            alert('Harap lengkapi semua field yang wajib diisi!');
            return;
        }
        
        // Show loading state
        const submitButton = document.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        submitButton.disabled = true;
        
        // Simulate processing
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }, 3000);
    });
</script>
@endsection