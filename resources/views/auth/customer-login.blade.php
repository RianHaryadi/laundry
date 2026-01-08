@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-8 px-4">
    <div class="max-w-md w-full space-y-8">
        <!-- Header Section -->
        <div class="text-center">
            <!-- Logo -->
            <div class="mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                    <i class="fas fa-tshirt text-white text-3xl"></i>
                </div>
            </div>
            
            <!-- Title -->
            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
                <p class="text-gray-600">Sign in to your laundry account</p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p class="text-red-700 text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf

                <!-- Email/Phone Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email or Phone Number
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            name="login" 
                            value="{{ old('login') }}" 
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="your@email.com or 081234567890"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Enter your password"
                            required
                        >
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Keep me signed in
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In to Account
                </button>
            </form>

        </div>

        <!-- Guest Option -->
        <div class="text-center">
            <a 
                href="{{ route('home') }}" 
                class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Continue browsing as guest
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePassword() {
    const passwordField = document.querySelector('input[name="password"]');
    const icon = document.querySelector('button[onclick="togglePassword()"] i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Auto-focus on login field if empty
document.addEventListener('DOMContentLoaded', function() {
    const loginField = document.querySelector('input[name="login"]');
    if (!loginField.value) {
        loginField.focus();
    }
});
</script>

<style>
/* Custom focus styles */
input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Smooth transitions */
* {
    transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Hover effects */
button:hover, a:hover {
    transform: translateY(-1px);
}

/* Placeholder styling */
input::placeholder {
    color: #9CA3AF;
    font-size: 14px;
}
</style>
@endsection