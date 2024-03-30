<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0",
 *     title="Test task API Documentation",
 *     description="Test task API Documentation Description",
 *     @OA\Contact(name="Swagger API Team")
 * )
 * @OA\Server(
 *     url="https://task.firscoders.shop/api/",
 *     description="Global API server"
 * )
 * @OA\Server(
 *     url="http://test-task.loc/api/",
 *     description="Local API server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"  
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
