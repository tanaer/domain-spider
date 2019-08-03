<?php

namespace App\Library\Spider\Drivers;

use App\Utils\SpiderUtils;

class Aiming extends BaseDriver
{
    protected $detail_url = 'https://am.22.cn%s';

    public function crawl($url, $category, $page)
    {
        $category['pageIndex'] = $page;
        $url .= '?' . http_build_query($category);
        $headers = [
            'host' => 'am.22.cn',
            'origin' => 'https://am.22.cn',
            'referer' => 'https://am.22.cn/ykj/',
            'user-agent' => SpiderUtils::getUserAgent(),
        ];
        $proxy = null;
        $query = $category;
        $options = compact('headers', 'query', 'proxy');
        $sleep = rand(3, 5);
        $data = $this->crawlByGuzzleHttp($url, $options, $sleep);
        return $data;
    }


    public function parse($data)
    {
        $result = [];
        //爱名网分页200条，数据为2个数组
        if (isset($data['data'][0]) && isset($data['data'][1]) && count($data['data'][0]) == 100) {
            $domains = array_merge($data['data'][0], $data['data'][1]);
        } else {
            $domains = $data['data'];
        }
        foreach ($domains as $domain) {
            $item = [];
            $price = floatval(str_replace(["￥", ","], "", $domain['avgPrice']));
            $item['domain'] = $domain['Domain'];
            $item['introduction'] = $domain['Introduce'];
            $item['price'] = $price;
            $item['url'] = sprintf($this->detail_url, $domain['Url']);
            array_push($result, $item);
        }
        return $result;
    }
}