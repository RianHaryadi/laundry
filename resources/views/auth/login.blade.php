@extends('layouts.app')

@section('title', 'Login - LaundryKu')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center mb-8">Login</h1>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="email@example.com" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="••••••••" required>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="mr-2">
                    <span class="text-gray-700">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold text-lg">Login</button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Belum punya akun? <a href="#" class="text-blue-600 hover:underline">Daftar Sekarang</a>
        </p>
    </div>
</div>
@endsection