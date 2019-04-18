<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyUpdateEmailNotification;
use Auth;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Validator;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify');
    }

    public function index()
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $attributes = $request->input('user');
        $email = Arr::pull($attributes, 'email');
        $validator = Validator::make($attributes, [
            'name' => 'sometimes|filled|string|min:2|max:100',
            'password' => 'sometimes|filled|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'result' => 'error',
                'message' => $validator->errors(),
            ]);
        }
        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $user = Auth::user();
        $user->fill($attributes);
        $user->save();

        $messages = [];
        if (! empty($email)) {
            $validator = Validator::make(['email' => $email], [
                'email' => 'email|unique:users,email,' . $user->id,
            ]);
            if (! $validator->fails()) {
                $notifiable = new AnonymousNotifiable;
                $notifiable->route('mail', $email);
                $notifiable->notify(new VerifyUpdateEmailNotification);
                $messages[] = 'sent email to verify';
            } else {
                $messages = $validator->errors();
            }
        }

        return response()->json([
            'result' => 'ok',
            'message' => $messages,
        ]);
    }

    public function verify(Request $request, $email)
    {
        $user = Auth::user();
        $user->fill(['email' => $email]);
        $user->save();

        return response()->json([
            'result' => 'ok',
        ]);
    }
}
