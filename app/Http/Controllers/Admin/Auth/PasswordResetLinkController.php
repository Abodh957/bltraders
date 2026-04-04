<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User;
use DB;
use Carbon\Carbon;
use Mail;
use Hash;
use Illuminate\Support\Str;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('admin.auth.layouts.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Retrieve the email from the request
        $email = $request->input('email');

        try {
            // Delete the corresponding password reset token if it exists
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            // Generate a new token
            $token = Str::random(64);

            // Insert the new token into the database
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            // Prepare data for the email
            $data = [
                'token' => $token,
                'subject' => 'Forgot password',
                'route' => 'route',
                'buttonName' => 'Your Password Reset Link',
                'message' => 'We have received a password change request for your Evernote account',
                'url' => route('password.reset', ['token' => $token]),
            ];

            // Send the email
            Mail::to($email)->send(new SendMail($data));

            // Return success response
            return back()->with('success', 'We have sent an email with the password reset link!');

        } catch (Exception $e) {
            // Log the exception (optional)
            \Log::error('Error in password reset process: ' . $e->getMessage());

            // Return an error response
            return back()->withErrors(['email' => 'There was an error processing your request. Please try again.']);
        }
    }
}
