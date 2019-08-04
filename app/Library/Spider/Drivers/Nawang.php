<?php

namespace App\Library\Spider\Drivers;


use App\Utils\DomainUtil;
use App\Utils\SpiderUtils;

class Nawang extends BaseDriver
{
    protected $detail_url = 'https://na.wang%s';

    public function crawl($url, $category, $page)
    {
        $headers = [
            'user-agent' => SpiderUtils::getUserAgent(),
        ];
        $category['ExpirePreordain_page'] = $page;
        $options = compact('headers');
        $ql_config = [
            'rule' => [
                'domain' => ['td:nth-child(1)', 'text'],
                'url' => ['td:nth-child(7) > a', 'href'],
            ],
            'range' => '#yw0 > table > tbody > tr',
            'encode' => '',
            'next_page' => '#pagination > div > a:contains(下一页)',
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
            $item['domain'] = $domain['domain'];
            $item['introduction'] = "";
            $item['price'] = "";
            $item['url'] = sprintf($this->detail_url, $domain['url']);
            array_push($result, $item);
        }
        return $result;
    }
}