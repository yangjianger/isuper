<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/26
 * Time: 19:30
 */

class Config{

	public static $all_config = null;

	//获取所有的配置
	public static function getAllConfig(){

		if(self::$all_config == null){
			$dir_path = __DIR__.'/config/';
			$dh = opendir($dir_path);

			$data = [];
			while ($file = readdir($dh)){
				$file_path = $dir_path.$file;
				if(is_file($file_path)){
					$file_arr = explode(".", $file);
					$data[$file_arr[0]] = require_once $file_path;
				}
			}

			self::$all_config = $data;
		}

		return self::$all_config;
	}

	//获取单项配置
	public static function getConfig($config_str){

		$all_config = self::getAllConfig();

		if(!$config_str){
			return $all_config;
		}

		$config_arr = explode(".", $config_str);

		if(!isset($config_arr[1])){
			return $all_config[$config_arr[0]] ? $all_config[$config_arr[0]] : array();
		}

		return (isset($all_config[$config_arr[0]][$config_arr[1]]) && $all_config[$config_arr[0]][$config_arr[1]]) ? $all_config[$config_arr[0]][$config_arr[1]] : "";

	}
}
