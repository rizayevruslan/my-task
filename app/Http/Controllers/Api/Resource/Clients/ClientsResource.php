<?php

namespace App\Http\Controllers\Api\Resource\Clients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientsResource extends Controller
{
    /**
     * Clients list.
     *
     * @OA\Get(
     *      path="/v1/clients",
     *      operationId="clientsList",
     *      summary="Clients list",
     *      tags={"Clients Routes"},
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         example="1",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="perpage",
     *         in="query",
     *         description="Per page number",
     *         required=false,
     *         example="15",
     *         @OA\Schema(type="string"),
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $page = (!$request->page || !is_numeric($request->page)) ? 1 : $request->page;
        $perpage = (!$request->perpage || !is_numeric($request->perpage)) ? 15 : $request->perpage;

        $users = DB::table('users')->select('full_name', 'birth_date', 'gender', 'phone', 'email')->paginate($perpage, ['*'], 'page', $page);

        return ApiResponse::success($users, 200);
    }

    /**
     * Client create.
     *
     * @OA\Post(
     *      path="/v1/clients",
     *      operationId="clientCreate",
     *      summary="Client create",
     *      tags={"Clients Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="full_name",
     *         in="query",
     *         description="Client full name",
     *         required=true,
     *         example="Your Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="birth_date",
     *         in="query",
     *         description="Birthday date",
     *         required=false,
     *         example="2010-01-01",
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="Gender (0-ayol; 1-erkak)",
     *         required=true,
     *         example="Erkak",
     *         @OA\Schema(
     *           type="string",
     *           enum={"0", "1"} 
     *          ),
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Phone number",
     *         required=true,
     *         example="+998991234567",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email address",
     *         required=false,
     *         example="example@gmail.com",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Password",
     *         required=true,
     *         example="YourePassword",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $requestData = $request->all();
        if (isset($requestData['phone']))
            $requestData['phone'] = str_replace(['+', ' ', '-', '(', ')'], '', $requestData['phone']);

        $validator = Validator::make($requestData, [
            'full_name' => 'required|max:32',
            'birth_date' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'required|in:0,1',
            'phone' => 'required|regex:/^998\d{9}$/|unique:users,phone',
            'email' => 'nullable|email',
            'password' => 'required|min:8|max:32'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $user = DB::table('users')->insertGetId([
            'full_name' => $requestData['full_name'],
            'birth_date' => $requestData['birth_date'] ?? null,
            'gender' => $requestData['gender'],
            'phone' => $requestData['phone'],
            'email' => $requestData['email'] ?? null,
            'password' => bcrypt($requestData['password']),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ApiResponse::success(['user_id' => $user], "Client created success!", 200);
    }

    /**
     * Client show.
     *
     * @OA\Get(
     *      path="/v1/clients/{client_id}",
     *      operationId="clientShow",
     *      summary="Client show",
     *      tags={"Clients Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="client_id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Client not found!", @OA\JsonContent()),
     * )
     *
     * @param $client_id
     */
    public function show(int $client_id)
    {
        $user = DB::table('users')->select('full_name', 'birth_date', 'gender', 'phone', 'email')->where('id', $client_id)->first();

        return ApiResponse::success($user, "Success!", 200);
    }

    /**
     * Client edit info.
     *
     * @OA\Get(
     *      path="/v1/clients/{client_id}/edit",
     *      operationId="clientEdit",
     *      summary="Client edit info",
     *      tags={"Clients Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="client_id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param $client_id
     */
    public function edit(int $client_id)
    {
        $user = DB::table('users')->where('id', $client_id)->first();

        return ApiResponse::success($user, "Client updated success!", 200);
    }

    /**
     * Client update.
     *
     * @OA\Put(
     *      path="/v1/clients/{client_id}",
     *      operationId="clientUpdate",
     *      summary="Client update",
     *      tags={"Clients Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="client_id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="full_name",
     *         in="query",
     *         description="Client full name",
     *         required=false,
     *         example="Your Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="birth_date",
     *         in="query",
     *         description="Birthday date",
     *         required=false,
     *         example="2010-01-01",
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="gender",
     *         in="query",
     *         description="Gender (0-ayol; 1-erkak)",
     *         required=false,
     *         example="Erkak",
     *         @OA\Schema(
     *           type="string",
     *           enum={"0", "1"} 
     *          ),
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Phone number",
     *         required=false,
     *         example="+998991234567",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Email address",
     *         required=false,
     *         example="example@gmail.com",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Password",
     *         required=false,
     *         example="YourePassword",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     * @param $client_id
     */
    public function update(Request $request, int $client_id)
    {
        $requestData = $request->all();
        $requestData['id'] = $client_id;
        if (isset($requestData['phone']))
            $requestData['phone'] = str_replace(['+', ' ', '-', '(', ')'], '', $requestData['phone']);


        $validator = Validator::make($requestData, [
            'id' => 'required|exists:users,id',
            'full_name' => 'nullable|max:32',
            'birth_date' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'nullable|in:0,1',
            'phone' => 'nullable|regex:/^998\d{9}$/|unique:users,phone',
            'email' => 'nullable|email',
            'password' => 'nullable|min:8|max:32'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $updateData = [];
        if ($requestData['full_name'])
            $updateData['full_name'] = $requestData['full_name'];
        if ($requestData['birth_date'])
            $updateData['birth_date'] = $requestData['birth_date'];
        if ($requestData['gender'])
            $updateData['gender'] = $requestData['gender'];
        if ($requestData['phone'])
            $updateData['phone'] = $requestData['phone'];
        if ($requestData['email'])
            $updateData['email'] = $requestData['email'];
        if ($requestData['password'])
            $updateData['password'] = bcrypt($requestData['password']);

        if (count($updateData) == 0)
            return ApiResponse::success(['client_id' => $client_id], "There are no changes!", 200);

        $updateData['updated_at'] = now();

        $update = DB::table('users')->where('id', $client_id)->update($updateData);

        if (!$update)
            return ApiResponse::success(['errors' => ["User updated error!"]], "Error!", 200);

        return ApiResponse::success(['client_id' => $client_id], "Client updated success!", 200);
    }

    /**
     * Client edit info.
     *
     * @OA\Delete(
     *      path="/v1/clients/{client_id}",
     *      operationId="clientDelete",
     *      summary="Client delete",
     *      tags={"Clients Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="client_id",
     *         in="path",
     *         description="Client id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     * 
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *     @OA\Response(response=404, description="Client not found!", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Delete error!", @OA\JsonContent()),
     * )
     *
     * @param $client_id
     */
    public function destroy(int $client_id)
    {
        $user = DB::table('users')->where('id', $client_id);

        if ($user->count() == 0)
            return ApiResponse::error("Client not found!", 404);

        $delete = $user->delete();

        if (!$delete)
            return ApiResponse::error("Client delete error!", 500);

        return ApiResponse::success(['client_id' => $client_id], "Client deleted success!", 200);
    }
}
