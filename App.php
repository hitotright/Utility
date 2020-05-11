<?php
namespace service;
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
            static::$instance = new static;
        }
        return self::$instance;
    }

    public function __get($class_name)
    {
        return $this->singleton($class_name);
    }

    /**
     * @param $class_name
     * @param $parameters
     * @return object
     */
    public function make($class_name,$concrete = null,$singleton=null){
        if(!property_exists(self::$instance,$class_name)||$singleton === false
            ||($singleton === null&&!property_exists(self::$instance->$class_name,'singleton'))){
            $class = "\\service\\".$class_name;
            self::$instance->$class_name = strpos($class_name,"\\") === false? new $class():new $class_name();
        }
        return self::$instance->$class_name;
    }

    //单例
    public function singleton($class_name,$concrete = null){
        return $this->make($class_name,$concrete,true);
    }
}
