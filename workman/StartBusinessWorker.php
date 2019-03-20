<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace app\script\workman;

use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'AppBusinessWorker-duxiaozhi';
// bussinessWorker进程数量
$worker->count = 4;
// 服务注册地址
$worker->registerAddress = '127.0.0.1:1238';


