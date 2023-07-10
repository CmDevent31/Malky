<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\CommentResource;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
//     public function login(Request $Request)
//     {
//         $Request->validate([
//             'email' => 'required|email',
//             'password' => 'required',
//         ]);

//         $header = $Request->header('app-token');

//         if ($header == 'appmalki') { // APP TOKEN
//             $user = User::where('email', $Request->email)->first();
//             if (! $user || ! Hash::check($Request->password, $user->password)){
//                 throw ValidationException::withMessages([
//                     'email' => ['The provided credentials are incorrect.'],
//                 ]);
//             }
//             $token = $user->createToken('Customer');
//             // return $token->toArray();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Berhasil Login Akun!',
//                 'data' => $token->toArray(),
//             ], 200);
//         } else {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Gagal Login Akun! App Token Salah!',
//                 'data' => (object)[]
//             ], 401);
//         }

        
//     }

//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()->delete();
//         return response()->json([
//             'success' => true,
//             'message' => 'Berhasi Logout!',
//             'data' => (object)[],
//         ], 200);
//     }

//     public function register(Request $request)
// {
//     $validated = $request->validate([
//         'email' => 'required|email',
//         'username' => 'required',
//         'password' => 'required',
//         'firstname' => 'required',
//         'lastname' => 'required',
//     ]);

//     $user = User::create([
//         'email' => $request->email,
//         'username' => $request->username,
//         'password' => Hash::make($request->password),
//         'firstname' => $request->firstname,
//         'lastname' => $request->lastname,
//     ]);

//     if ($user) {
//         return response()->json([
//             'success' => true,
//             'message' => 'Berhasil Membuat Akun!',
//             'data' => $user,
//         ], 201);
//     } else {
//         return response()->json([
//             'success' => false,
//             'message' => 'Gagal Membuat Akun!',
//         ], 401);
//     }
// }

    
//     public function me(Request $request)
//     {
//         return response()->json(Auth::user());
//     }






  /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'username' => 'required',
            'password' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
        ]);
        if($validator->fails()){
        return response()->json($validator->messages());
        }
          $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
    ]);
    if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil Membuat Akun!',
                    'data' => $user,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Membuat Akun!',
                ], 401);
            }

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
