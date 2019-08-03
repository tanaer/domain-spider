<?php

namespace App\Utils;

class DomainUtil
{

    /**
     * 域名格式检查
     * @param $domain
     * @return bool
     */
    public static function checkFormat($domain)
    {
        if (preg_match("/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9-])+(\.[a-z]{2,5})?\.([a-z]|[\x{4e00}-\x{9fa5}]){2,10}$/ui", $domain)) {
            // 去掉-开头的域名
            if (substr($domain, 0, 1) != '-' && stripos($domain, '--') === FALSE) {
                return true;
            }
        }
        return false;
    }

    public static function checkPremium($domain)
    {
        $is_premium = false;
        $head = explode('|', config('domain.highlight_words_head'));
        foreach ($head as $word) {
            if ($word && strpos($domain, $word) === 0) {
                $is_premium = true;
                break;
            }
        }
        $tail = explode('|', config('domain.highlight_words_tail'));
        foreach ($tail as $word) {
            if ($word && strpos($domain, $word . '.') !== false) {
                $is_premium = true;
                break;
            }
        }
        return $is_premium;
    }
}