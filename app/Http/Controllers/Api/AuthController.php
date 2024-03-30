<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * User login api.
     *
     * @OA\Post(
     *      path="/v1/login",
     *      operationId="authLogin",
     *      summary="User login",
     *      tags={"Authentication"},
     *      @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="User's phone number",
     *         required=true,
     *         example="+998912223344",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="User's password",
     *         required=true,
     *         example="user12345",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(response=200, description="Success!"),
     *      @OA\Response(response=422, description="Validation error!"),
     *      @OA\Response(response=401, description="Unauthorized!")
     * )
     *
     * @param Request $request
     */
    public function login(Request $request)
    {
        if ($request->phone)
            $request->phone = str_replace([" ", "(", ")", "-", "+"], "", $request->phone);

        $validator = Validator::make($request->only('phone', 'password'), [
            'phone' => 'required|numeric',
            'password' => 'required|min:8|max:20'
        ]);

        if ($validator->fails())
            return ApiResponse::error($validator->errors(), 422);

        $attemp = Auth::attempt(['phone' => $request->phone, 'password' => $request->password]);

        if ($attemp) {
            $user = Auth::user();

            $user->makeHidden(['created_at', 'updated_at', 'email_verified_at']);

            $data['user'] = $user;

            $user->tokens()->delete();

            $data['token'] = $user->createToken('authToken')->plainTextToken;

            return ApiResponse::success($data, 200);
        }
        return ApiResponse::error("Unauthorized!", 401);
    }


    /**
     * User logout.
     *
     * @OA\Post(
     *      path="/v1/logout",
     *      operationId="userLogout",
     *      summary="User logout",
     *      tags={"Authentication"},
     *      security={{ "bearerAuth": {} }},
     *      @OA\Response(response=200, description="Success!"),
     * )
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success('From Profel was successfully released.', 200);
    }
}
