<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function get_all_subscriptions()
    {
        try {
            return response()->json([
                'message' => 'Successfully Fetched Subscriptions',
                'data' => Subscription::all(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to fetch Subscriptions',
            ], 400);
        }
    }
    
    public function create_subscription(Request $request)
    {
        try {
            $subscription = Subscription::create($request->all());
            return response()->json([
                'message' => 'Successfully Created Subscription',
                'data' => $subscription,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create Subscription',
            ], 400);
        }
    }
    
    public function update_subscription(Request $request)
    {
        try {
            $subscription = Subscription::findOrFail($request->id);
            $subscription->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
            ]);
            return response()->json([
                'message' => 'Successfully Updated Subscription'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update Subscription',
            ], 400);
        }
    }
}
