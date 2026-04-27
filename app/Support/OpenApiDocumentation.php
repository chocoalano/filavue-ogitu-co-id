<?php

namespace App\Support;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Webstore Customer API",
 *     description="Dokumentasi endpoint API customer untuk autentikasi dan fitur terkait dashboard."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Default API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Bearer Token",
 *     description="Masukkan token dengan format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Customer Auth",
 *     description="Endpoint autentikasi customer API"
 * )
 * @OA\Tag(
 *     name="Home",
 *     description="Endpoint konten beranda"
 * )
 * @OA\Tag(
 *     name="Shop",
 *     description="Endpoint katalog dan detail produk"
 * )
 * @OA\Tag(
 *     name="Articles",
 *     description="Endpoint artikel publik"
 * )
 * @OA\Tag(
 *     name="Pages",
 *     description="Endpoint halaman statis publik"
 * )
 * @OA\Tag(
 *     name="Newsletter",
 *     description="Endpoint berlangganan newsletter"
 * )
 * @OA\Tag(
 *     name="Payments",
 *     description="Endpoint pembayaran dan webhook"
 * )
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Endpoint data dashboard customer"
 * )
 * @OA\Tag(
 *     name="Cart",
 *     description="Endpoint keranjang belanja customer"
 * )
 * @OA\Tag(
 *     name="Wishlist",
 *     description="Endpoint wishlist customer"
 * )
 * @OA\Tag(
 *     name="Checkout",
 *     description="Endpoint checkout dan ongkir"
 * )
 * @OA\Tag(
 *     name="Customer Address",
 *     description="Endpoint manajemen alamat customer"
 * )
 * @OA\Tag(
 *     name="Dashboard Orders",
 *     description="Endpoint order pada dashboard customer"
 * )
 * @OA\Tag(
 *     name="Dashboard Wallet",
 *     description="Endpoint e-wallet pada dashboard customer"
 * )
 * @OA\Tag(
 *     name="Dashboard Account",
 *     description="Endpoint profil akun customer"
 * )
 * @OA\Tag(
 *     name="MLM",
 *     description="Endpoint penempatan member jaringan"
 * )
 * @OA\Tag(
 *     name="Orders",
 *     description="Endpoint invoice order control panel"
 * )
 */
class OpenApiDocumentation {}
