<?php
/**
 * FTP
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/8/8
 * Time: 15:43
 */
define('ROOT' , realpath(dirname(__FILE__)).'/');
define('REMOTE','/web/');

function main($argc,$argv){
    $arr_file=[];
    if($argc >= 2 ){
        unset($argv[0]);
        buildFile($argv,$arr_file);
    }else{
        die ('please enter files !'.PHP_EOL);
    }
    $arr_config=loadConfig();
    sendFtp($arr_file,$arr_config);
}

function loadConfig(){
    $config= @include_once(ROOT.'application/config/ftp.php');
    return empty($config)?die('not find config!(application/config/ftp.php)'):$config;
}

function buildFile($argv,&$arr_file,$root=ROOT,$remote=REMOTE){
    foreach ($argv as $value)
    {
        $local_file =$root.$value;
        $remote_file =$remote.$value;
        if(is_dir($local_file)){
            $dir = scandir($local_file);
            unset($dir[0],$dir[1]);
            if(!empty($dir)){
                buildFile($dir,$arr_file,$local_file.'/',$remote_file.'/');
            }
        }else{
            $arr_file[]=[
                'local'=>$local_file,
                'remote'=>$remote_file
            ];
        }
    }
}

function sendFtp($arr_file,$arr_config){
    foreach ($arr_config as $config){
        $config['port']=isset($config['port'])&&$config['port']!=''?$config['port']:'21';
        $ftp_con = @ftp_connect($config['host'],$config['port']) or die("Couldn't connect to ftp!");
        @ftp_login($ftp_con, $config['username'], $config['password']) or die('login failed!');
        if(isset($config['mode'])&&$config['mode']==true)
        {
            ftp_pasv($ftp_con,true);
        }
        $dir_checked=[];
        foreach ($arr_file as $file)
        {
            checkDirs($file['remote'],$ftp_con,$dir_checked);
            ftp_put($ftp_con, $file['remote'], $file['local'], FTP_BINARY);
        }
        ftp_close($ftp_con);
    }
}

function checkDirs($path,$ftp_con,&$dir_checked)
{
    @ftp_chdir($ftp_con,REMOTE);
    $path = str_replace(REMOTE,'',$path);//去除根目录
    $path_arr = explode('/',$path); // 取目录数组
    array_pop($path_arr); // 去除文件名
    $dir_path=implode('/',$path_arr);
    if(!in_array($dir_path,$dir_checked))
    {
        foreach($path_arr as $val) // 创建目录
        {
            if(@ftp_chdir($ftp_con,$val) == FALSE)
            {
                $tmp = @ftp_mkdir($ftp_con,$val);
                if($tmp == FALSE)
                {
                    echo "mkdir failed ,please check permission!($val)";
                    exit;
                }
                @ftp_chdir($ftp_con,$val);
            }
        }
        $dir_checked[]=$dir_path;
    }

}

main($argc,$argv);