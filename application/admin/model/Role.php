<?php
namespace app\admin\model;

use app\common\model\BaseCU;

class Role extends BaseCU
{
	// 通过role_menu表关联menu
	public function menus()
	{
		return $this->belongsToMany('Menu','\app\admin\model\RoleMenu');
	}
}