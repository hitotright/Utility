<?php

namespace app\script\command;

use app\common\Utility;
use PDOException;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;

class SyncData extends Command
{
    private $a_id=1;
    private $ip;
    private $port =8282;
    private $db_config = 'local';

    /**
     * 面板
     */
    protected function configure()
    {
        $this->setName('syncdata')->setDescription('接收a端数据同步到b端');
    }

    /**
     * 入口
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $time = time();
        $this->output->writeln(date('Y-m-d H:i:s'));
        $this->ip = config('aduan_ip');
        $this->handle();
        $time = time() - $time;
        $this->output->writeln("all steps end!" . round($time / 60) . ':' . round($time / 60));
    }

    private function handle()
    {
        $key = config('md5_key');
        $b_id = Db::connect($this->db_config)->table('global_set')->where('gs_option','=','b_id')->value('gs_value',0);
        $uuid = 'B'.str_pad($b_id,11,0,STR_PAD_LEFT);
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        // 创建socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket <= 0) throw new \Exception('创建socket失败,REASON:' . socket_strerror($socket));
        try {
            // 连接服务器
            $result = socket_connect($socket, $this->ip, $this->port);
            if ($result < 0 || is_null($result) || !$result) throw new \Exception('连接失败,REASON:' . socket_strerror($result));
            $check =json_encode(['type'=>'verify','uuid'=>$uuid]);
            $check = Utility::encrypt($check,$key);
            $check.='\n';
            if (!socket_write($socket, $check, strlen($check))) throw new \Exception('消息发送失败,REASON:' . socket_strerror($socket));
            $tmp='';
            while (true){
                // 读取socket返回的数据
                $res = socket_read($socket,8192);
                if(var_export($res,1) == ''){
                    exit;
                }
                echo '收到：',var_export($res,1),PHP_EOL;
                $res = $tmp .$res;
                $data_arr = explode('\n',$res);
                $tmp = array_pop($data_arr);
                foreach ($data_arr as $data){
                    $in = '{"type":"ping"}';
                    if($data != $in){
                        $data = Utility::decrypt($data,$key);
                    }
                    echo 'data:',var_export($data,1),PHP_EOL;
                    $data = json_decode($data,1);
                    if($data['type'] == 'ping'){
                        Db::connect($this->db_config)->execute('select 1');
                    }
                    if($data['type'] == 'sql'){
                        $this->output->writeln(var_export($data,1));
//                    Log::error('data:'.var_export($data,1));
                        if($sql = $this->sqlCompose($data)){
                            $in = json_encode(['type'=>'sql_return','uuid'=>$data['source'],'sd_id'=>$data['sd_id'],
                                'status'=>$this->sqlHandle($sql)?2:3,'source'=>$uuid]);
                        }
                    }
                    if($data['type'] == 'sql_return'){
                        $this->output->writeln(var_export($data,1));
//                    Log::error('data:'.var_export($data,1));
                        $this->sqlConfirm($data['sd_id'],$data['status']);
                    }
//                Log::error('in:'.$in);
                    // 写入文件信息
                    $in = Utility::encrypt($in,$key);
                    $in.='\n';
                    if (!socket_write($socket, $in, strlen($in))) throw new \Exception('消息发送失败,REASON:' . socket_strerror($socket));
                }
            }
        } catch (Exception $e) {
            $this->output->writeln('ERROR: '.$e->getMessage());
            error($e);
        }
        // 关闭socket
        socket_close($socket);
    }

    private function sqlCompose($data){
        if($data['count'] == $data['current']){
            $sql = '';
            for ($i=1;$i<$data['current'];$i++){
                $sql.=Cache::get('sync_sql_'.$data['sd_id'].'_'.$i,'');
            }
            return $sql.$data['sql'];
        }else{
            Cache::set('sync_sql_'.$data['sd_id'].'_'.$data['current'],$data['sql'],0);
            return false;
        }
    }

    private function sqlHandle($sql){
        $sql = stripslashes($sql);
        echo 'handle:',$sql,PHP_EOL;
        Log::error('sql:'.$sql);
        try{
            Db::connect($this->db_config)->execute($sql);
            return true;
        }catch (Exception $e){
            error($e);
        }
        return false;
    }

    private function sqlConfirm($sd_id,$status){
        try{
            $sql= Db::connect($this->db_config)->table('sync_data')->fetchSql()->where('sd_id','=',$sd_id)->update(['status'=>$status,
                'end_time'=>date('Y-m-d H:i:s')]);
            Db::connect($this->db_config)->execute($sql);
            return true;
        }catch (Exception $e){
            error($e);
        }
        return false;
    }


}