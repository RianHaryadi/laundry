@extends('layouts.app')

@section('title', 'Booking - LaundryKu')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 mb-6 shadow-lg">
                <i class="fas fa-calendar-alt text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Booking Layanan</h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">Pesan layanan laundry Anda dengan mudah. Tim kami akan menimbang cucian saat pickup.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white">Form Pemesanan</h2>
                        <p class="text-blue-100">Pilih layanan dan jadwal penjemputan</p>
                    </div>
                    
                    <form action="{{ route('booking.store') }}" method="POST" class="p-8">
                        @csrf

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-tshirt mr-2 text-blue-600"></i>Pilih Jenis Layanan
                            </label>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($services as $service)
                                <div class="service-option relative">
                                    <input type="radio" name="service_id" id="service_{{ $service['id'] }}" 
                                           value="{{ $service['id'] }}" class="hidden peer" 
                                           {{ (old('service_id') ?? $selectedServiceId ?? '') == $service['id'] ? 'checked' : '' }} required>
                                    
                                    <label for="service_{{ $service['id'] }}" class="block p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-14 h-14 rounded-lg bg-gradient-to-br {{ $service['color'] }} flex items-center justify-center mr-4">
                                                <i class="{{ $service['icon'] }} text-white text-xl"></i>
                                            </div>
                                            <div class="flex-grow">
                                                <h3 class="font-bold text-gray-800 text-lg">{{ $service['name'] }}</h3>
                                                <p class="text-sm text-gray-600">{{ $service['description'] }}</p>
                                                <div class="mt-2 flex items-center text-sm text-gray-600">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <span>Estimasi: {{ $service['duration'] }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-blue-600 font-bold text-base">{{ $service['formatted_price'] }}</div>
                                                <div class="text-gray-500 text-xs mt-1">{{ $service['pricing_label'] }}</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('service_id')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($customer && $customer->available_coupons >= 6)
                        <div class="mb-8 p-5 bg-amber-50 border-2 border-amber-200 rounded-2xl flex items-center justify-between shadow-sm transform transition hover:scale-[1.01]">
                            <div class="flex items-center">
                                <div class="bg-amber-100 p-3 rounded-full mr-4 text-amber-600">
                                    <i class="fas fa-gift text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-amber-900">Klaim Layanan GRATIS! 🎁</p>
                                    <p class="text-xs text-amber-700">Anda memiliki saldo {{ $customer->available_coupons }} kupon aktif.</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_free_service" id="is_free_service" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                        @endif

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>Pilih Lokasi Outlet Terdekat
                            </label>
                            <select name="outlet_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Pilih Outlet Terdekat</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }} - {{ $outlet->address ?? 'Lokasi' }}</option>
                                @endforeach
                            </select>
                            @error('outlet_id')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-truck mr-2 text-blue-600"></i>Metode Pengiriman
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="delivery-option relative">
                                    <input type="radio" name="delivery_method" id="delivery_walk_in" value="walk_in" class="hidden peer" required>
                                    <label for="delivery_walk_in" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center mr-3">
                                                <i class="fas fa-store text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Walk-in</h4>
                                                <p class="text-xs text-gray-600">Antar sendiri</p>
                                            </div>
                                        </div>
                                        <span class="text-gray-600 font-bold text-sm">Normal</span>
                                    </label>
                                </div>

                                <div class="delivery-option relative">
                                    <input type="radio" name="delivery_method" id="delivery_pickup" value="pickup" class="hidden peer" checked required>
                                    <label for="delivery_pickup" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center mr-3">
                                                <i class="fas fa-truck-pickup text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Pickup</h4>
                                                <p class="text-xs text-gray-600">Dijemput kurir</p>
                                            </div>
                                        </div>
                                        <span class="text-blue-600 font-bold text-sm">+20%</span>
                                    </label>
                                </div>

                                <div class="delivery-option relative">
                                    <input type="radio" name="delivery_method" id="delivery_delivery" value="delivery" class="hidden peer" required>
                                    <label for="delivery_delivery" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center mr-3">
                                                <i class="fas fa-shipping-fast text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Delivery</h4>
                                                <p class="text-xs text-gray-600">Diantar kurir</p>
                                            </div>
                                        </div>
                                        <span class="text-green-600 font-bold text-sm">+20%</span>
                                    </label>
                                </div>

                                <div class="delivery-option relative">
                                    <input type="radio" name="delivery_method" id="delivery_both" value="pickup_delivery" class="hidden peer" required>
                                    <label for="delivery_both" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center mr-3">
                                                <i class="fas fa-exchange-alt text-white"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Both</h4>
                                                <p class="text-xs text-gray-600">Jemput & Antar</p>
                                            </div>
                                        </div>
                                        <span class="text-purple-600 font-bold text-sm">+40%</span>
                                    </label>
                                </div>
                            </div>
                            @error('delivery_method')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>Kecepatan Layanan
                            </label>
                            <div class="space-y-3">
                                <div class="speed-option relative">
                                    <input type="radio" name="service_speed" id="speed_regular" value="regular" class="hidden peer" checked required>
                                    <label for="speed_regular" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center mr-4">
                                                <i class="fas fa-clock text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Regular</h4>
                                                <p class="text-sm text-gray-600">Selesai dalam 2-3 hari</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-green-600 font-bold">Harga Normal</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="speed-option relative">
                                    <input type="radio" name="service_speed" id="speed_express" value="express" class="hidden peer" required>
                                    <label for="speed_express" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center mr-4">
                                                <i class="fas fa-bolt text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Express</h4>
                                                <p class="text-sm text-gray-600">Selesai dalam 1 hari</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-orange-600 font-bold">+50%</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="speed-option relative">
                                    <input type="radio" name="service_speed" id="speed_same_day" value="same_day" class="hidden peer" required>
                                    <label for="speed_same_day" class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-300">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center mr-4">
                                                <i class="fas fa-rocket text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Same Day</h4>
                                                <p class="text-sm text-gray-600">Selesai di hari yang sama</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-red-600 font-bold">+100%</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('service_speed')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 text-xl mr-4 mt-1"></i>
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-2">Informasi Penting</h4>
                                    <p class="text-gray-700 text-sm">
                                        Kurir kami akan menimbang cucian Anda saat pickup. 
                                        Harga final akan dihitung berdasarkan berat/jumlah aktual cucian.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>Jadwal Penjemputan
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-600 mb-2">Tanggal Pickup</label>
                                    <div class="relative">
                                        <input type="date" name="pickup_date" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="{{ date('Y-m-d') }}" required>
                                        <i class="fas fa-calendar absolute right-4 top-4 text-gray-400"></i>
                                    </div>
                                    @error('pickup_date')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-gray-600 mb-2">Waktu Pickup</label>
                                    <select name="pickup_time" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
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

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-user mr-2 text-blue-600"></i>Informasi Kontak
                            </label>
                            
                            @if($customer)
                                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                                    <div class="flex items-center mb-4">
                                        <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                                        <span class="text-green-800 font-semibold">Data terisi otomatis dari akun Anda</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-user-circle text-gray-600 mr-3 w-5"></i>
                                            <span class="text-gray-800 font-medium">{{ $customer->name }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-phone text-gray-600 mr-3 w-5"></i>
                                            <span class="text-gray-800 font-medium">{{ $customer->phone }}</span>
                                        </div>
                                        @if($customer->email)
                                        <div class="flex items-center">
                                            <i class="fas fa-envelope text-gray-600 mr-3 w-5"></i>
                                            <span class="text-gray-800 font-medium">{{ $customer->email }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-gray-600 mb-2">Nama Lengkap</label>
                                        <div class="relative">
                                            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan nama lengkap" required>
                                            <i class="fas fa-user-circle absolute right-4 top-4 text-gray-400"></i>
                                        </div>
                                        @error('name')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-600 mb-2">Nomor Telepon</label>
                                            <div class="relative">
                                                <div class="absolute left-4 top-4 text-gray-500">+62</div>
                                                <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full pl-16 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="812-3456-7890" required>
                                                <i class="fas fa-phone absolute right-4 top-4 text-gray-400"></i>
                                            </div>
                                            @error('phone')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 mb-2">Email (Opsional)</label>
                                            <div class="relative">
                                                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="nama@email.com">
                                                <i class="fas fa-envelope absolute right-4 top-4 text-gray-400"></i>
                                            </div>
                                            @error('email')
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-700">
                                            <i class="fas fa-lightbulb text-blue-600 mr-2"></i>
                                            Sudah punya akun? 
                                            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Login</a> 
                                            untuk booking lebih cepat!
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-4 text-lg">
                                <i class="fas fa-map-marked-alt mr-2 text-blue-600"></i>Alamat Lengkap
                            </label>
                            
                            @if($customer && $customer->address)
                                <div class="space-y-4">
                                    <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                                                <span class="text-green-800 font-semibold">Alamat tersimpan</span>
                                            </div>
                                            <button type="button" onclick="toggleAddressEdit()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                                <i class="fas fa-edit mr-1"></i>Ubah
                                            </button>
                                        </div>
                                        <div class="flex items-start">
                                            <i class="fas fa-map-marker-alt text-gray-600 mr-3 mt-1 w-5"></i>
                                            <p class="text-gray-800" id="saved-address">{{ $customer->address }}</p>
                                        </div>
                                    </div>
                                    
                                    <div id="address-edit-form" style="display: none;">
                                        <div class="relative">
                                            <textarea name="address" rows="4" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan alamat lengkap untuk pickup">{{ old('address', $customer->address) }}</textarea>
                                            <i class="fas fa-map-marker-alt absolute right-4 top-4 text-gray-400"></i>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                Anda bisa menggunakan alamat berbeda untuk pickup ini
                                            </div>
                                            <button type="button" onclick="toggleAddressEdit()" class="text-gray-600 hover:text-gray-700 text-sm">
                                                <i class="fas fa-times mr-1"></i>Batal
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" id="default-address" value="{{ $customer->address }}">
                                </div>
                            @else
                                <div class="relative">
                                    <textarea name="address" rows="4" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan alamat lengkap untuk pickup" required>{{ old('address') }}</textarea>
                                    <i class="fas fa-map-marker-alt absolute right-4 top-4 text-gray-400"></i>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Pastikan alamat lengkap dan jelas untuk mempermudah kurir
                                </div>
                            @endif
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-10">
                            <label class="block text-gray-600 mb-2">
                                <i class="fas fa-sticky-note mr-2"></i>Catatan Khusus (Opsional)
                            </label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Ada noda membandel, pakai pewangi lavender, pakaian bayi terpisah, dll."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 rounded-xl hover:from-blue-700 hover:to-indigo-800 font-bold text-lg transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i> Booking Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-8 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-receipt mr-3 text-blue-600"></i>Ringkasan
                    </h2>
                    
                    <div class="space-y-6 mb-8">
                        <div class="pb-4 border-b border-gray-100">
                            <h3 class="font-medium text-gray-500 mb-2 text-sm uppercase tracking-wider">Layanan Dipilih</h3>
                            <p class="text-gray-800 font-semibold text-lg" id="service-display">Belum dipilih</p>
                            <p class="text-blue-600 font-bold mt-1" id="price-display">-</p>
                        </div>
                        
                        <div class="pb-4 border-b border-gray-100">
                            <h3 class="font-medium text-gray-500 mb-2 text-sm uppercase tracking-wider">Metode & Kecepatan</h3>
                            <p class="text-gray-800 font-medium" id="delivery-method-display">Belum dipilih</p>
                            <p class="text-sm mt-1" id="delivery-method-price">-</p>
                        </div>

                        <div class="pb-4 border-b border-gray-100 hidden" id="reward-summary">
                            <h3 class="font-medium text-green-600 mb-2 text-sm uppercase tracking-wider">Kupon Reward</h3>
                            <p class="text-green-700 font-bold italic">Layanan Gratis Aktif!</p>
                        </div>
                        
                        <div class="pb-4 border-b border-gray-100">
                            <h3 class="font-medium text-gray-500 mb-2 text-sm uppercase tracking-wider">Jadwal Pickup</h3>
                            <p class="text-gray-800 font-medium" id="pickup-display">Belum diatur</p>
                        </div>
                        
                        <div class="pb-4 border-b border-gray-100">
                            <h3 class="font-medium text-gray-500 mb-2 text-sm uppercase tracking-wider">Estimasi Selesai</h3>
                            <p class="text-gray-800 font-medium" id="delivery-display">-</p>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-6">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <i class="fas fa-weight text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Harga Final</h3>
                                <p class="text-gray-600 text-sm">Dihitung saat penimbangan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center text-gray-600 text-sm">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Penimbangan transparan</span>
                        </div>
                        <div class="flex items-center text-gray-600 text-sm">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Garansi kualitas 100%</span>
                        </div>
                        <div class="flex items-center text-gray-600 text-sm">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Lacak status real-time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .service-option input:checked + label,
    .speed-option input:checked + label,
    .delivery-option input:checked + label {
        border-color: #3b82f6;
        background-color: #eff6ff;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1);
    }
    
    .service-option label:hover,
    .speed-option label:hover,
    .delivery-option label:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        cursor: pointer;
    }
</style>

<script>
    // Service data from PHP
    const servicesData = {
        @foreach($services as $service)
            "{{ $service['id'] }}": {
                name: "{{ $service['name'] }}",
                price: "{{ $service['formatted_price'] }}",
                duration: "{{ $service['duration'] }}"
            },
        @endforeach
    };

    // Delivery method data
    const deliveryMethodData = {
        'walk_in': { label: 'Walk-in', percentage: 0, color: 'text-gray-600' },
        'pickup': { label: 'Pickup', percentage: 20, color: 'text-blue-600' },
        'delivery': { label: 'Delivery', percentage: 20, color: 'text-green-600' },
        'pickup_delivery': { label: 'Pickup & Delivery', percentage: 40, color: 'text-purple-600' }
    };

    // Listen to all inputs
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('change', updateSummary);
    });
    
    document.querySelector('select[name="outlet_id"]')?.addEventListener('change', updateSummary);
    document.querySelector('select[name="pickup_time"]')?.addEventListener('change', updateSummary);

    function updateSummary() {
        const selectedService = document.querySelector('input[name="service_id"]:checked');
        const selectedSpeed = document.querySelector('input[name="service_speed"]:checked');
        const selectedDeliveryMethod = document.querySelector('input[name="delivery_method"]:checked');
        const isFreeService = document.getElementById('is_free_service')?.checked;
        const pickupDate = document.querySelector('input[name="pickup_date"]')?.value;
        const pickupTime = document.querySelector('select[name="pickup_time"]')?.value;
        
        // 1. Delivery Summary
        if (selectedDeliveryMethod) {
            const methodValue = selectedDeliveryMethod.value;
            const methodData = deliveryMethodData[methodValue];
            document.getElementById('delivery-method-display').textContent = methodData.label;
            
            const priceElement = document.getElementById('delivery-method-price');
            if (methodData.percentage > 0) {
                priceElement.innerHTML = `<span class="${methodData.color} font-semibold">+${methodData.percentage}%</span> dari harga dasar`;
            } else {
                priceElement.innerHTML = `<span class="${methodData.color} font-semibold">Harga Normal</span>`;
            }
        }
        
        // 2. Service Summary
        if (selectedService) {
            const serviceId = selectedService.value;
            const service = servicesData[serviceId];
            
            let speedLabel = 'Regular';
            let daysToAdd = 2;
            
            if (selectedSpeed) {
                const speedValue = selectedSpeed.value;
                if (speedValue === 'express') {
                    speedLabel = 'Express (+50%)';
                    daysToAdd = 1;
                } else if (speedValue === 'same_day') {
                    speedLabel = 'Same Day (+100%)';
                    daysToAdd = 0;
                }
            }
            
            document.getElementById('service-display').textContent = service.name + ' - ' + speedLabel;
            
            // Handle display if FREE service is toggled
            const priceDisplay = document.getElementById('price-display');
            const rewardSummary = document.getElementById('reward-summary');
            
            if (isFreeService) {
                priceDisplay.innerHTML = `<span class="line-through text-gray-400 font-normal mr-2">${service.price}</span> <span class="text-green-600">Rp 0 (Reward)</span>`;
                rewardSummary.classList.remove('hidden');
            } else {
                priceDisplay.textContent = service.price;
                rewardSummary.classList.add('hidden');
            }
            
            // 3. Estimated Delivery
            if (pickupDate) {
                const date = new Date(pickupDate);
                date.setDate(date.getDate() + daysToAdd);
                const deliveryDate = date.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                });
                document.getElementById('delivery-display').textContent = deliveryDate;
            }
        }
        
        // 4. Pickup display
        let pickupDisplay = 'Belum diatur';
        if (pickupDate && pickupTime) {
            const date = new Date(pickupDate);
            const formattedDate = date.toLocaleDateString('id-ID', {
                weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
            });
            pickupDisplay = `${formattedDate}, ${pickupTime}`;
        }
        document.getElementById('pickup-display').textContent = pickupDisplay;
    }

    function toggleAddressEdit() {
        const savedAddressSection = document.querySelector('.bg-green-50');
        const editForm = document.getElementById('address-edit-form');
        const textarea = document.querySelector('textarea[name="address"]');
        const defaultAddress = document.getElementById('default-address');
        
        if (editForm.style.display === 'none') {
            savedAddressSection.style.display = 'none';
            editForm.style.display = 'block';
            textarea.focus();
        } else {
            savedAddressSection.style.display = 'block';
            editForm.style.display = 'none';
            if (defaultAddress) textarea.value = defaultAddress.value;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Init Date
        const dateInput = document.querySelector('input[name="pickup_date"]');
        if (dateInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.value = tomorrow.toISOString().split('T')[0];
        }
        
        // Init Time
        const timeSelect = document.querySelector('select[name="pickup_time"]');
        if (timeSelect) timeSelect.value = '10:00-12:00';
        
        // UPDATE: Jangan paksa pilih service pertama jika sudah ada yang ter-checked (Auto-Select URL)
        const currentCheckedService = document.querySelector('input[name="service_id"]:checked');
        if (!currentCheckedService) {
            const firstService = document.querySelector('input[name="service_id"]');
            if (firstService) firstService.checked = true;
        }
        
        updateSummary();
    });
</script>
@endsection