<?php

namespace App\Library\Spider\Drivers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use QL\QueryList;

class BaseDriver
{
    /**
     * crawl by GuzzleHttp
     * @param $url
     * @param $options
     * @param bool $recheck
     * @return mixed|null
     */
    protected function crawlByGuzzleHttp($url, $options, $recheck = false)
    {
        $data = null;
        try {
            Log::info('start crawl ' . $url);
            $client = new Client();
            $opts = [
                'connect_timeout' => 10,
                'timeout' => 10,
                'headers' => $options['headers']
            ];
            $response = $client->request('GET', $url, $opts);
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents, true);
            Log::info('end crawl');
        } catch (\Exception $exception) {
            //失败重试
            if (!$recheck) {
                return $this->crawlByGuzzleHttp($url, $options, true);
            }
            Log::info('GuzzleHttp爬虫失败：' . $exception->getMessage());
        }
        return $data;
    }

    /**
     * crawl by QueryList
     * @param $url
     * @param $options
     * @param array $args
     * @param $ql_config
     * @param bool $recheck
     * @return array
     */
    protected function crawlByQueryList($url, $options, $args = [], $ql_config, $recheck = false)
    {
        $data = [];
        $total = 0;
        $next_page = "";
        try {
            $log_url = $url . '?' . http_build_query($args);
            Log::info('start crawl ' . $log_url);
            $ql = QueryList::getInstance();
            $opts = [
                'connect_timeout' => 10,
                'timeout' => 10,
                'headers' => $options['headers']
            ];
            $qlObj = $ql->get($url, $args, $opts);

            if (isset($ql_config['total'])) {
                $total = $qlObj->find($ql_config['total'])->text();
                $total = (int)str_replace(["(", ")"], "", $total);
            } elseif (isset($ql_config['next_page'])) {
                $next_page = $qlObj->find($ql_config['next_page'])->attr('href');
            }


            if (($encode = $ql_config['encode']) && strtolower($ql_config['encode']) != 'utf-8') {
                $qlObj->removeHead()->encoding('utf-8', $encode);
            }
            $range = $ql_config['range'];
            if ($range) {
                $qlObj->range($range);
            }
            $rules = $ql_config['rule'];
            $list = $qlObj->rules($rules)
                ->query()->getData(function ($item) {
                    return str_replace(["\t", "\r\n", "\n"], '', $item);
                });
            $data = $list->all();
            Log::info('end crawl');
        } catch (\Exception $exception) {
            Log::info("QueryList爬虫失败：" . $exception->getMessage());
            //失败重试
            if (!$recheck) {
                return $this->crawlByQueryList($url, $options, $args, $ql_config, true);
            }
        }
        return compact('data', 'total', 'next_page');
    }
}