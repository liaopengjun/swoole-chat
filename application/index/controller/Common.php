<?php

namespace app\index\controller;
use think\Controller;
use app\index\lib\Predis;

class Common extends  Controller
{

    //检查token是否过期
    public function initialize(){
        $email = session('email');

        if($email){
            $userStatus =  Predis::getInstance()->get(Predis::userkey($email));
            if(!$userStatus){
                $this->error('用户身份已过期','/login');
            }
        }else{
            $this->error('请登录','/login');
        }


    }



}
