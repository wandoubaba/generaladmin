<?php
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule =   [
        'admin_name'            =>  'require|length:4,25|alphaDash|checkName:admin',
        'admin_password'		=>	'require|confirm|length:4,25|alphaDash',
        'admin_email'           =>  'email',
    ];
    
    protected $message  =   [
        'admin_name.require'        =>	'用户名是必填项',
        'admin_name.length'         =>  '用户名长度在4-25之间',
        'admin_name.alphaDash'      =>  '用户名只允许包含字母、数字、下划线或减号',
        'admin_password.require'	=>	'密码是必填项',
        'admin_password.confirm'    =>  '两次输入的密码不一致',
        'admin_password.length'     =>  '密码的长度在4-25之间',
        'admin_passowrd.alphaDash'  =>  '密码只允许包含字母、数字、下划线或减号',
        'admin_email.email'         =>  '邮箱格式要填写正确',
    ];
    
    protected $scene = [
        'init'          =>  [],
        'password'      =>  ['admin_name','admin_password'],
        'nopassword'    =>  ['admin_name'=>'require|length:4,25|alphaDash','admin_email'],
        'login'         =>  ['admin_name'=>'require|length:4,25|alphaDash','amdin_password'=>'require|length:4,25|alphaDash'],
    ];

    protected function checkName($value,$rule,$data)
    {
        return $rule == $value ? '不允许对admin进行操作' : true;
    }
}