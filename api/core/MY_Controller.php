<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use API父类
 * @author sky
 * @date 2018-10-16 15:00
 *
 */
class MY_Controller extends CI_Controller
{
    public $member_id = 0;

    public $api_log;

    public function __construct($params = [])
    {
        parent::__construct();

        //APP：校验接口过期、签名
        if(isset($params['check_sign']) || isset($params['check_login']))
        {
            $api_data = array_merge($_GET,$_POST);
            $this->_checkSign($api_data);

            if(isset($params['check_login']))
            {
                $this->checkToken();
            }
        }
        else
        {
            //H5：校验登录token
            $this->checkToken();
        }
    }

    /**
     * H5：校验登录token
     */
    private function checkToken()
    {
        $token = getString('token');
        if(!$token)
        {
            outJson(1, 'token不能为空');
        }

        $this->load->library("redis/membercache");
        $tokenInfo = $this->membercache->getToken($token);
        if(!$tokenInfo)
        {
            outJson(198, 'token不存在');
        }

        if($tokenInfo < NOW_TIME)
        {
            //删除过期token
            $this->membercache->delToken($token);
            outJson(299, 'token已过期');
        }

        $tokenArr = explode("_", $token);
        $this->member_id = $tokenArr[0];
    }

    /**
     * 处理接口参数
     */
    private function assemble($params)
    {
        if(!is_array($params))
        {
            return null;
        }

        ksort($params,SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val)
        {
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }

        return $sign;
    }

    /**
     * 校验签名
     */
    private function _checkSign($params)
    {
        $this->api_log['request'] = $params;

        if(!isset($params['timestamp']) || strlen($params['timestamp']) != 10)
        {
            $this->outJson(1, 'timestamp format error ');
        }

        //提供10秒容错
        if($params['timestamp']>NOW_TIME && ($params['timestamp']-NOW_TIME)>10)
        {
            $this->outJson(1, 'current time error');
        }

        if(NOW_TIME>$params['timestamp'] && (NOW_TIME-$params['timestamp'])>10)
        {
            $this->outJson(1, 'timeout');
        }

        if(!isset($params['sign']))
        {
            $this->outJson(1, 'sign is not null');
        }

        $api_sign = $params['sign'];

        unset($params['sign']);

        $sign = $this->getSign($params,API_SIGN_KEY);

        if($sign != $api_sign)
        {
            $this->outJson(1, 'sign error');
        }
    }

    /**
     * 生成唯一的RequestId
     */
    public function getRequestId()
    {
        return md5(uniqid(date('Y-m-d H:i:s'), true));
    }
}