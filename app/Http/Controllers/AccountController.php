<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function getUser()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'firstName' => 'required|string|min:3|max:255',
            'lastName' => 'required|string|min:3|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $user->firstName = $request->input('firstName');
        $user->lastName = $request->input('lastName');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Account updated successfully',
            'user' => $user
        ]);
    }
}
