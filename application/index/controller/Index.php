<?php
namespace app\index\controller;
use app\index\controller\Common;

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


}
