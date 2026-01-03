<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;

class CustomerAuthController extends Controller
{
    /**
     * Show customer login form
     */
    public function showLoginForm()
    {
        return view('auth.customer-login');
    }

    /**
     * Handle customer login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', // bisa email atau phone
            'password' => 'required|string',
        ]);

        // Try to find customer by email or phone
        $customer = Customer::where('email', $credentials['login'])
            ->orWhere('phone', $credentials['login'])
            ->first();

        // Debug: Log the customer search
        \Log::info('Login attempt for: ' . $credentials['login']);
        \Log::info('Customer found: ' . ($customer ? 'YES (ID: '.$customer->id.')' : 'NO'));
        
        // Check if customer exists
        if (!$customer) {
            return back()->withErrors([
                'login' => 'Account not found. Please check your email/phone.',
            ])->withInput($request->except('password'));
        }

        // Check if customer has password
        if (!$customer->password) {
            return back()->withErrors([
                'login' => 'This account does not have web access. Please contact support.',
            ])->withInput($request->except('password'));
        }

        // Debug: Log password check
        $passwordMatch = Hash::check($credentials['password'], $customer->password);
        \Log::info('Password match: ' . ($passwordMatch ? 'YES' : 'NO'));
        
        // Verify password
        if (!$passwordMatch) {
            return back()->withErrors([
                'login' => 'Incorrect password. Please try again.',
            ])->withInput($request->except('password'));
        }

        // Log the customer in using 'customer' guard
        Auth::guard('customer')->login($customer, $request->boolean('remember'));

        $request->session()->regenerate();

        \Log::info('Customer logged in successfully: ' . $customer->id);

        return redirect()->intended(route('home'));
    }

    /**
     * Show customer registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.customer-register');
    }

    /**
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:255|unique:customers,phone',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
        ]);

        // Create customer - check which columns exist
        $customerData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'], 
        ];
        
        // Add email if provided
        if (!empty($validated['email'])) {
            $customerData['email'] = $validated['email'];
        }
        
        // Add address if exists in table and provided
        if (Schema::hasColumn('customers', 'address') && !empty($validated['address'])) {
            $customerData['address'] = $validated['address'];
        }
        
        // Add membership fields if they exist
        if (Schema::hasColumn('customers', 'is_member')) {
            $customerData['is_member'] = true;
        }
        if (Schema::hasColumn('customers', 'member_since')) {
            $customerData['member_since'] = now();
        }
        if (Schema::hasColumn('customers', 'email_notifications')) {
            $customerData['email_notifications'] = true;
        }
        if (Schema::hasColumn('customers', 'sms_notifications')) {
            $customerData['sms_notifications'] = true;
        }

        $customer = Customer::create($customerData);

        // Debug log
        \Log::info('Customer registered: ' . $customer->id . ' - ' . $customer->email);

        // Auto login after registration
        Auth::guard('customer')->login($customer);

        return redirect()->route('home')
            ->with('success', 'Registration successful! Welcome to our platform.');
    }

    /**
     * Handle customer logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.customer-forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
        ]);

        // TODO: Implement password reset email logic
        // You can use Laravel's built-in password reset or create custom logic

        return back()->with('success', 'Password reset link has been sent to your email.');
    }
}