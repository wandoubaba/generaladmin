<?php
namespace app\admin\model;
use app\common\model\BaseCU;

class AdminLog extends BaseCU
{
	public function admin()
	{
		return $this->hasOne('Admin');
	}

	public function getLogIpAttr($value) {
		if($value!==null) {
			return long2ip($value);
		} else {
			return '';
		}
	}

	public function setLogPermissibleAttr($value) {
		if($value) {return 1;}
		else {return 0;}
	}

	public function getLogPermissibleAttr($value) {
		if($value) {return '允许';}
		else {return '拒绝';}
	}
}