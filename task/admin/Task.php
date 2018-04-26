<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/26
 * Time: 18:16
 */
namespace myswoole\task\admin;

//后台任务列表
class Task{
	public function pushLive($data){
		$server = $_POST['http_server'];
		
		echo "任务数据开始：".PHP_EOL;
		print_r($data);
		echo PHP_EOL;
		echo "任务数据结束：".PHP_EOL;

	}
}