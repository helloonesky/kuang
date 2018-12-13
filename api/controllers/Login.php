<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 登录相关接口
 */
class Login extends CI_Controller {

    /**
     * 获取APP应用相关配置
     */
    public function getAppversion()
    {
        $data = [];
        $data['version'] = $this->config->item('version');
        $data['ios_download'] = $this->config->item('ios_download');
        $data['and_download'] = $this->config->item('and_download');
        $data['is_must_update'] = $this->config->item('is_must_update');
        $data['update_info']  = $this->config->item('update_info');

        outJson(0, '', $data);
    }

    /**
     * 会员登录操作
     */
    public function doLogin()
    {
        $this->load->library("mysql/memberdb");
        $this->load->library("redis/membercache");
        $nickname = postString('nickname');
        $pwd = postString('pwd');

        if(!$pwd || !$nickname)
        {
            outJson(1, '参数不能为空');
        }

        //密码配置
        $this->load->library("redis/Passwordcache");
        $this->config->load('assets');

        $is_need_check_pay_pwd = $this->config->item('is_need_check_login_pwd');
        $login_pwd_error_max = $this->config->item('login_pwd_error_max');
        $_login_pwd_key = $nickname.'_'.date('Y-m-d').'_login_pwd';
        $_hash_key = 'input_login_pwd';

        $pwd_error_num = $this->passwordcache->hGetInfo($_hash_key, $_login_pwd_key);

        if($is_need_check_pay_pwd && $pwd_error_num && $pwd_error_num>=$login_pwd_error_max)
        {
            outJson(1, '登录密码错误已达上限,请明日再来');
        }

        //校验密码
        $aespwd = aesCode($pwd, 'encode', MEMBER_REG_PWD);
        $memberInfo = $this->memberdb->getInfo(['nickname'=>$nickname], [], 'id,nickname,member_icon,status,pwd');
        if(!$memberInfo)
        {
            outJson(1, '该账户未注册');
        }

        if($aespwd != $memberInfo['pwd'])
        {
            $pwd_error_num = $pwd_error_num ? ++$pwd_error_num : 1;
            $this->passwordcache->hSetInfo($_hash_key, $_login_pwd_key, $pwd_error_num, 86400);
            outJson(1, '登录密码输入有误');
        }

        if($memberInfo['status'] == 0)
        {
            outJson(1, '账户未激活,请等待审核');
        }

        if($memberInfo['status'] == 2)
        {
            outJson(1, '账户审核拒绝,不能登录');
        }

        //删除上一次登陆的token
        $loginInfo = $this->membercache->getMember($memberInfo['id']);
        if($loginInfo)
        {
            $loginInfo = json_decode($loginInfo, true);
            $this->membercache->delToken($loginInfo['last_login_token']);
        }

        //写token 15天过期
        $token = createLoginToken($memberInfo['id']);
        $this->membercache->setToken($token, NOW_TIME+15*86400);

        //写入用户信息
        $memberInfo['last_login_token'] = $token;
        unset($memberInfo['status']);
        unset($memberInfo['pwd']);
        $this->membercache->setMemberInfo($memberInfo['id'], json_encode($memberInfo, JSON_UNESCAPED_UNICODE));

        outJson(0, '登陆成功', $token);
    }

    /**
     * 会员退出登陆操作
     */
    public function loginOut()
    {
        $this->load->library("redis/membercache");

        $token = postString('token');
        if(!$token)
        {
            outJson(1, 'token不能为空');
        }

        $this->load->library("redis/membercache");
        $tokenInfo = $this->membercache->getToken($token);
        if(!$tokenInfo)
        {
            outJson(1, 'token不存在');
        }
        //删除token
        $this->membercache->delToken($token);

        outJson(0, '退出成功');
    }

}