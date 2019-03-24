<?php
namespace app\admin\validate;

use think\Validate;

class Config extends Validate
{
    protected $rule =   [
        'config_name'   =>  'require|length:2,20',
        'config_key'    =>  'require|length:2,20|alphaDash',
        'config_value'  =>	'length:0,4000',
    ];
    
    protected $message  =   [
        'config_name.require'=>'配置名称不能空',
        'config_name.length'=>'配置名称长度在2至20之间',
        'config_key.require'=>'配置键名不能空',
        'config_key.length'=>'配置键名长度在2至20之间',
        'config_key.alphaDash'=>'配置键名只允许英文字母、数字、下划线或减号',
        'config_value.length'=>'配置值最大长度不能超过4000',
    ];
    
    protected $scene = [
        
    ];

}