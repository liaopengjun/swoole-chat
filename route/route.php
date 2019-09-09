<?php

//登录注册页面
Route::get('/login', 'index/login/index');
Route::get('/outLogin', 'index/login/outLogin');
Route::get('/registered', 'index/login/registered');
Route::post('/adduser', 'index/login/adduser');
Route::post('/getLogin', 'index/login/getLogin');


