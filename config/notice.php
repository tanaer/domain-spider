<?php

return [
    //钉钉通知
    'ding_access_token'=>env('DING_ACCESS_TOKEN',''),
    'ding_at_mobiles'=>explode(',',env('DING_AT_MOBILES','')),
    'ding_at_all'=>env('DING_AT_ALL',false)
];