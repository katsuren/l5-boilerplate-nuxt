<?php

namespace App\Http\Controllers\Api\Auth;

use App\Entities\User;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use JWTAuth;

class VerificationController extends Controller
{
    protected $userRepository;

    public function __construct(User $userRepository)
    {
        $this->userRepository = $userRepository;

        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(Request $request)
    {
        try {
            $user = $this->userRepository->where('email', $request->input('email'))->first();
            if (empty($user)) {
                throw new AuthorizationException;
            }
        } catch (Exception $e) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return $this->responseToken($user);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->responseToken($user);
    }

    protected function responseToken($user)
    {
        $token = JWTAuth::fromUser($user);
        return response()->json(['token' => $token]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = $this->userRepository->where('email', $request->input('email'))->first();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => 'already verified'], 403);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['ok']);
    }
}
