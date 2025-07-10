<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email'=>'required|string|unique:users',
            'password'=>'required|string',
        ]);

        $user = new User([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if($user->save()){
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Successfully created user!',
                'accessToken'=> $token,
                'userData' =>$user,
            ],201);
        }
        else{
            return response()->json(['error'=>'Provide proper details']);
        }
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email','password']);
        if(!Auth::attempt($credentials))
        {
            return response()->json([
                'message' => 'Unauthorized'
            ],401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'accessToken' =>$token,
            'userData' =>$user,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Get the all User
     *
     * @return [json] user object
     */
    public function fetchAllUsers(Request $request): JsonResponse
    {
        $users = User::filter($request->all())->paginate($request->query('itemsPerPage'))->withQueryString();


        return response()->json(['users' => $users->items(), 'totalUsers' => $users->total()]);
    }

    public function delete(Request $request): JsonResponse
    {
        $user = User::find($request->query('userId'));

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'user not found'], 404);
        }

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'success delete user'], 201);
    }

    /**
     * update  User
     *
     * @return [json] user object
     */
    public function update(Request $request): JsonResponse
    {
        $user = User::find($request->query('userId'));

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ]);
        }

        $user->fullName = $request->input('fullName') ?? $user->fullName;
        $user->username = $request->input('username') ?? $user->username;
        if ($request->input('password')){
            $user->password = bcrypt($request->input('password'));
        }
        $user->role = $request->input('role') ?? $user->role;
        $user->status = $request->input('status') ?? $user->status;

        // Save the changes to the database
        $user->save();

        return response()->json(['message' => $user], 201);

    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);

    }


}
