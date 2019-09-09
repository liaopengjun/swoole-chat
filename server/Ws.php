<?php

class Ws{

    const Host = '0.0.0.0';
    const Part = 8811;
    public $ws = null;

    public function  __construct(){

        $this->ws = new swoole_websocket_server(self::Host,self::Part);


        $this->ws->set([
            'enable_static_handler' => true,
            'document_root' => "/data/chat/application/index/view",
            'worker_num' => 4,
            'task_worker_num' => 4,
        ]);


        $this->ws->on("open",[$this,'oneOpen']);

        $this->ws->on("task",[$this,'onTask']);

        $this->ws->on("workerstart", [$this, 'onWorkerStart']);

        $this->ws->on("request", [$this, 'onRequest']);

        $this->ws->on("finish",[$this,'onFinish']);

        $this->ws->on("message",[$this,'onMessage']);

        $this->ws->on("close",[$this,'onClose']);

        //开启
        $this->ws->start();

    }

    /** ws 监听连接事件
     * @param $ws
     * @param $request
     */
    public function oneOpen($ws,$request){
       print_r($ws);

        var_dump($request->fd);
        //将用户存储到redis中
//        \app\common\redis\Predis::getInstance()->sadd('redis_sadd_key',$request->fd);

        //执行定时
//        if($request->fd == 1){
//            //每隔2000ms触发一次
//            swoole_timer_tick(2000, function ($timer_id) {
//                echo "I love deng:{$timer_id}\n";
//            });
//
//        }


    }


    /** 监听消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws,$frame){
        echo "ser-push-message:{$frame->data}\n";
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
//        echo "消息内容:{$frame->data}\n";

//        $data = [
//            'task'=>1,
//            'fd'=>$frame->fd,
//        ];

        //$ws->task($data);

        //3000ms后执行此函数
//        swoole_timer_after(3000, function() use ($ws,$frame) {
//            echo "after 3000ms\n";

//            $ws->push($frame->fd,"Deng also loves the junge");

//        });

//        $ws->push($frame->fd,"时间".date("Y-m-d H:i:s"));

    }
    /**
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server,  $worker_id){

        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/');
        // 加载框架里面的文件
        require __DIR__ . '/../thinkphp/base.php';

    }

    /** 回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response){
        $_SERVER  =  [];
        if(isset($request->server)) {
            foreach($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if(isset($request->header)) {
            foreach($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        $_GET = [];
        if(isset($request->get)) {
            foreach($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        $_POST = [];
        if(isset($request->post)) {
            foreach($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $_FILES = [];
        if(isset($request->files)) {
            foreach($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        //返回http服务
        $_POST['http_server'] = $this->ws;

        ob_start();
        // 执行应用并响应
        try {
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e) {
            // todo
        }

        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);

    }

    /**
     * @param $serv
     * @param $task_id
     * @param $src_worker_id
     * @param $data
     * @return string
     */
    public function onTask($serv, $task_id,$src_worker_id,$data){
        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\task\Task;

        $method = $data['method'];
        $flag = $obj->$method($data['data'], $serv);
        /*$obj = new app\common\lib\ali\Sms();
        try {
            $response = $obj::sendSms($data['phone'], $data['code']);
        }catch (\Exception $e) {
            // todo
            echo $e->getMessage();
        }*/

        return $flag; // 告诉worker

    }

    /**
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function onFinish($serv,$task_id,$data){
        echo "client:{$task_id}\n";
        echo "finish-data-success:{$data}";
    }

    /** 监听关闭事件
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd){
        echo "close:{$fd}\n";
        //清除redis
//        \app\common\redis\Predis::getInstance()->srem('redis_sadd_key',$fd);

    }



}
$obj = new Ws();