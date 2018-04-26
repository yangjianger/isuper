<?php
/**
 * Created by PhpStorm.
 * User: yangjiang
 * Date: 2018/4/26
 * Time: 19:37
 */

return [
	'ip_address'                => "0.0.0.0", //检测ip
	'port'                      => 8811, // 端口
	'worker_num'                => 4, //进程数
	'task_worker_num'           => 8, //task 任务数
	'enable_static_handler'     => true, //是否允许开启静态资源
	'document_root'             => "/usr/local/nginx/html/myswoole/static", // 静态资源地址
	'process_name'              => "server_http_server", // 主进程名称
	'table_adduser_key'         => "link_user_", // 内存表键值
	'table_size'                => 1024, //table 表申请内存
];