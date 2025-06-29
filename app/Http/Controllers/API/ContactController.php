<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'message' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contactData = [
                'fullName' => $request->fullName,
                'email' => $request->email,
                'message' => $request->message,
                'submitted_at' => now()
            ];

            // Send email to business
            Mail::to(config('mail.from.address'))->send(new ContactFormMail($contactData));

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully! We will get back to you soon.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again later.'
            ], 500);
        }
    }
}