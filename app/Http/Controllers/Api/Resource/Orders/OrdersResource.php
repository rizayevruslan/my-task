<?php

namespace App\Http\Controllers\Api\Resource\Orders;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrdersResource extends Controller
{
    /**
     * Orders list.
     *
     * @OA\Get(
     *      path="/v1/orders",
     *      operationId="ordersList",
     *      summary="Orders list",
     *      tags={"Order Routes"},
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

        $orders = DB::table('orders')
            ->join('users', 'orders.client_id', '=', 'users.id')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->join('warehouses', 'orders.warehouse_id', '=', 'warehouses.id')
            ->select('users.id as user_id', 'users.full_name as user_name', 'products.title as product_title', 'warehouses.title as warehouse_title', 'orders.quantity', 'orders.full_amount')
            ->paginate($perpage, ['*'], 'page', $page);

        return ApiResponse::success($orders, 200);
    }

    /**
     * Order add.
     *
     * @OA\Post(
     *      path="/v1/orders",
     *      operationId="OrderCreate",
     *      summary="Order add",
     *      tags={"Order Routes"},  
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

        $orders = DB::table('orders');

        $product = DB::table('products')->where('id', $request->product_id)->first();

        $order = $orders->insertGetId([
            'client_id' => auth()->user()->id,
            'product_id' => $request->product_id,
            'warehouse_id' => $request->warehouse_id,
            'quantity' => $request->quantity,
            'full_amount' => $request->quantity * $product->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ApiResponse::success(['order_id' => $order], "Order added success!", 200);
    }

    /**
     * Order show.
     *
     * @OA\Get(
     *      path="/v1/orders/{order_id}",
     *      operationId="orderShow",
     *      summary="Order show",
     *      tags={"Order Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         description="Order id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Order not found!", @OA\JsonContent()),
     * )
     *
     * @param $order_id
     */
    public function show(int $order_id)
    {
        $order = DB::table('orders')
            ->join('users', 'orders.client_id', '=', 'users.id')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->join('warehouses', 'orders.warehouse_id', '=', 'warehouses.id')
            ->select('users.id as user_id', 'users.full_name as user_name', 'products.title as product_title', 'warehouses.title as warehouse_title', 'orders.quantity', 'orders.full_amount')
            ->where('orders.id', $order_id)->first();

        return ApiResponse::success($order, "Success!", 200);
    }

    /**
     * Order edit info.
     *
     * @OA\Get(
     *      path="/v1/orders/{order_id}/edit",
     *      operationId="orderEdit",
     *      summary="Order edit info",
     *      tags={"Order Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         description="Order id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param $order_id
     */
    public function edit(int $order_id)
    {
        $order = DB::table('orders')
            ->join('users', 'orders.client_id', '=', 'users.id')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->join('warehouses', 'orders.warehouse_id', '=', 'warehouses.id')
            ->select('users.id as user_id', 'users.full_name as user_name', 'products.title as product_title', 'warehouses.title as warehouse_title', 'orders.quantity', 'orders.full_amount')
            ->where('orders.id', $order_id)->first();

        return ApiResponse::success($order, "Order update info!", 200);
    }

    /**
     * Order update.
     *
     * @OA\Put(
     *      path="/v1/orders/{order_id}",
     *      operationId="orderUpdate",
     *      summary="Order update",
     *      tags={"Order Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         description="Order id",
     *         required=true,
     *         example="4",
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
     * @param $order_id
     */
    public function update(Request $request, int $order_id)
    {
        $requestData = $request->all();
        $requestData['id'] = $order_id;

        $validator = Validator::make($requestData, [
            'id' => 'required|exists:orders,id',
            'quantity' => 'nullable|numeric|min:1|max:99999999999',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $updateData = [];
        if ($request->quantity) {
            $product = $orders = DB::table('orders')
                ->join('products', 'orders.product_id', '=', 'products.id')
                ->where('orders.id', $order_id)
                ->first();
            $updateData['full_amount'] = $request->quantity * $product->amount;
            $updateData['quantity'] = $request->quantity;
        }

        if (count($updateData) == 0)
            return ApiResponse::success(['order_id' => $order_id], "There are no changes!", 200);

        $updateData['updated_at'] = now();

        $update = DB::table('orders')->where('id', $order_id)->update($updateData);

        if (!$update)
            return ApiResponse::success(['errors' => ["Order info updated error!"]], "Error!", 200);

        return ApiResponse::success(['order_id' => $order_id], "Order info updated success!", 200);
    }

    /**
     * Order delete.
     *
     * @OA\Delete(
     *      path="/v1/orders/{order_id}",
     *      operationId="orderDelete",
     *      summary="Order delete",
     *      tags={"Order Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         description="Order id",
     *         required=true,
     *         example="3",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *     @OA\Response(response=404, description="Order not found!", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Delete error!", @OA\JsonContent()),
     * )
     *
     * @param $order_id
     */
    public function destroy(int $order_id)
    {
        $orders = DB::table('orders')->where('id', $order_id);

        if ($orders->count() == 0)
            return ApiResponse::error("Order not found!", 404);

        $delete = $orders->delete();

        if (!$delete)
            return ApiResponse::error("Order delete error!", 500);

        return ApiResponse::success(['order_id' => $order_id], "Order deleted success!", 200);
    }
}
