<?php
namespace app\index\controller;
use think\Db;
class Index extends Common
{
    public function index()
    {


        $uid = session('uid');
        $email = session('email');
        $this->assign('uid',$uid);
        $this->assign('email',$email);
        return view();


    }

    public function chatMsg(){
            $data = input('post.');
            if(!$data['toid']){
                return json([
                    'mag'=>'用户不存在',
                    'status'=>0,
                ]);
            }

            $data['addtime'] = time();
            $data['is_red'] = 0;
            $res = db('record')->insert($data);

            if($res){
                return json([
                   'msg'=>'发送成功',
                   'status'=>1,
                ]);
            }else{
                return json([
                    'msg'=>'系统错误无法发送消息',
                    'status'=>0,
                ]);
            }

        }
    //聊天记录
        public function load(){

            if(request()->isPost()){

                $fromid = input('post.fromid');
                $toid = input('post.toid');
                $count =  Db::name('record')
                        ->where("fromid=:fromid and toid=:toid or fromid=:toid1 and toid=:fromid1")
                        ->bind(['fromid' =>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
                        ->count('id');
                if($count>=10){
                    $message = Db::name('record')
                        ->where("fromid=:fromid and toid=:toid or fromid=:toid1 and toid=:fromid1")
                        ->bind(['fromid' =>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
                        ->limit($count-10,10)->order('id')->select();
                }else{
                    $message =Db::name('record')
                        ->where("fromid=:fromid and toid=:toid or fromid=:toid1 and toid=:fromid1")
                        ->bind(['fromid' =>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])
                        ->order('id')->select();
                }
                return json($message);

            }

        }


}
