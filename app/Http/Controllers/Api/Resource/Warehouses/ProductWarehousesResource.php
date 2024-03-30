<?php

namespace App\Http\Controllers\Api\Resource\Warehouses;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductWarehousesResource extends Controller
{
    /**
     * Product warehouses list.
     *
     * @OA\Get(
     *      path="/v1/product-warehouses",
     *      operationId="productWarehousesList",
     *      summary="Product Warehouses list",
     *      tags={"Product Warehouses Routes"},
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

        $productWarehouses = DB::table('product_warehouses')
            ->join('products', 'product_warehouses.product_id', '=', 'products.id')
            ->join('warehouses', 'product_warehouses.warehouse_id', '=', 'warehouses.id')
            ->select('products.title as product_title', 'warehouses.title as warehouse_title', 'product_warehouses.quantity')
            ->paginate($perpage, ['*'], 'page', $page);

        return ApiResponse::success($productWarehouses, 200);
    }

    /**
     * Warehouse add.
     *
     * @OA\Post(
     *      path="/v1/product-warehouses",
     *      operationId="productWarehousesCreate",
     *      summary="Product Warehouse add",
     *      tags={"Product Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Product id",
     *         required=true,
     *         example="3",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         description="Warehouse id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         description="Quantity",
     *         required=true,
     *         example="43",
     *         @OA\Schema(type="integer")
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
            'product_id' => 'required|numeric|exists:products,id',
            'warehouse_id' => 'required|numeric|exists:warehouses,id',
            'quantity' => 'required|numeric|min:1|max:99999999999'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $productWarehouse = DB::table('product_warehouses');

        if ($productWarehouse->where('product_id', $request->product_id)->where('warehouse_id', $request->warehouse_id)->exists()) {
            return ApiResponse::error("Product Warehouse already exists!", 422);
        }

        $warehouse = $productWarehouse->insertGetId([
            'product_id' => $request->product_id,
            'warehouse_id' => $request->warehouse_id,
            'quantity' => $request->quantity,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ApiResponse::success(['product_warehouse_id' => $warehouse], "Product Warehouse added success!", 200);
    }

    /**
     * Product Warehouse show.
     *
     * @OA\Get(
     *      path="/v1/product-warehouses/{product_warehouse_id}",
     *      operationId="productWarehousesshow",
     *      summary="Product Warehouse show",
     *      tags={"Product Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_warehouse_id",
     *         in="path",
     *         description="Product Warehouse id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Product Warehouse not found!", @OA\JsonContent()),
     * )
     *
     * @param $product_warehouse_id
     */
    public function show(int $product_warehouse_id)
    {
        $product_warehouse = DB::table('product_warehouses')
            ->join('products', 'product_warehouses.product_id', '=', 'products.id')
            ->join('warehouses', 'product_warehouses.warehouse_id', '=', 'warehouses.id')
            ->select('products.title as product_title', 'warehouses.title as warehouse_title', 'product_warehouses.quantity')
            ->where('product_warehouses.id', $product_warehouse_id)->first();

        return ApiResponse::success($product_warehouse, "Success!", 200);
    }

    /**
     * Product Warehouse edit info.
     *
     * @OA\Get(
     *      path="/v1/product-warehouses/{product_warehouse_id}/edit",
     *      operationId="productWarehousesEdit",
     *      summary="Product Warehouse edit info",
     *      tags={"Product Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_warehouse_id",
     *         in="path",
     *         description="Product Warehouse id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param $product_warehouse_id
     */
    public function edit(int $product_warehouse_id)
    {
        $product_warehouse = DB::table('product_warehouses')
            ->join('products', 'product_warehouses.product_id', '=', 'products.id')
            ->join('warehouses', 'product_warehouses.warehouse_id', '=', 'warehouses.id')
            ->select('products.title as product_title', 'warehouses.title as warehouse_title', 'product_warehouses.quantity')
            ->where('product_warehouses.id', $product_warehouse_id)->first();

        return ApiResponse::success($product_warehouse, "Product Warehouse update info!", 200);
    }

    /**
     * Product Warehouse update.
     *
     * @OA\Put(
     *      path="/v1/product-warehouses/{product_warehouse_id}",
     *      operationId="productWarehousesUpdate",
     *      summary="Product Warehouse update",
     *      tags={"Product Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_warehouse_id",
     *         in="path",
     *         description="Product Warehouse id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         description="Quantity",
     *         required=true,
     *         example="43",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     * @param $product_warehouse_id
     */
    public function update(Request $request, int $product_warehouse_id)
    {
        $requestData = $request->all();
        $requestData['id'] = $product_warehouse_id;

        $validator = Validator::make($requestData, [
            'id' => 'required|exists:product_warehouses,id',
            'quantity' => 'nullable|numeric|min:1|max:99999999999',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $updateData = [];
        if ($request->quantity)
            $updateData['quantity'] = $request->quantity;

        if (count($updateData) == 0)
            return ApiResponse::success(['warehouse_id' => $product_warehouse_id], "There are no changes!", 200);

        $updateData['updated_at'] = now();

        $update = DB::table('product_warehouses')->where('id', $product_warehouse_id)->update($updateData);

        if (!$update)
            return ApiResponse::success(['errors' => ["Product Warehouse info updated error!"]], "Error!", 200);

        return ApiResponse::success(['warehouse_id' => $product_warehouse_id], "Product Warehouse info updated success!", 200);
    }

    /**
     * Warehouse delete.
     *
     * @OA\Delete(
     *      path="/v1/product-warehouses/{product_warehouse_id}",
     *      operationId="productWarehousesDelete",
     *      summary="Product Warehouse delete",
     *      tags={"Product Warehouses Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_warehouse_id",
     *         in="path",
     *         description="Product Warehouse id",
     *         required=true,
     *         example="3",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *     @OA\Response(response=404, description="Product Warehouse not found!", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Delete error!", @OA\JsonContent()),
     * )
     *
     * @param $product_warehouse_id
     */
    public function destroy(int $product_warehouse_id)
    {
        $product_warehouse = DB::table('product_warehouses')->where('id', $product_warehouse_id);

        if ($product_warehouse->count() == 0)
            return ApiResponse::error("Product Warehouse not found!", 404);

        $delete = $product_warehouse->delete();

        if (!$delete)
            return ApiResponse::error("Product Warehouse delete error!", 500);

        return ApiResponse::success(['product_warehouse_id' => $product_warehouse_id], "Product Warehouse deleted success!", 200);
    }
}
