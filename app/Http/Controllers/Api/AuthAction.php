<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\LoginResource;
use App\Http\Resources\ProfileResource;
use App\Http\Requests\RegistrationRequest;

class AuthAction extends Controller
{
    use ImageUpload;
    public function registration(RegistrationRequest $request)
    {
        try {

            $user = User::create([
                'name' => ucfirst($request->name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'token' => $token,
                'response_data' => new LoginResource($user),
                'message' => 'Registration Successfully !',
                'status' => 201
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'response_data' => new LoginResource($user),
                    'message' => 'Login Successfully!',
                    'status' => 200
                ]);
            }
            return response()->json(['message' => 'Invalid credentials'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
            return response()->json(['message' => 'Successfully logged out'], 200);
        }
        return response()->json(['message' => 'Invalid token'], 401);
    }



    public function getProfile()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'response_data' => new ProfileResource($user),
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function updateProfile(ProfileRequest $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user->update([
                'name' => ucfirst($request->name),
                'email' => $request->email,
                'dob' => $request->dob,
            ]);

            return response()->json([
                'response_data' => new ProfileResource($user),
                'status' => 201
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function profile_upload_avatar(Request $request)
    {

        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            if (isset($request->image)) {
                $this->deleteOne($user->image);
                $file = $request->image;
                $filename = $this->imageUpload($file, 500, 500, 'uploads/images/User/', true);
                $image = 'uploads/images/User/' . $filename;
            } else {
                $image = $user->image ?? '';
            }

            $user->update(['image' => $image]);

            return response()->json([
                'response_data' => new ProfileResource($user),
                'status' => 201
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
