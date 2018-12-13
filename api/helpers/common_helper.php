<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use 自定义辅助函数
 * @author sky
 * @date 2018-07-15 15:00
 */
if(!function_exists('outJson'))
{
    /**
     * API统一JSON输出
     */
    function outJson($error=0, $msg='', $data=[])
    {
        $res = [];
        $res['error'] = $error ? $error : 0;
        $res['msg'] = $msg ? $msg : '';
        $res['data'] = $data ? $data : [];

        $res = json_encode($res, JSON_UNESCAPED_UNICODE);
        echo $res;
        exit();
    }
}

if(!function_exists('getInt'))
{
    /**
     * 获取get传递的int类型参数
     */
    function getInt($field)
    {
        $CI =& get_instance();
        return intval($CI->input->get($field, true));
    }
}

if(!function_exists('getString'))
{
    /**
     * 获取get传递的string类型参数
     */
    function getString($field)
    {
        $CI =& get_instance();
        return trim($CI->input->get($field, true));
    }
}

if(!function_exists('postInt'))
{
    /**
     * 获取post传递的int类型参数
     */
    function postInt($field)
    {
        $CI =& get_instance();
        return intval($CI->input->post($field, true));
    }
}

if(!function_exists('postString'))
{
    /**
     * 获取post传递的string类型参数
     */
    function postString($field)
    {
        $CI =& get_instance();
        return trim($CI->input->post($field, true));
    }
}

if(!function_exists('checkUrl'))
{
    /**
     * 验证url地址
     */
    function checkUrl($url)
    {
        return preg_match('/^https?:[\/]{2}[a-z]+[.]{1}[a-z\d\-]+[.]{1}[a-z\d]*[\/]*[A-Za-z\d]*[\/]*[A-Za-z\d]*/', $url);
    }
}

if(!function_exists('client_ip'))
{
    /**
     * 获取客户端IP地址
     * @return string
     */
    function client_ip()
    {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
        {
            $onlineip = getenv('HTTP_CLIENT_IP');
        }else if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown'))
        {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
        {
            $onlineip = getenv('REMOTE_ADDR');
        }else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
        {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }

        if(!preg_match('/[\d\.]{7,15}/', $onlineip))
        {
            $onlineip = '0.0.0.0';
        }

        return $onlineip;
    }
}

if(!function_exists('verifyEmail'))
{
    /**
     * 验证电子邮箱
     * @param $email
     * @return false|int  true成功  false 失败
     */
    function verifyEmail($email)
    {
        $pattern = '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';
        return preg_match($pattern, $email);
    }
}

if(!function_exists('verifyBankCard'))
{
    /**
     * 校验银行卡卡号合法性
     * @param $cardNum
     * @return false|int
     */
    function verifyBankCard($cardNum)
    {
        $pattern = '/^[\d]{15}|[\d]{16}|[\d]{17}|[\d]{19}$/';

        return preg_match($pattern, $cardNum);
    }
}

if(!function_exists('verifyMobile'))
{
    /**
     * 校验手机号合法性
     * @param $mobile
     * @return false|int
     */
    function verifyMobile($mobile)
    {
        $pattern = '/^[1][\d]{10}$/';

        return preg_match($pattern, $mobile);
    }
}