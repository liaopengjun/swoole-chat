<?php
namespace app\index\controller;
use think\Db;
use app\index\lib\Predis;
class Login
{

    public function index()
    {
        return view();
    }

    public function  registered(){
        return view();
    }

    public function adduser(){
        if(request()->isPost()){

            $data =input('post.');
            $result = Db::name('user')->where('email',$data['email'])->find();

            if($result){
                return json([
                    'status'=>0,
                    'msg'=>'该邮箱已经注册'
                ]);
            }
            $data ['create_time'] = time();
            unset($data['repassword']);
            $data['password'] = md5($data['password']);
            
            $result = Db::name('user')->insert($data);

            if($result){
                return json([
                   'status'=>1,
                   'msg'=>'注册成功'
                ]);
            }else{
                return json([
                    'status'=>0,
                    'msg'=>'注册失败'
                ]);
            }
        }

    }

    public function getLogin(){
        $data =input('post.');
        
        $LoginErrorincr = Predis::getInstance()->get(Predis::incrkey($data['email']));

        if($LoginErrorincr >= 5){
            //设置过期时间
            Predis::getInstance()->expire(Predis::incrkey($data['email']),60);
            return json([
                'status'=>0,
                'msg'=>"密码输错五次请一分钟在重试",
            ]);
        }

        $res = Db::name('user')->where('email',$data['email'])->find();

        if(!$res){
            return json([
               'status'=>0,
               'msg'=>"用户不存在",
            ]);

        }else if($res['password'] == md5($data['password'])){
            session('email',$data['email']);
            session('uid',$res['id']);
            //存token 7200m
            $token = getRandStr(32);
            Predis::getInstance()->set(Predis::userkey($data['email']),$token,7200);
            return json([
                'status'=>1,
                'msg'=>"登陆成功",
            ]);
        }else{
            if(Predis::getInstance()->exists(Predis::incrkey($data['email']))){
                Predis::getInstance()->incr(Predis::incrkey($data['email']));
            }else{
                Predis::getInstance()->set(Predis::incrkey($data['email']),1);
            }
            return json([
                'status'=>0,
                'msg'=>"密码不正确",
            ]);
        }


    }
    public function outLogin(){
        $email = session('email');
        Predis::getInstance()->del(Predis::userkey($email));
        session('email',null);
        session('uid',null);
        return json([
            'status'=>1,
            'msg'=>"退出成功",
        ]);

    }

}
