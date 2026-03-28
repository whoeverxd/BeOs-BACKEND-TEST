<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
	title: 'BeOs Backend Test API',
	version: '1.0.0',
	description: 'Documentacion de la API para gestion de productos, divisas y precios.'
)]
#[OA\Server(
	url: 'http://localhost:8000',
	description: 'Servidor local'
)]
class OpenApi
{
}
