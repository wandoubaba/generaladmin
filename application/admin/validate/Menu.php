<?php
namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule =   [
        'menu_name'     =>  'require',
        // 'menu_route'    =>  'requireIf:[menu_father_id,neq],0',
        'menu_sn'		=>	'number',
    ];
    
    protected $message  =   [
        'menu_name.require' =>	'名称是必填项',
        'menu_sn.number'	=>	'排序号只允许是数字',
    ];
    
    protected $scene = [
        
    ];

}