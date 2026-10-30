<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "MercaditoSV API",
    description: "API para gestión de productos, órdenes, pagos y usuarios con autenticación Sanctum y pagos con Stripe"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Servidor local"
)]
#[OA\Server(
    url: "https://mercaditosv-backend.up.railway.app/api",
    description: "Servidor de producción"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token"
)]
abstract class Controller
{
    //
}