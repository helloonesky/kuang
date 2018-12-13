<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 用户相关
 */
class Member extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 修改邮箱
     */
    public function editEmail()
    {
        $this->load->library("mysql/memberdb");

        $email   = postString('email');

        if(!$email)
        {
            outJson(1, '邮箱为空');
        }

        if(!verifyEmail($email))
        {
            outJson(1, '请填写正确的邮箱');
        }

        $update = ['email'=>$email,'updatetime'=>NOW_TIME];
        $where  = ['id'=>$this->member_id];

        if($this->memberdb->updateInfo($update,$where))
        {
            outJson(0, '邮箱更新成功');
        }
        else
        {
            outJson(1, '邮箱更新失败');
        }
    }

    /**
     * 忘记支付密码
     */
    public function lostPayPassword()
    {
        $mobile          = postString('mobile');
        $code            = postString('code');
        $passpord        = postString('pwd');
        $confirm_pwd     = postString('confirm_pwd');

        $this->load->library("mysql/memberdb");

        if(!$confirm_pwd || !$passpord)
        {
            outJson(1, '参数为空');
        }

        if($passpord != $confirm_pwd)
        {
            outJson(1, '两次密码输入不一致');
        }

        if(strlen($passpord) !=6)
        {
            outJson(1, '支付密码长度在6位');
        }

        if(!is_numeric($passpord))
        {
            outJson(1, '支付密码只能是数字');
        }

        //得到密文密码
        $pwd = aesCode($passpord, 'encode', MEMBER_REG_PWD);

        $update = ['pay_pwd'=>$pwd,'updatetime'=>NOW_TIME];
        $where  = ['id'=>$this->member_id];

        if($this->memberdb->updateInfo($update,$where))
        {
            outJson(0, '支付密码重置成功');
        }
        else
        {
            outJson(1, '支付密码重置失败');
        }
    }

    /**
     * 重置登录密码
     */
    public function resetPassword()
    {
        $old_pwd     = postString('old_pwd');
        $new_pwd     = postString('new_pwd');

        $this->load->library("mysql/memberdb");

        if(!$old_pwd || !$new_pwd)
        {
            outJson(1, '参数为空');
        }

        if(strlen($new_pwd)<6 || strlen($new_pwd)>18)
        {
            outJson(1, '密码长度在6到18位');
        }

        //校验旧密码是否正确
        $info = $this->memberdb->getInfo(['id'=>$this->member_id], [], 'id,pwd');

        if(!$info)
        {
            outJson(1, '读取信息失败');
        }

        $password = aesCode($old_pwd, 'encode', MEMBER_REG_PWD);

        if($password != $info['pwd'])
        {
            outJson(1, '旧密码输入有误');
        }

        //得到密文密码
        $pwd = aesCode($new_pwd, 'encode', MEMBER_REG_PWD);

        $update = ['pwd'=>$pwd,'updatetime'=>NOW_TIME];

        $where  = ['id'=>$this->member_id];

        if($this->memberdb->updateInfo($update,$where))
        {
            outJson(0, '密码重置成功');
        }
        else
        {
            outJson(1, '密码重置失败');
        }
    }

    /**
     * 重置支付密码
     */
    public function resetPayPassword()
    {
        $old_pwd     = postString('old_pwd');
        $new_pwd     = postString('new_pwd');

        $this->load->library("mysql/memberdb");

        if(!$old_pwd || !$new_pwd)
        {
            outJson(1, '参数为空');
        }

        if(strlen($new_pwd)!= 6)
        {
            outJson(1, '支付密码长度为6位');
        }

        if(!is_numeric($new_pwd))
        {
            outJson(1, '支付密码只能为纯数字');
        }

        //校验旧密码是否正确
        $info = $this->memberdb->getInfo(['id'=>$this->member_id], [], 'id,pay_pwd');

        if(!$info['pay_pwd'])
        {
            outJson(1, '读取信息失败');
        }

        $password = aesCode($old_pwd, 'encode', MEMBER_REG_PWD);

        if($password != $info['pay_pwd'])
        {
            outJson(100, '旧密码输入有误');
        }

        //得到密文密码
        $pwd = aesCode($new_pwd, 'encode', MEMBER_REG_PWD);

        $update = ['pay_pwd'=>$pwd,'updatetime'=>NOW_TIME];
        $where  = ['id'=>$this->member_id];

        if($this->memberdb->updateInfo($update,$where))
        {
            outJson(0, '支付密码重置成功');
        }
        else
        {
            outJson(1, '支付密码重置失败');
        }
    }

    /**
     *用户绑定银行卡
     */
    public function bindBankCard()
    {
        $bankname = postString('bankname');
        $bankinfo = postString('bankinfo');
        $bankcard = postString('bankcard');
        $bankuser = postString('bankuser');
        $mobile   = postString('mobile');
        $code     = postString('code');

        $member_id= $this->member_id;

        if(!$bankname || !$bankinfo || !$bankcard || !$bankuser || !$mobile)
        {
            outJson(1,'参数为空');
        }

        if(!verifyBankCard($bankcard))
        {
            outJson(1,'银行卡卡号格式有误');
        }

        if(!verifyMobile($mobile))
        {
            outJson(1,'手机格式有误');
        }

        $this->load->library("mysql/memberbankcarddb");

        //校验银行卡卡号
        $card = $this->memberbankcarddb->getInfo(['bankcard'=>$bankcard],[],'status');

        if($card['status'] == 1)//没有解绑
        {
           outJson(1,'该卡号已经绑定');
        }
        else if($card['status'] == 2)//已经解绑
        {
            $_update = [
                'status'  =>'1',
                'bankname'=>$bankname,
                'bankuser'=>$bankuser,
                'mobile'  =>$mobile,
                'bankinfo'=>$bankinfo,
            ];

            $_filter = ['member_id'=>$member_id,'bankcard'=>$bankcard];

            $is_success = $this->memberbankcarddb->updateInfo($_update,$_filter);
        }
        else
        {
            $_save = [
                'status'  =>'1',
                'bankname'=>$bankname,
                'bankuser'=>$bankuser,
                'mobile'  =>$mobile,
                'bankinfo'=>$bankinfo,
                'member_id'=>$member_id,
                'bankcard'=>$bankcard,
                'createtime'=>NOW_TIME,
            ];

            $is_success = $this->memberbankcarddb->addInfo($_save);
        }

        if($is_success)
        {
            outJson(0,'绑定成功');
        }
        else
        {
            outJson(1,'绑定失败');
        }
    }

    /**
     * 解绑银行卡接口
     */
    public function unbindBankCard()
    {
        $id      = postString('id');
        $pay_pwd = postString('pay_pwd');
        $this->load->library("mysql/memberbankcarddb");

        if(!$id || !$pay_pwd)
        {
            outJson(1,'参数为空');
        }

        $this->load->library("common/member_lib");

        if (!$this->member_lib->checkPayPwd($pay_pwd,$this->member_id))
        {
            outJson(100,'支付密码有误');
        }

        $_filter = ['member_id'=>$this->member_id,'id'=>$id];

        $card = $this->memberbankcarddb->getInfo($_filter,[],'status');

        if(!$card)
        {
            outJson(1,'卡号读取失败');
        }
        else if($card['status'] == 2)//没有解绑
        {
            outJson(1,'该卡号已经解绑');
        }
        else
        {
            $is_success = $this->memberbankcarddb->updateInfo(['status'=>'2','updatetime'=>NOW_TIME],$_filter);
        }

        if($is_success)
        {
            outJson(0,'解绑成功');
        }
        else
        {
            outJson(1,'解绑失败');
        }
    }

    /**
     * 分页获取用户绑定的银行卡
     */
    public function getUserBankCard()
    {
        $offset  = getInt('offset') ?  getInt('offset') : 0;
        $limit   = getInt('limit')>0 ? getInt('limit') : 20;

        $this->load->library("mysql/memberbankcarddb");

        $where = [
            'member_id'=>$this->member_id,
            'status'=>'1',
        ];

        $field = 'id,bankname,bankinfo,bankcard';
        $result = $this->memberbankcarddb->getList($offset,$limit,$where,$field);

        $data           = [];
        $data['list']   = [];
        if(!$result['list'])
        {
            outJson(0,'无银行卡信息',$data);
        }

        $this->load->library("common/memberbankcard_lib");
        //隐藏卡号
        foreach ($result['list'] as &$value)
        {
            $value['bankcard'] = $this->memberbankcard_lib->hiddenBandCard($value['bankcard']);
        }

        $data['list'] = $result['list'];

        $data['total_page']= isset($result['page']['total_pages']) ? $result['page']['total_pages'] : 0;

        outJson(0,'获取成功',$data);
    }

    /**
     * 根据id得到银行卡详情
     *
     */
    public function getBandCardById()
    {
        $id = getInt('id');

        if(!$id )
        {
            outJson(1,'缺少参数');
        }

        $this->load->library("mysql/memberbankcarddb");

        $where = [
            'member_id'=>$this->member_id,
            'id'=>$id,
            'status'=>'1',
        ];

        $field = 'id,bankname,bankinfo,bankcard';

        $row = $this->memberbankcarddb->getInfo($where,[],$field);

        if(!$row)
        {
            outJson(1,'该卡已经解绑或未绑定');
        }

        outJson(0,'成功',['card'=>$row]);
    }

    /**
     * 解绑银行卡之前 校验支付密码是否正确
     */
    public function checkPayPwd()
    {
        $pay_pwd = postString('pay_pwd');

        if(!$pay_pwd)
        {
            outJson(1,'缺少参数');
        }

        $this->load->library("mysql/memberdb");
        //校验旧密码是否正确
        $info = $this->memberdb->getInfo(['id'=>$this->member_id], [], 'id,pay_pwd');

        if(!$info['pay_pwd'])
        {
            outJson(1, '读取信息失败');
        }

        $password = aesCode($pay_pwd, 'encode', MEMBER_REG_PWD);

        if($password != $info['pay_pwd'])
        {
            outJson(100, '旧密码输入有误');
        }

        outJson(0, '校验成功');
    }

}