<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use 会员注册相关
 * @author sky
 * @date 2018-10-29 17:00
 */
class Regdb {
    public $_table = 'member_info';
    public $_mysqllib;

    public function __construct()
    {
        $this->_mysqllib = new mysql_lib(['table' => $this->_table]);
    }

    /**
     * @use 添加一条信息
     */
    public function addRegpayInfo($data)
    {
        return $this->_mysqllib->addInfo($data);
    }

    /**
     * 根据条件获取到一条数据
     */
    public function getInfo($where, $order = array(), $field = null)
    {
        return $this->_mysqllib->getInfo($where, $order, $field);
    }

    /**
     * @use 更新信息
     */
    public function updateInfo($data, $where)
    {
        return $this->_mysqllib->updateInfo($data, $where);
    }
}