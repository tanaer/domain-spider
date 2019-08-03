<?php

namespace App\Utils;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProxyUtil
{

    /**
     * 获取代理
     * @return bool|string
     */
    public static function getProxy()
    {
        if (!$proxy = Redis::get('proxy')) {
            if (!$proxy = self::getProxyFromPool()) {
                $proxy = self::getProxyFromDaXiang();
            }
        }
        return $proxy;
    }

    /**
     * 获取代理池代理
     * @return bool|string
     */
    public static function getProxyFromPool()
    {
        if (!$proxy_pool_host = config('tool.proxy_pool_host')) {
            return false;
        } else {
            try {
                $url = $proxy_pool_host . 'api/proxies/premium';
                $client = new Client();
                $response = $client->request('GET', $url, [
                    'connect_timeout' => 3,
                    'timeout' => 3,
                ]);
                $data = json_decode($response->getBody()->getContents(), true);
                $proxy = $data['data']['protocol'] . '://' . $data['data']['ip'] . ':' . $data['data']['port'];
                Redis::setex('proxy', 60 * 30, trim($proxy));
                Log::info('获取代理池代理：' . $proxy);
                return $proxy;
            } catch (\Exception $exception) {
                Log::info("获取代理失败：" . $exception->getMessage());
            }
        }
        return false;
    }

    /**
     * 获取大象代理
     * @return null
     */
    public static function getProxyFromDaXiang()
    {
        $proxy = null;
        $import_url = config('tool.proxy_daxiang_host');
        if ($import_url) {
            $data = file_get_contents($import_url);
            $proxies = array_values(explode("\n", $data));
            Log::info('获取大象代理', $proxies);
            foreach ($proxies as $proxy) {
                Redis::rpush('proxy_list', trim($proxy));
            }
            $proxy = Redis::lpop('proxy_list');
            Redis::setex('proxy', 60 * 30, trim($proxy));
        }
        return $proxy;
    }
}