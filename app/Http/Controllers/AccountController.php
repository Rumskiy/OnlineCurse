<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\Auth\UpdateUserRequest;
use App\Http\Resources\user\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function getUser()
    {
        $user = Auth::user();

        return $this->sendJsonWhisData($user, UserResource::class);

    }

    public function update(UpdateUserRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->hasFile('avatar_img')) {
            $user->clearMediaCollection('default');
            $user->addMediaFromRequest('avatar_img')->toMediaCollection('default');
        }

        $data = $request->validated();

        // Hash new password if provided, otherwise drop the key
        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $this->sendJsonWhisData($user, UserResource::class);
    }
}
