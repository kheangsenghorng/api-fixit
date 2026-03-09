<?php

namespace App;

/**
 * @OA\Info(
 *     title="Fixit API",
 *     version="1.0.0",
 *     description="Service Marketplace Platform API"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Get(
 *     path="/api/ping",
 *     tags={"System"},
 *     summary="Ping API",
 *     @OA\Response(response=200, description="OK")
 * )
 */
class Swagger
{
}
