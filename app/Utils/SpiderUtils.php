<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SpiderUtils
{
    public static function storage($domains, $site)
    {
        $hash = str_replace(['.', 'http://'], ['_', ''], $site) . '_' . date('m_d_H_i_s', time());
        foreach ($domains as $key => $domain) {
            Redis::hset('spider:' . $hash, $domain['domain'], json_encode($domain));
        }
        Redis::rpush('spider:' . $site, $hash);
        Log::info('storage success ' . $hash);
    }

    /**
     * 获取 User-Agent
     * @return mixed
     */
    public static function getUserAgent()
    {
        $user_agents = [
            'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.94 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
            'Mozilla/5.0 (iPad; CPU OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3',
        ];
        return $user_agents[rand(0, count($user_agents) - 1)];
    }

}