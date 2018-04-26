<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/26
 * Time: 10:49
 */

//监控服务 ws http 8811

class Server{
	const PORT = 8811;

	public function port(){
		$shell = "netstat -apn 2>/dev/null | grep ".self::PORT."|grep LISTEN| wc -l";
		$result = shell_exec($shell);
		if($result != 1){
			//发送邮件
			echo "系统挂掉了".date("Y-m-d H:i:s").PHP_EOL;
		} else {
			echo "系统正常".date("Y-m-d H:i:s").PHP_EOL;
		}
	}
}

//每两秒执行一次
swoole_timer_tick(2000, function($timer_id){
	(new Server())->port();
	echo "timer-start".PHP_EOL;
});
