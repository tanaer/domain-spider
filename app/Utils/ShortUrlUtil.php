<?php

namespace App\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShortUrlUtil
{
    private static $sina_api_url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=%s%s';

    public static function getShortUrl($long_urls)
    {
        $result = [];
        $long_urls = is_array($long_urls) ? $long_urls : array($long_urls);
        $chunk = array_chunk($long_urls, 20);
        foreach ($chunk as $item) {
            $short_urls = self::getSinaShortUrl($item);
            if ($short_urls) {
                $result = array_merge($result, $short_urls);
            }
        }
        return $result;
    }

    private static function getSinaShortUrl($long_urls)
    {
        $result = [];
        try {
            $source = config('short-url.sina_app_key');
            //参数拼装
            $url_param = array_map(function ($value) {
                return '&url_long=' . urlencode($value);
            }, $long_urls);
            $url_param = implode('', $url_param);

            //新浪短链接接口
            $request_url = sprintf(self::$sina_api_url, $source, $url_param);
            $client = new Client();
            $response = $client->request('GET', $request_url, [
                'connect_timeout' => 2,
                'timeout' => 2,
            ]);
            $data = $response->getBody()->getContents();
            $result = json_decode($data, true);
        } catch (\Exception $exception) {
            Log::error('新浪短链接生成失败：' . $exception->getMessage());
        }
        return $result;
    }

}