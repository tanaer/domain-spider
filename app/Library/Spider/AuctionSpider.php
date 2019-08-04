<?php

namespace App\Library\Spider;

use App\Library\Notice\DingDingNotice;
use App\Library\Spider\Drivers\Aiming;
use App\Library\Spider\Drivers\Aliyun;
use App\Library\Spider\Drivers\Ename;
use App\Library\Spider\Drivers\Nawang;
use App\Utils\CommonUtil;
use App\Utils\DomainUtil;
use App\Utils\ShortUrlUtil;
use App\Utils\SpiderUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AuctionSpider
{
    protected $site;
    const REGISTRAR_22 = '22';
    const REGISTRAR_ENAME = 'ename';
    const REGISTRAR_ALIYUN = 'aliyun';
    const REGISTRAR_NAWANG = 'nawang';

    const REGISTRAR = [
        self::REGISTRAR_ENAME,
        self::REGISTRAR_ALIYUN,
        self::REGISTRAR_22,
    ];

    public function __construct($site)
    {
        $this->site = $site;
    }

    public function handle()
    {
        set_time_limit(0);
        $this->crawl();
        $this->compare();
    }

    private function crawl()
    {
        $domains = $parse_domains = [];
        $site = $this->site;
        $spider_configs = config('spider');
        $config = $spider_configs[$site];

        foreach ($config['categories'] as $category) {
            for ($page = 1; $page < 500; $page++) {
                $url = $config['base_url'];
                try {
                    if ($site == self::REGISTRAR_ENAME) {//易名中国
                        $driver = new Ename();
                    } elseif ($site == self::REGISTRAR_ALIYUN) {//阿里云
                        $driver = new Aliyun();
                    } elseif ($site == self::REGISTRAR_22) {//爱名网
                        $driver = new Aiming();
                    } elseif ($site == self::REGISTRAR_NAWANG) {//纳网
                        $driver = new Nawang();
                    }
                    $data = $driver->crawl($url, $category, $page);
                    $parse = $driver->parse($data);
                    $domains = array_merge($domains, $parse);
                } catch (\Exception $e) {
                    Log::error('域名爬取失败', ['url' => $site, 'error' => $e->getMessage(), 'data' => $data]);
                    if (!isset($data['data'])) {
                        break;
                    }
                    continue;
                }
                if ($page >= $config['max_page']) {
                    break;
                }
                if ($site == self::REGISTRAR_NAWANG && !$data['next_page']) {
                    break;
                }
            }
        }
        if ($domains) {
            SpiderUtils::storage($domains, $site);
        } else {
            Log::error("[{$site}] 爬虫无数据");
        }
    }

    /**
     * 数据比对
     */
    private function compare()
    {
        $site = $this->site;
        $add = [];
        try {
            //超过2个hash删除较早记录
            while (Redis::llen('spider:' . $site) > 2) {
                $delete_hash = Redis::lpop('spider:' . $site);
                Redis::del($delete_hash);
            }
            $files = Redis::lrange('spider:' . $site, 0, 1);
            //是否第一次爬取
            if (isset($files[1])) {//否
                $old_hash = $files[0];
                $new_hash = $files[1];
            } else {//是
                $old_hash = null;
                $new_hash = $files[0];
            }
            $new_domains = Redis::hgetall('spider:' . $new_hash);
            //存在历史爬虫记录
            if ($new_domains && $old_hash) {
                $old_domains = Redis::hgetall('spider:' . $old_hash);
                $diff = array_udiff($new_domains, $old_domains, function ($new, $old) {
                    $new = json_decode($new, true);
                    $old = json_decode($old, true);
                    return strcmp($new['domain'], $old['domain']);
                });
                foreach ($diff as $k => $v) {
                    array_push($add, json_decode($v, true));
                }
                //删除历史记录
                Redis::del('spider:' . $old_hash);
                Redis::lpop('spider:' . $site);
                //价格升序
                $add = CommonUtil::sortArray($add, 'price', SORT_ASC);
                //新增域名通知
                $this->notice($site, $add);
            }
            Log::info("[$site] Storage End\n");
        } catch (\Exception $exception) {
            Log::error('一口价域名比对失败：', ['site' => $site, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * 通知
     * @param $site
     * @param array $domains
     */
    private function notice($site, array $domains)
    {
        $notice_msg = "";
        $premium_msg = "";
        $notice_count = $premium_count = 0;
        $site_str = __('common.' . $site);
        if ($domains) {
            $short_urls = $this->getShortUrl($domains);
            foreach ($domains as $item) {
                if (DomainUtil::checkPremium($item['domain'])) {
                    $premium_count++;
                    $premium_msg = $this->getNoticeMsg($premium_msg, $short_urls, $item);
                } else {
                    $notice_count++;
                    $notice_msg = $this->getNoticeMsg($notice_msg, $short_urls, $item);
                }
            }
            $ding = new DingDingNotice();
            if ($notice_count) {
                $notice_msg = "【{$site_str}】新增域名 " . $notice_count . " 个 \n" . $notice_msg;
                $ding->notice($notice_msg);
                Log::info('新增域名通知成功：' . $notice_msg);
            }
            if ($premium_count) {
                $premium_msg = "【{$site_str}】新增优质域名 " . $premium_count . " 个 \n" . $premium_msg;
                $ding->notice($premium_msg, config('ding.at_mobiles'));
                Log::info('优质域名通知成功：' . $premium_msg);
            }
        }
        if (!$notice_msg && !$premium_msg) {
            Log::info("【{$site_str}】一口价域名无变化");
        }
    }

    /**
     * 获取短链接
     * @param $domains
     * @return array|null
     */
    private function getShortUrl($domains)
    {
        $urls = null;
        $long_urls = collect($domains)->pluck('url')->toArray();
        $short_urls = ShortUrlUtil::getShortUrl($long_urls);
        if ($short_urls) {
            $urls = collect($short_urls)->pluck('url_short', 'url_long')->toArray();
        }
        return $urls;
    }

    /**
     * 获取通知
     * @param $notice_msg
     * @param $short_urls
     * @param $item
     * @return string
     */
    private function getNoticeMsg($notice_msg, $short_urls, $item)
    {
        $url = isset($short_urls[$item['url']]) ? $short_urls[$item['url']] : "";
        $notice_msg .= $item['domain'] . "   {$item['price']}  {$url}" . " \n";
        return $notice_msg;
    }

}