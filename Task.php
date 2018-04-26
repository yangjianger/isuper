<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/26
 * Time: 18:16
 */

//任务列表
class Task{
	public function pushLive($data){
		$server = $_POST['http_server'];

		echo "任务数据开始：".PHP_EOL;
		print_r($data);
		echo PHP_EOL;
		echo "任务数据结束：".PHP_EOL;

	}

	public function userRequest($data){
		sleep(5);
		$data = json_decode($data, true);
		$logs = date("Y-m-d H:i:s").' ';
		foreach ($data as $k=>$v){
			$logs .= $k . ":" . $v." ";
		}
		$logs .= PHP_EOL;

		//写入日志
		$filename = __DIR__.'/logs/'.date("Y-m-d").'_logs.txt';
		file_put_contents($filename, $logs, FILE_APPEND);

	}
}