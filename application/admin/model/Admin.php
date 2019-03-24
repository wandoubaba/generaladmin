<?php
namespace app\admin\model;
use app\common\model\BaseCUD;
use app\admin\model\Role;

class Admin extends BaseCUD
{
	protected $auto = [
		// 'admin_password',
		'admin_role_id',
	];

	// 通过role_menu表关联role
	public function role()
	{
		return $this->hasOne('Role','role_id','admin_role_id');
	}

	public function logs()
	{
		return $this->hasMany('AdminLog','admin_name');
	}

	// admin_status获取器
	public function getAdminStatusAttr($value) {
		$s = '';
		switch ($value) {
			case '1':
				$s = '正常';
				break;
			default:
				$s = '禁用';
				break;
		}
		return $s;
	}

	// admin_role_id获取器
	// public function getAdminRoleIdAttr($value) {
	// 	$result = '';
	// 	if($value != 0) {
	// 		$result = Db::table('db_role')->where('role_id',$value)->value('role_name');
	// 	}
	// 	return $result;
	// }

	// admin_login_ip获取器，bigint转换为IPV4字符串
	public function getAdminLoginIpAttr($value) {
		if($value!==null) {
			return long2ip($value);
		} else {
			return '';
		}
	}

	// admin_login_time获取器，时间戳转换字符串
	public function getAdminLoginTimeAttr($value) {
		if($value) {
			return date(\think\Config::get('database.datetime_format'),$value);
		} else {
			return '';
		}
	}

	/**
	 * admin_password字段修改器,返回md5(sha1(p).sha1(n))
	 * @param [type] $value [description]
	 * @param [type] $data  [description]
	 */
	public function setAdminPasswordAttr($value, $data) {
		return to_encrypt($value, $data['admin_name']);
	}

	public function setAdminRoleIdAttr($value) {
		return $value=='' ? null : $value;
	}

}