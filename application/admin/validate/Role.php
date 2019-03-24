<?php
namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule =   [
        'role_name'     =>  'require',
    ];
    
    protected $message  =   [
        'role_name.require' =>	'名称是必填项',
    ];
    
    protected $scene = [
        
    ];

}