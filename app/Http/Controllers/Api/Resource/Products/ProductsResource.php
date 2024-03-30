<?php

namespace App\Http\Controllers\Api\Resource\Products;

use App\Http\Controllers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductsResource extends Controller
{
    /**
     * Products list.
     *
     * @OA\Get(
     *      path="/v1/products",
     *      operationId="productsList",
     *      summary="Products list",
     *      tags={"Products Routes"},
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

        $products = DB::table('products')->select('title', 'amount')->paginate($perpage, ['*'], 'page', $page);

        return ApiResponse::success($products, 200);
    }

    /**
     * Product add.
     *
     * @OA\Post(
     *      path="/v1/products",
     *      operationId="productCreate",
     *      summary="Product add",
     *      tags={"Products Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Product name",
     *         required=true,
     *         example="Product Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="Product price",
     *         required=true,
     *         example="130000",
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
        if (isset($request->amount))
            $request->amount = str_replace(['.', ' ', ','], '', $request->amount);

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $product = DB::table('products')->insertGetId([
            'title' => $request->title,
            'amount' => $request->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ApiResponse::success(['product_id' => $product], "Product added success!", 200);
    }

    /**
     * Product show.
     *
     * @OA\Get(
     *      path="/v1/products/{product_id}",
     *      operationId="productShow",
     *      summary="Product show",
     *      tags={"Products Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Product not found!", @OA\JsonContent()),
     * )
     *
     * @param $product_id
     */
    public function show(int $product_id)
    {
        $product = DB::table('products')->where('id', $product_id)->first();

        return ApiResponse::success($product, "Success!", 200);
    }

    /**
     * Product edit info.
     *
     * @OA\Get(
     *      path="/v1/products/{product_id}/edit",
     *      operationId="productEdit",
     *      summary="Product edit info",
     *      tags={"Products Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product id",
     *         required=true,
     *         example="2",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     * )
     *
     * @param $product_id
     */
    public function edit(int $product_id)
    {
        $product = DB::table('products')->where('id', $product_id)->first();

        return ApiResponse::success($product, "Product update info!", 200);
    }

    /**
     * Product update.
     *
     * @OA\Put(
     *      path="/v1/products/{product_id}",
     *      operationId="productUpdate",
     *      summary="Product update",
     *      tags={"Products Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Product title",
     *         required=false,
     *         example="New Product Name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="New Product price",
     *         required=false,
     *         example="132000",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Validation error!", @OA\JsonContent()),
     * )
     *
     * @param Request $request
     * @param $product_id
     */
    public function update(Request $request, int $product_id)
    {
        $requestData = $request->all();
        $requestData['id'] = $product_id;

        $validator = Validator::make($requestData, [
            'id' => 'required|exists:products,id',
            'title' => 'nullable|max:255',
            'amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Validation error!", 422, $validator->errors());
        }

        $updateData = [];
        if ($request->title)
            $updateData['title'] = $request->title;
        if ($request->amount)
            $updateData['amount'] = $request->amount;

        if (count($updateData) == 0)
            return ApiResponse::success(['product_id' => $product_id], "There are no changes!", 200);

        $updateData['updated_at'] = now();

        $update = DB::table('products')->where('id', $product_id)->update($updateData);

        if (!$update)
            return ApiResponse::success(['errors' => ["Product info updated error!"]], "Error!", 200);

        return ApiResponse::success(['product_id' => $product_id], "Product info updated success!", 200);
    }

    /**
     * Product edit info.
     *
     * @OA\Delete(
     *      path="/v1/products/{product_id}",
     *      operationId="productDelete",
     *      summary="Product delete",
     *      tags={"Products Routes"},  
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product id",
     *         required=true,
     *         example="143",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success!", @OA\JsonContent()),
     *     @OA\Response(response=404, description="Product not found!", @OA\JsonContent()),
     *     @OA\Response(response=500, description="Delete error!", @OA\JsonContent()),
     * )
     *
     * @param $product_id
     */
    public function destroy(int $product_id)
    {
        $product = DB::table('products')->where('id', $product_id);

        if ($product->count() == 0)
            return ApiResponse::error("Product not found!", 404);

        $delete = $product->delete();

        if (!$delete)
            return ApiResponse::error("Product delete error!", 500);

        return ApiResponse::success(['product_id' => $product_id], "Product deleted success!", 200);
    }
}
