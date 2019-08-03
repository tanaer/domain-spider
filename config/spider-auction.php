<?php
/**
 * 一口价筛选配置
 */
return [
    '22' => [//爱名网
        'base_url' => 'https://am.22.cn/ajax/yikoujia/default.ashx',
        'max_page' => env('MAX_PAGE_22', 1),
        'categories' => [
            [
                'ddlSuf' => '.cn',//后缀
                'ddlclass' => 11,//纯数字
                'stype' => 32,//声母
                'registrar' => 1,//注册商（爱名网）
                'strlen' => '4,4',//长度
                'orderby' => 'Price_a',//排序
                'pageIndex' => 1,//页码
                'pageCount' => env('PER_PAGE_22', 50),//每页条数
            ]
        ],
    ],
    'aliyun' => [//阿里云
        'base_url' => 'https://domainapi.aliyun.com/onsale/search',
        'max_page' => env('MAX_PAGE_ALIYUN', 2),
        'categories' => [
            [
                'productType' => 2,//一口价（万网）
                'constitute' => 1208,//纯数字
                'suffix' => 'cn',//后缀
                'minLength' => 4,//最小长度
                'maxLength' => 4,//最大长度
                'fetchSearchTotal' => 'true',//提取搜索总数
                'sortBy' => 3,
                'sortType' => 1,
                'currentPage' => 1,//页码
                'pageSize' => env('PER_PAGE_ALIYUN', 200),//每页条数
            ]
        ],
    ],
    'ename' => [//易名中国
        'base_url' => 'http://auction.ename.com/tao/buynow/',
        'max_page' => env('MAX_PAGE_ENAME', 2),
        'categories' => [
            [
                'transtype' => 1,//一口价
                'domaingroup' => 11,//分类
                'selectTwo' => [6],//声母
                'domaintld' => [2],//后缀
                'registrar' => 1,//注册商（易名中国）
                'domainlenstart' => 4,//最小长度
                'domainlenend' => 4,//最大长度
                'sort' => 2,//排序
                'page' => 1,//页码
                'pageSize' => env('PER_PAGE_ENAME', 200),//每页条数
            ],
        ],
    ],
];