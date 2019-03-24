<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 1,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 根据账号和密码的明文对密码进行本站自定义加密并返回加密后密文
 * @param  [string] $password [密码明文]
 * @param  [string] $name     [用户名明文]
 * @return [string]           [加密后的密文]
 */
function to_encrypt($password, $name) {
    // 对密码和用户名两个字符串进行简单处理
    // 用PHP自带password_hash()方法对新字符串进行加密处理
    // 结果返回60位字符串（每次运行返回的结果都会不同）
    $crypt = md5($password.strrev($name));
    $crypt = password_hash($crypt, PASSWORD_DEFAULT);
    //$crypt = hash('sha256',$crypt);
    return $crypt;
}

/**
 * 对用户名密码进行加密验证
 * @param  [type] $password [密码明文]
 * @param  [type] $name     [用户名明文]
 * @param  [type] $hash     [需要被验证的密文]
 * @return [type]           [验证结果布尔值]
 */
function to_encrypt_compare($password, $name, $hash) {
    // 对密码和用户名两个字符串进行重新排列组合生成新的字符串
    // 用PHP自带password_verify()方法对新字符串进行加密处理
    // 结果返回布尔值
    $crypt = md5($password.strrev($name));
    return password_verify($crypt, $hash);
}