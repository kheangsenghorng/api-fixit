<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Fixit API",
 *     version="1.0.0",
 *     description="Service Marketplace Platform API"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController
{
}
