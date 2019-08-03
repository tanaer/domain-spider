<?php

namespace App\Library\Notice;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DingDingNotice
{
    public function notice($cont, $mobiles = [], $at_all = '')
    {
//        // 默认@用户
//        if (!count($mobiles)) {
//            $mobiles = config('notice.ding_at_mobiles');
//        }
//        // 是否@所有人
//        if (!$at_all) {
//            $at_all = config('notice.ding_at_all');
//        }
        $cont_arr = explode("\n", $cont);
        $cont_chunk = array_chunk($cont_arr, 400);
        foreach ($cont_chunk as $item) {
            $content = implode("\n", $item);
            $this->dingNotice($content, $mobiles, $at_all);
        }
    }

    private function dingNotice($cont, $mobiles, $at_all)
    {
        $msg = [
            'msgtype' => 'text',
            'text' => [
                'content' => $cont
            ],
            'at' => [
                'atMobiles' => $mobiles,
                'isAtAll' => $at_all
            ]
        ];
        $api = 'https://oapi.dingtalk.com/robot/send';
        $client = new Client();
        $result = $client->request('POST', $api, [
            'query' => [
                'access_token' => config('notice.ding_access_token')
            ],
            'json' => json_decode(json_encode($msg)),
            'verify' => false
        ]);
        $err_arr = json_decode($result->getBody()->getContents(), true);
        if ($err_arr['errcode']) {
            Log::info('钉钉通知出错', [$cont, $err_arr]);
        }
    }
}