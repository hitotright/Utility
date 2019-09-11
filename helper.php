<?php

use think\Log;

/**
 * hex颜色加深减淡
 * @param $color
 * @param $percent 0-1
 * @return string
 */
function changeColor($color, $percent)
{
    if(strpos($color,'#') === false){
        return '';
    }
    $color = str_replace('#', "", $color);
    $t = $percent < 0 ? 0 : 255;
    $p = $percent < 0 ? $percent * -1 : $percent;
    $RGB = str_split($color, 2);
    $R = hexdec($RGB [0]);
    $G = hexdec($RGB [1]);
    $B = hexdec($RGB [2]);
    return '#' . substr(dechex(0x1000000 + (round(($t - $R) * $p) + $R) * 0x10000 + (round(($t - $G) * $p) + $G) * 0x100
            + (round(($t - $B) * $p) + $B)), 1);
}

if (!function_exists('array_column')) {
    function array_column(array $array, $column, $index_key = null)
    {
        $data = [];
        foreach ($array as $v) {
            $vv = $column !== null ? $v[$column] : $v;
            if ($index_key === null) {
                $data[] = $vv;
            } else {
                $data[$v[$index_key]] = $vv;
            }
        }
        return $data;
    }
}

function errorLog(Exception $e){
    Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
}

function wildcard($str,$url='',$title='',$keywords='',$description='',$pub_time='',$image=''){
    $time = str_replace(' ','T',date('Y-m-d H:i:s'));
    $pub_time = $pub_time == ''?$time:str_replace(' ','T',$pub_time);
    $http='https://www.'.config('domain');
    if(is_array($image)){
        if(!empty($image)){
            $image = '"'.$http.implode('","'.$http,$image) .'"';
        }else{
            $image ='';
        }
    }else{
        $image = trim($image)!==''?'"'.$http.$image.'"':'';
    }
    $wildcard = [
        'url'=>$url,
        'time'=>$time,
        'title'=>$title,
        'keywords'=>$keywords,
        'description'=>$description,
        'pub_time'=>$pub_time,
        'image'=>$image
    ];
    $preg = $replace =[];
    foreach ($wildcard as $key=>$value){
        $preg[]='/{{'.$key.'}}/';
        $replace[] = $value;
    }
    return preg_replace($preg,$replace,$str);
}

if(!function_exists('mb_str_split')){
    function mb_str_split($str,$len){
        $arr= preg_split('/(?<!^)(?!$)/u', $str );
        $res = array_chunk($arr,$len);
        foreach ($res as $k=>$v){
            $res[$k]=implode('',$v);
        }
        return $res;
    }
}
