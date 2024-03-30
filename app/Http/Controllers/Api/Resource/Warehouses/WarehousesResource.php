<?php

namespace App\Http\Controllers\Api\Resource\Warehouses;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehousesResource extends Controller
{
    /**
     * Warehouses list.
     *
     * @OA\Get(
     *      path="/v1/warehouses",
     *      operationId="warehousesList",
     *      summary="Warehouses list",
     *      tags={"Warehouses Routes"},
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
     *         @OA\Schema(type="string")
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

        $warehouses = DB::table('warehouses')->select('title', DB::raw("(CASE WHEN is_active = 1 THEN 'active' ELSE 'passive' END) as status"))->paginate($perpage, ['*'], 'page', $page);

        return ApiResponse::success($warehouses, 200);
    }

    /**
     * Warehouse add.
     *
     * @OA\Post(
     *      path="/v1/warehouses",
     *      operationId="warehouseCreate",
     *      summary="Warehouse add",
     *      tags={"Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Warehouse name",
     *         required=true,
     *         example="Warehouse Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Status (0-passive; 1-active)",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *           type="integer",
     *           enum={"0", "1"} 
     *          ),
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'is_active' => 'required|numeric|in:0,1'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $warehouse = DB::table('warehouses')->insertGetId([
            'title' => $request->title,
            'is_active' => $request->is_active,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ApiResponse::success(['warehouse_id' => $warehouse], "Warehouse added success!", 200);
    }

    /**
     * Warehouse show.
     *
     * @OA\Get(
     *      path="/v1/warehouses/{warehouse_id}",
     *      operationId="warehouseshow",
     *      summary="Warehouse show",
     *      tags={"Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="warehouse_id",
     *         in="path",
     *         description="Warehouse id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Warehouse not found!", @OA\JsonContent()),
     * )
     *
     * @param $warehouse_id
     */
    public function show(int $warehouse_id)
    {
        $warehouse = DB::table('warehouses')->where('id', $warehouse_id)->first();

        return ApiResponse::success($warehouse, "Success!", 200);
    }

    /**
     * Warehouse edit info.
     *
     * @OA\Get(
     *      path="/v1/warehouses/{warehouse_id}/edit",
     *      operationId="warehouseEdit",
     *      summary="Warehouse edit info",
     *      tags={"Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="warehouse_id",
     *         in="path",
     *         description="Warehouse id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param $warehouse_id
     */
    public function edit(int $warehouse_id)
    {
        $warehouse = DB::table('warehouses')->where('id', $warehouse_id)->first();

        return ApiResponse::success($warehouse, "Warehouse update info!", 200);
    }

    /**
     * Warehouse update.
     *
     * @OA\Put(
     *      path="/v1/warehouses/{warehouse_id}",
     *      operationId="warehouseUpdate",
     *      summary="Warehouse update",
     *      tags={"Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="warehouse_id",
     *         in="path",
     *         description="Warehouse id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Warehouse title",
     *         required=false,
     *         example="New Product Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Status (0-passive; 1-active)",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *           type="integer",
     *           enum={"0", "1"} 
     *          ),
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     * @param $warehouse_id
     */
    public function update(Request $request, int $warehouse_id)
    {
        $requestData = $request->all();
        $requestData['id'] = $warehouse_id;

        $validator = Validator::make($requestData, [
            'id' => 'required|exists:warehouses,id',
            'title' => 'nullable|max:255',
            'is_active' => 'nullable|numeric|in:0,1',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $updateData = [];
        if ($request->title)
            $updateData['title'] = $request->title;
        if ($request->is_active)
            $updateData['is_active'] = $request->is_active;

        if (count($updateData) == 0)
            return ApiResponse::success(['warehouse_id' => $warehouse_id], "There are no changes!", 200);

        $updateData['updated_at'] = now();

        $update = DB::table('warehouses')->where('id', $warehouse_id)->update($updateData);

        if (!$update)
            return ApiResponse::success(['errors' => ["Warehouse info updated error!"]], "Error!", 200);

        return ApiResponse::success(['warehouse_id' => $warehouse_id], "Warehouse info updated success!", 200);
    }

    /**
     * Warehouse delete.
     *
     * @OA\Delete(
     *      path="/v1/warehouses/{warehouse_id}",
     *      operationId="warehouseDelete",
     *      summary="Warehouse delete",
     *      tags={"Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="warehouse_id",
     *         in="path",
     *         description="Warehouse id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *     @OA\Response(response=404, description="Warehouse not found!", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Delete error!", @OA\JsonContent()),
     * )
     *
     * @param $warehouse_id
     */
    public function destroy(int $warehouse_id)
    {
        $warehouse = DB::table('warehouses')->where('id', $warehouse_id);

        if ($warehouse->count() == 0)
            return ApiResponse::error("Warehouse not found!", 404);

        $delete = $warehouse->delete();

        if (!$delete)
            return ApiResponse::error("Warehouse delete error!", 500);

        return ApiResponse::success(['warehouse_id' => $warehouse_id], "Warehouse deleted success!", 200);
    }
}
