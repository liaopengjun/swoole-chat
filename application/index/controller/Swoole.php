<?php

namespace app\index\controller;

use think\swoole\Server;
use app\index\lib\Predis;
use app\index\model\User;
class Swoole extends Server
{

    protected $host = '0.0.0.0';
    protected $port = 9502;
    protected $serverType = 'socket';
    protected $mode = SWOOLE_PROCESS;
    protected $sockType = SWOOLE_SOCK_TCP;
    protected $start_fd = 0;
    protected $option = [
        'worker_num'=> 4, //设置启动的Worker进程数
        'dispatch_mode'=>5,
        'daemonize'	=> false, //守护进程化（上线改为true）
        'dispatch_mode' => 2,
        'backlog'	=> 128, //Listen队列长度
        'task_worker_num' => 10
    ];


    //建立连接时回调函数
    public function onOpen($server, $request) {

        $fd = $request->fd;//客户端标识
        $uid = $request->get['uid'];
        $token = $request->get['token'];

        if(!$uid){
            $server->push($fd,'请先登录');
            $server->close($fd);
            return;
        }

        $userStatus =  Predis::getInstance()->get(Predis::userkey($token));

        if(!$userStatus){
            $server->push($fd,'用户身份无效,请重新登录');
            $server->close($fd);
            return;
        }


        //绑定用户
        $server->bind($fd,$uid);

        //存redis fd
        Predis::getInstance()->set(Predis::fdkey($uid),$fd);

        //获取总连接人数
        $clientList = $server->getClientList($this->start_fd,50);

        //获取绑定的uid
        foreach ($clientList as $v){
            $binduidArr= $server->getClientInfo($v);
            foreach ($binduidArr as $k1=>$v1){
                if($k1 == 'uid'){
                    $bindUid[] = $v1;
                }
            }
        }

        //在线连接用户列表
        $res = db('user')->where('id','in',$bindUid)->select();
        $data = [
            'fd'=>$clientList,
            'res' =>$res,
        ];

        //推送在线用户
        $task_id = $server->task($data);
    }

    public function onMessage ($server, $frame) {

        $sendmsg = json_decode($frame->data,true);

        if($sendmsg['type'] == 'say'){
            $toid = $sendmsg['res']['toid'];
            $fd = Predis::getInstance()->get(Predis::fdkey($toid));//接受者 fd
            $server->push($fd,$frame->data);
        }


    }

    public function onRequest ($request, $response) {
        $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
    }

    public function onClose($ser, $fd) {
        echo "client {$fd} closed\n";
    }

    public function onTask($serv, $task_id,$src_worker_id,$data){

        if($data){
            foreach ($data['fd'] as $v){
                $resArr = [
                    'type'=>'init',
                    'res'=>$data['res']
                ];
                $res = json_encode($resArr);
                $serv->push($v,$res);
            }
        }


    }



}
