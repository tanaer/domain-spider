<?php

namespace App\Library\Spider\Drivers;


use App\Utils\DomainUtil;
use App\Utils\SpiderUtils;

class Ename extends BaseDriver
{
    public function crawl($url, $category, $page)
    {
        $headers = [
            'host' => 'auction.ename.com',
            'user-agent' => SpiderUtils::getUserAgent(),
        ];
        $category['page'] = $page;
        $options = compact('headers');
        $ql_config = [
            'rule' => [
                'domain' => ['td:nth-child(2)', 'text'],
                'price' => ['td:nth-child(5) span:nth-child(1)', 'text'],
                'introduction' => ['td:nth-child(3) div span', 'text'],
                'url' => ['td:nth-child(2) > a', 'href'],
            ],
            'range' => 'html > body > div:nth-child(3) > div > div:nth-child(2) > div > div:nth-child(1) > div:nth-child(2) > form > table > tbody > tr',
            'encode' => '',
        ];
        $data = $this->crawlByQueryList($url, $options, $category, $ql_config);
        return $data;
    }

    public function parse($data)
    {
        $result = [];
        $data = $data['data'];
        foreach ($data as $domain) {
            //域名格式校验
            if (!DomainUtil::checkFormat($domain['domain'])) {
                continue;
            }
            $item = [];
            $price = floatval(str_replace(",", "", $domain['price']));
            $item['domain'] = $domain['domain'];
            $item['introduction'] = $domain['introduction'];
            $item['price'] = $price;
            $item['url'] = 'http:' . $domain['url'];
            array_push($result, $item);
        }
        return $result;
    }
}