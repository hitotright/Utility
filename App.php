<?php
namespace app\index\model;
/**
 * Created by PhpStorm.
 * User: hitotright
 * Date: 2019/7/29
 * Time: 17:43
 */
class App
{
    private static $instance;

    /**
     * @return App
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            static::$instance = ['App'=>new static];
        }
        return self::$instance['App'];
    }

    public function __get($class_name)
    {
        return $this->make($class_name);
    }

    /**
     * @param $class_name
     * @param $parameters
     * @return object
     */
    public function make($class_name){
        if(!isset(self::$instance[$class_name])){
            $class = "\\app\\index\\model\\".$class_name;
            self::$instance[$class_name] = strpos($class_name,"\\") === false? new $class():new $class_name();
        }
        return self::$instance[$class_name];
    }
}
