<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //注册
    public function signup()
    {
        $has_username_and_password = $this->has_username_and_password();
        if (!$has_username_and_password)
            return ['status' => 0, 'msg' => '用户名和密码皆不可为空'];
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];

        $user_exists = $this
            ->where('username', $username)
            ->exists();

        if ($user_exists)
            return ['status' => 0, 'msg' => '用户名已存在'];
        $user = $this;
        $user->password = $password;
        $user->username = $username;
        if ($user->save())
            return ['status' => 1, 'id' => $user->id];
        else return ['status' => 0, 'msg' => 'db insert failed'];

    }

    //登陆api
    public function login()
    {
        //检查用户名和密码是否存在
        $has_username_and_password = $this->has_username_and_password();
        if (!$has_username_and_password) {
            return ['status' => 0, 'msg' => '用户名和密码皆不可为空'];
        }
        $username = $has_username_and_password[0];
        $password = $has_username_and_password[1];
        //在数据中查找是否有该username, 如果有返回查询结果第一条
        $user = $this->where('username', $username)->first();
        if(!$user)
            return ['status' => 0, 'msg' => '用户名不存在'];
        $raw_password = $user->password;
//        dd($raw_password);
//        dd(rq('password'));
        //检查密码是否正确
        if($raw_password != rq('password'))
            return ['status' => 0, 'msg' => '密码不正确'];
        session()->put('username', $user->username);
        session()->put('user_id', $user->id);
        return ['status' => 1, 'msg' => '登陆成功'];
    }

    public function has_username_and_password()
    {
        $username = rq('username');
        $password = rq('password');
        /*检查用户名和密码是否为空*/
        if ($username && $password)
            return [$username, $password];
        return false;
    }
    //logout
    public function logout()
    {
        session()->forget('username');
        session()->forget('user_id');
        dd(session()->all());
        return ['status' => 1];
    }
    //change_password
    public function change_password()
    {
        //检查是否登录
        if(!$this->is_logged_in())
            return ['status'=>0,'msg'=>'login required'];
        //url中是否含有old&new password
        if(!rq('old_password') ||
            !rq('new_password'))
            return [
                'status' => 0,
                'msg' => 'old_password and new_password
                 are required'];
        //找到原密码
        $user = $this->find(session('user_id'));
        $raw_password = $user->password;
        //比较原密码
        if($raw_password != rq('old_password'))
            return ['status' => 0, 'msg' => '密码不正确'];
        //存入数据
        $user->password = rq('new_password');
        return $user->save() ?
            ['status' => 1]:
            ['status' => 0, 'msg' => 'db change failed'];

    }

    public function is_logged_in()
    {
        return session('user_id') ?: false;
    }
    //piano borrow/back
    public function borrow()
    {
        //是否登录
        if(!$this->is_logged_in())
            return ['status'=>0,'msg'=>'login required'];
        //是否借过
        if($this->borrowed())
            return ['status'=>0, 'msg' => 'you have borrowed a piano'];

        //将piano_id放入数据中
        $user = $this->find(session('user_id'));
        $user->piano_id = rq('pianoid');

        //添加一条租借的信息
        $user
            ->pianos()
            ->attach(session('user_id'), ['status' => '1']);

        //如果pianoid为空，则存入数据库
        return $user->save() ?
            ['status' => 1]:
            ['status' => 0,
                'msg' => 'db change failed'];
    }
    
    //归还
    public function giveback()
    {
        //是否登录
        if(!$this->is_logged_in())
            return ['status'=>0,'msg'=>'login required'];
        //是否借过
        if(!$this->borrowed())
            return ['status'=>0, 'msg' => 'you have not borrowed a piano'];
        //将piano_id放入数据中
        $user = $this->find(session('user_id'));
        $user->piano_id = null;

        //添加一条租借的信息
        $user
            ->pianos()
            ->attach(session('user_id'), ['status' => '0']);

        //如果pianoid为空，则存入数据库
        return $user->save() ?
            ['status' => 1]:
            ['status' => 0,
                'msg' => 'db change failed'];

    }

    public function borrowed()
    {
        if(!rq('pianoid'))
            return ['status'=>0,'msg'=>'pianoid required'];
        //检查是否已经租借钢琴
        $user = $this->find(session('user_id'));
        $pianoid = $user->piano_id;

        return $pianoid;
    }

    //session check
    public function checksession()
    {
        dd(session()->all());
    }

    //myhistory
    //remove user
    public function remove()
    {
        //是否登录
        if(!$this->is_logged_in())
            return ['status'=>0,'msg'=>'login required'];
        //参数中是否含有id
        if(!rq('id'))
            return ['status' =>0,'msg'=>'id is required'];
        $user = $this->find(rq('id'));
        if(!$user)
            return ['status'=>0,'msg'=>'user is not exist'];
        return $user->delete() ?
            ['status' => 1]:
            ['status'=> 0, 'db delete failed'];
    }

    //read user
    public function read()
    {
        //请求id
        if(!rq('id'))
            return ['status'=> 0, 'msg'=>'required id'];
        $get = ['username','username2','class','piano_id'];
        //获得id
        $user = $this->find(rq('id'), $get);
        $data = $user->toArray();
        return ['status' => 1, $data];
    }

    public function pianos()
    {
        return $this
            ->belongsToMany('App\Piano')
            ->withPivot('status')
            ->withTimestamps();
    }



}
