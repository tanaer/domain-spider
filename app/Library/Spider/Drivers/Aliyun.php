<?php

namespace App\Library\Spider\Drivers;


use App\Utils\SpiderUtils;

class Aliyun extends BaseDriver
{
    protected $detail_url = 'https://mi.aliyun.com/detail/online.html?domainName=%s&productType=%s';

    public function crawl($url, $category, $page)
    {
        $token = $this->getToken();
        $category['currentPage'] = $page;
        $category['token'] = "tdomain-aliyun-com:{$token}";
        $url .= '?' . http_build_query($category);
        $headers = [
            'referer' => 'https://mi.aliyun.com/',
            'user-agent' => SpiderUtils::getUserAgent(),
        ];
        $query = $category;
        $options = compact('headers', 'query');
        $data = $this->crawlByGuzzleHttp($url, $options);
        return $data;
    }

    public function parse($data)
    {
        $result = [];
        $domains = $data['data']['pageResult']['data'];
        foreach ($domains as $domain) {
            $item = [];
            $price = floatval(str_replace(",", "", $domain['price']));
            $item['domain'] = $domain['domainName'];
            $item['introduction'] = $domain['introduction'];
            $item['price'] = $price;
            $item['url'] = sprintf($this->detail_url, $domain['domainName'], $domain['productType']);
            array_push($result, $item);
        }
        return $result;
    }

    private function getToken()
    {
        $token = "";
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghzklmnopqrstuvwxyz0123456789';
        for ($i = 0; $i < 32; $i++) {
            $random = mt_rand(0, pow(10, 8)) % strlen($str);
            $token .= $str{$random};
        }
        return $token;
    }
}