<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Get all user successfully',
            'users'   => $users,
        ]);
        // return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|string|in:user,recruiter',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Successfully create account',
            'data'    => $user,
            'token'   => $token,
        ]);
    }

    public function login(Request $request)
    {
         $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $jwtGuard = Auth::guard('api'); // always use JWT guard

        if (!$token = $jwtGuard->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, $jwtGuard, $credentials);
    }


    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'User get successfully',
            'user'    => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'nullable|string|in:user,recruiter',
        ]);

        $user = User::findOrFail($id);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'success' =>true,
            'message' => 'User update successfully',
            'user'    => $user,
            'token'   => $token,
        ]);
    }
    public function destroy($id)
    {
           $user = User::findOrFail($id);
           $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
    }
    
    public function logout()
    {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully.'
            ]);
    }

    protected function respondWithToken($token, $jwtGuard, $credentials)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 180,
            'jwt_gurd'     => $jwtGuard,
            'user'         =>$credentials
        ]);
    }
}