<?php

namespace App\Utils;

class CommonUtil
{

    /**
     * 二维数组去重
     * @param $array2D array 二维数组
     * @return array
     */
    public static function array_unique_fb($array2D)
    {
        $unique = $array_keys = [];
        foreach ($array2D as $k => $v) {
            //每个数组键值（外部域名有的格式不一致）
            $array_keys[$k] = array_keys($v);
            //降维
            $v = implode(',', $v);
            $temp[$k] = $v;
        }
        //去掉重复的字符串
        $temp = array_unique($temp);
        foreach ($temp as $k => $v) {
            $array = explode(',', $v);
            //按照键值重新拼装
            foreach ($array_keys[$k] as $key => $val) {
                $data[$val] = $array[$key];
            }
            array_push($unique, $data);
        }
        return $unique;
    }

    /**
     * sort array
     * @param array $array
     * @param $key
     * @param int $order
     * @return array
     */
    public static function sortArray(array $array, $key, $order = SORT_DESC)
    {
        $tmp = array();
        foreach ($array as $k => $v) {
            $tmp[$k] = $v[$key];
        }
        array_multisort($tmp, $order, $array);
        return $array;
    }

}