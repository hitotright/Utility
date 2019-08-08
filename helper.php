<?php

use think\Log;

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
