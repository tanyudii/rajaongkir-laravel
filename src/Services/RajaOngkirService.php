<?php

namespace Tanyudii\RajaOngkirLaravel\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tanyudii\RajaOngkirLaravel\Exceptions\RajaOngkirException;

class RajaOngkirService
{
    const ACCOUNT_TYPE_PRO = 'pro';
    const ACCOUNT_TYPE_BASIC = 'basic';
    const ACCOUNT_TYPE_STARTER = 'starter';

    const RAJAONGKIR_API_URL = [
        self::ACCOUNT_TYPE_PRO => 'https://pro.rajaongkir.com/api/',
        self::ACCOUNT_TYPE_BASIC => 'https://api.rajaongkir.com/basic/',
        self::ACCOUNT_TYPE_STARTER => 'https://api.rajaongkir.com/starter/',
    ];

    protected $type;

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = Config::get('rajaongkir-laravel.key');
        $this->type = Config::get('rajaongkir-laravel.type', self::ACCOUNT_TYPE_BASIC);
    }

    /**
     * @return string
     * @throws RajaOngkirException
     */
    public function getApiUrl()
    {
        if (!isset(self::RAJAONGKIR_API_URL[$this->type])) {
            throw new RajaOngkirException("RajaOngkir: Account type is invalid.");
        }

        return self::RAJAONGKIR_API_URL[$this->type];
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function isAssoc(array $arr)
    {
        return (array() === $arr) ? false : array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $method
     * @param bool $strictCollection
     * @return array
     * @throws RajaOngkirException
     */
    public function request(string $url, array $params = [], $method = 'GET', $strictCollection = true)
    {
        $method = strtolower($method);
        if (!in_array($method, ['get', 'post'])) {
            throw new RajaOngkirException('RajaOngkir: Http method is invalid.');
        }

        $curl = curl_init();

        if ($method == 'post') {
            curl_setopt($curl, CURLOPT_URL, $this->getApiUrl() . $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            $apiUrl = $this->getApiUrl() . $url;
            if (!empty($params)) {
                $apiUrl .= '?' . http_build_query($params, '');
            }

            curl_setopt($curl, CURLOPT_URL, $apiUrl);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, ['key: ' . $this->getApiKey()]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($curl);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        curl_close($curl);

        if ($contentType == 'application/json') {
            $decodedOutput = json_decode($output, true);
            if (isset($decodedOutput['rajaongkir'])) {
                $data = $decodedOutput['rajaongkir'];
                $status = $data['status'];

                $responseData = [
                    'status' => $status['code']
                ];

                if ($responseData['status'] == 200) {
                    $results = $data['results'] ?? $data['result'];
                    $responseData['data'] = $strictCollection
                        ? $this->isAssoc($results) ? [$results] : $results
                        : $results;
                } else {
                    $responseData['message'] = $status['description'];
                }

                return $responseData;
            }
        }

        throw new RajaOngkirException('RajaOngkir: The response is invalid.');
    }

    /**
     * @return array
     * @throws RajaOngkirException
     */
    public function getCurrency()
    {
        try {
            return $this->request('currency');
        } catch (RajaOngkirException $e) {
            throw $e;
        }
    }

    /**
     * @param mixed ...$args
     * @return array
     * @throws RajaOngkirException
     */
    public function getProvince(...$args)
    {
        try {
            $params = [];

            if (!empty($args)) {
                $params['id'] = $args[0];
            }

            return $this->request('province', $params);
        } catch (RajaOngkirException $e) {
            throw $e;
        }
    }

    /**
     * Arguments length 2 [provinceId, cityId]
     * Arguments length 1 [cityId]
     *
     * @param mixed ...$args
     * @return array
     * @throws RajaOngkirException
     */
    public function getCity(...$args)
    {
        try {
            $params = [];

            if (!empty($args)) {
                $totalArguments = count($args);
                if ($totalArguments == 2) {
                    $params['province'] = $args[0];
                    $params['id'] = $args[1];
                } else {
                    $params['id'] = $args[0];
                }
            }

            return $this->request('city', $params);
        } catch (RajaOngkirException $e) {
            throw $e;
        }
    }

    /**
     * Arguments length 2 [cityId, subDistrictId]
     * Arguments length 1 [cityId]
     *
     * @param mixed ...$args
     * @return array
     * @throws RajaOngkirException
     */
    public function getSubDistrict(...$args)
    {
        try {
            $params = [];

            if (!empty($args)) {
                $params['city'] = $args[0];

                $totalArguments = count($args);
                if ($totalArguments == 2) {
                    $params['id'] = $args[1];
                } else {
                    $params['city'] = $args[0];
                }
            }

            return $this->request('subdistrict', $params);
        } catch (RajaOngkirException $e) {
            throw $e;
        }
    }

    /**
     * @param string $courier
     * @param string $waybill
     * @return array
     * @throws RajaOngkirException
     */
    public function getWaybill(string $courier, string $waybill)
    {
        try {
            return $this->request('waybill', [
                'courier' => $courier,
                'waybill' => $waybill,
            ], 'POST', false);
        } catch (RajaOngkirException $e) {
            throw $e;
        }
    }

    /**
     * Arguments position [leng h, width, height, diameter]
     *
     * @param string $origin
     * @param string $originType
     * @param string $destination
     * @param string $destinationType
     * @param int $weight
     * @param string $courier
     * @param mixed ...$args
     *
     * @return array
     * @throws Exception
     */
    public function checkCost(string $origin,
                            string $originType,
                            string $destination,
                            string $destinationType,
                            int $weight,
                            string $courier,
                            ...$args)
    {
        try {
            $params = [
                'origin' => $origin,
                'originType' => $originType,
                'destination' => $destination,
                'destinationType' => $destinationType,
                'weight' => $weight,
                'courier' => $courier,
            ];

            if (!empty($args)) {
                $keyArguments = ['length', 'width', 'height', 'diameter'];
                foreach ($args as $index => $arg) {
                    if ($index > 3) break;

                    $params[$keyArguments[$index]] = $arg;
                }
            }

            return Cache::remember(json_encode($params), Config::get('rajaongkir-laravel.cache_duration'), function () use ($params) {
                return $this->request('cost', $params, 'POST');
            });
        } catch (Exception $e) {
            throw $e;
        }
    }
}
