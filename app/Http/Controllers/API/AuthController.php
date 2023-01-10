<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential' => 'required',
            'password' => 'required',
            'remember' => 'boolean'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Error validation', $validator->errors(), 403);
        }
        $user = User::query()->where('email', $request->credential)->orWhere(
            'user',
            $request->credential
        )->first();
        if ($user) {
            $remember = $request->has('remember') ?? false;
            $pwIsCorrect = Hash::check($request->password, $user->password);
            if ($pwIsCorrect) {
                Auth::login($user, $remember);
                $success['token_type'] = 'bearer';
                $success['token'] =  $user->createToken('MyAuthApp')->plainTextToken;
                $success['token_expiration_time'] =  Carbon::now('America/Recife')->addMinutes(config('sanctum.expiration', 0))->getTimestampMs();
                $success['name'] =  $user->name;
                $success['timezone'] = Carbon::now('America/Recife')->timezone->getName();
                return $this->sendResponse($success, 'User signed in');
            }
            return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 401);
        }
        return $this->sendError('Unauthorized.', ['error' => 'Unauthorized'], 401);
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Error validation', $validator->errors());
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        return $this->sendResponse($user, 'User created successfully.');
    }
}
