<?php
# Role 和 Menu对应的“中间模型”
# 需要引入think\model\Pivot并extends
namespace app\admin\model;
use think\model\Pivot;

class RoleMenu extends Pivot
{
	public function role()
	{
		return $this->hasOne('Role');
	}

	public function menu()
	{
		return $this->hasOne('Menu');
	}
}