<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    /**
     * Store a new subscriber.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ], [
            'email.unique' => __('This email is already subscribed.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first('Email'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $subscriber = Subscriber::create([
            'email' => $request->email,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => __('Successfully subscribed to the newsletter!'),
            'data' => $subscriber,
        ]);
    }
}
