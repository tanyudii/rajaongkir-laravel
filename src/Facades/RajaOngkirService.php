<?php

namespace Tanyudii\RajaOngkirLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getCurrency()
 * @method static array getProvince(...$args)
 * @method static array getCity(...$args)
 * @method static array getSubDistrict(...$args)
 * @method static array getWaybill(string $courier, string $waybill)
 * @method static array checkCost(string $origin, string $originType, string $destination, string $destinationType, int $weight, string $courier, ...$args)
 *
 * @see \Tanyudii\RajaOngkirLaravel\Services\RajaOngkirService
 */
class RajaOngkirService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rajaongkir-laravel';
    }
}
