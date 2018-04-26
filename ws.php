<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/23
 * Time: 20:57
 */

//对swoole web_socket_server 功能的封装
//既可以用http服务器 也可以用websocket服务
require_once "./Task.php";
require_once "./Config.php";

class Ws{
	private $_ip = "";
	private $_port = 0;
	private $_server_http_server;
	private $_table;
	private $_config; //配置文件

	public function __construct($ip="0.0.0.0", $port=8811) {
		$this->_ip      = $ip;
		$this->_port    = $port;
	}

	//设置table数据
	private function setTable(){
		//主要是为了共享数据
		$table = new swoole_table($this->_config['table_size']);

		//设置列
		$table->column("data", $table::TYPE_STRING, 120);
		$table->column("id", $table::TYPE_INT, 11);

		//创建表
		$table->create();
		$this->_table = $table;
	}

	public function run(){

		$this->_config = Config::getConfig("web");

		$this->_server_http_server = new swoole_websocket_server($this->_config['ip_address'], $this->_config['port']);
		$this->_server_http_server->set([
			'worker_num'            => $this->_config['worker_num'],
			'task_worker_num'       => $this->_config['task_worker_num'],
			'enable_static_handler' => $this->_config['enable_static_handler'],
			'document_root'         => $this->_config['document_root'],
		]);

		//start 主进程时调用
		$this->_server_http_server->on('start', [$this, "onStart"]);

		//start 主进程是调用  进程组启动
		$this->_server_http_server->on('WorkerStart', [$this, "onWorkerStart"]);

		//服务端接收到请求时触发
		$this->_server_http_server->on('request', [$this, "onRequest"]);

		//用户连接时触发
		$this->_server_http_server->on('open', [$this, "onOpen"]);

		//服务端接收到数据
		$this->_server_http_server->on('message', [$this, "onMessage"]);

		//投递任务
		$this->_server_http_server->on('task', [$this, "onTask"]);

		//任务完成时调用
		$this->_server_http_server->on('finish', [$this, "onFinish"]);

		//客户端关闭时调用
		$this->_server_http_server->on('close', [$this, "onClose"]);

		//设置内存表 用于存储用户连接信息
		$this->setTable();

		$this->_server_http_server->start();
	}

	//start 主进程时调用
	public function onStart(swoole_websocket_server $server){
		//设置主进程别名 netstat -apn|grep 9501
		//tcp        0      0 0.0.0.0:9501            0.0.0.0:*               LISTEN      32400/live_master
		// 可以获取进程号 pidof live_master

		swoole_set_process_name($this->_config['process_name']);
	}

	//start 主进程是调用  进程组启动
	public function onWorkerStart(swoole_websocket_server $server, $worker_id){

		//这里表是新表不用清空数据，但是如果是redis存储需要清空表数据

		//开启应用 制作框架
		#define('APP_PATH', __DIR__ . '/../../application/');
		//不用直接执行
		#require __DIR__ . '/../../thinkphp/start.php';
	}

	//服务端接收到请求时触发
	public function onRequest($request, $response){
		$_SERVER = $_POST = $_GET = array();

		if(isset($request->server)){
			foreach ($request->server as $k=>$v){
				$_SERVER[strtoupper($k)] = $v;
			}
		}

		if(isset($request->header)){
			foreach ($request->header as $k=>$v){
				$_SERVER[strtoupper($k)] = $v;
			}
		}

		if(isset($request->get)){
			foreach ($request->get as $k=>$v){
				$_GET[$k] = $v;
			}
		}

		if(isset($request->post)){
			foreach ($request->post as $k=>$v){
				$_POST[$k] = $v;
			}
		}

		if($request->server['request_uri'] == "/favicon.ico"){
			$response->status(404);
			$response->end();
			return;
		}

		//投递一个任务
		$request_data = array_merge($_GET, $_POST, $_SERVER);
		$data = [
			'method' => 'userRequest',
			'data'   => json_encode($request_data)
		];
		$this->_server_http_server->task($data);

		$_POST['http_server']   = $this->_server_http_server;
		$_POST['http_table']    = $this->_table;

		//  执行应用
		/*ob_start();
		think\App::run()->send();
		$result = ob_get_contents();
		ob_end_clean();*/
		$result = " server_0111";

		$response->end($result);
	}

	//用户连接时触发
	public function onOpen(swoole_websocket_server $server, $request){
		//将连接存到表中
		$this->_table->set($this->_config['table_adduser_key'].$request->fd, ['id'=>$request->fd, 'data'=>""]);

		//遍历表 看看有什么
		foreach ($this->_table as $k=>$v){
			print_r($v);
		}

		$server->push($request->fd, "我连接上你了");
	}

	//服务端接收到数据
	public function onMessage(swoole_websocket_server $server, $request){
		//批量推送
		$server->push($request->fd, "收到数据有：{$request->data}");
	}

	//投递任务
	public function onTask(swoole_websocket_server $server, $task_id, $work_id, $data){
		$metaod = $data['method'];
		$obj = new Task();
		$flag = $obj->$metaod($data['data'], $server);

		return "ON task success\n";
	}

	//任务完成时调用
	public function onFinish(swoole_websocket_server $server, $task_id, $data){
		echo "task_id: {$task_id} finish\n";
		//是task 返回的内容
		echo "finish-data-success:{$data}\n";
	}

	//客户端关闭时调用
	public function onClose(swoole_websocket_server $server, $fd){

		//删除连接用户
		$this->_table->del($this->_config['table_adduser_key'].$fd);

		echo "client {$fd} closed\n";
	}
}

$sw = new Ws();
$sw->run();

