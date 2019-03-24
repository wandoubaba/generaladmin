<?php
namespace app\admin\controller;

use app\common\controller\General;
use think\captcha\Captcha;
use think\Session;
use think\Request;
use think\Loader;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Role as RoleModel;


class Index extends General
{
    public function index()
    {
    	$this->view->assign('title', $this->get_config('admin_title'));
    	$this->view->assign('pagetitle', '登录');
        return $this->view->fetch();
    }

    /**
	 * 输出验证码图片，前台可用src="{:url()}"方式引用
	 * @return [type] [验证码图片]
	 */
	public function login_captcha()
	{
		$config = [
    		'useCurve'	=>	false,
    		'length'	=>	4,
    		'fontSize'	=>	20,
    	];
		$captcha = new Captcha($config);
		return $captcha->entry();
	}

	/**
	 * 执行login操作：
	 * 对比用户名密码，
	 * 成功后将登录信息写入session，
	 * 超级管理员手动设置角色，
	 * 超级管理员的菜单为空，
	 * 记录登录IP、时间和次数
	 * 登录成功后将新的密码hash结果写入数据表
	 * @return [type] [如果登录成功，则自动跳转到admin模块的home页]
	 */
	public function do_login()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
		$data = input();
		$result['data'] = $data;
		if(!captcha_check($data['captcha'])) {
			$result['message'] .= '：验证码不正确';
			return $result;
		}
		$validate = Loader::validate('Admin');
		$v = $validate->scene('login')->check($data);
		if(!$v) {
			$result['message'] .= $validate->getError();
			return $result;
		}
		// 表单验证通过，可以对用户名密码进行验证了
		try {
			$admin = AdminModel::get(['admin_name'=>$data['admin_name']]);
		} catch(\Exception $e) {
			$result['message'] .= $e->getMessage();
			return $result;
		}
		if(!$admin) {
			// 用户名错误
			$result['message'] .= '用户名错误';
			return $result;
		}
		if(!to_encrypt_compare($data['admin_password'], $data['admin_name'], $admin->admin_password)) {
			// 密码错误
			$result['message'] .= '密码错误';
			return $result;
		}
		if($admin->admin_status != '正常') {
			// 账号已停用
			$result['message'] .= '账号已禁用';
			return $result;
		}
		// 一切判断全部通过，开始准备账号角色权限信息，用异常处理的方式
		try {
			// admin用户天生就是超级管理员
			$admin->admin_super = $admin->admin_name=='admin' ? 1 : $admin->admin_super;
			// 对超级管理员手动配置角色，对非超级管理员通过数据表关联角色
			$role = $admin->admin_super ? new RoleModel(['role_name'=>'超级管理员']) : $admin->role;
			// 根据角色关联菜单，超级管理手动设置菜单权限为null
			$menus = $admin->admin_super ? null : ($admin->role ? $admin->role->menus : null);
			// 将管理员、角色、菜单写入Session
			Session::set('admin_infor', $admin);	// 账号信息
			Session::set('admin_role', $role);	// 角色信息
			Session::set('admin_menus', $menus);	// 菜单权限
			// 把当前IP、时间、登录次数、新的密码更新到数据库中
			$admin->where(['admin_name'=>$admin['admin_name']])
				->setInc('admin_login_count');
			// 执行Db类的update不会触发修改器，所以要将密码做加密处理后再update
			$admin->where(['admin_name'=>$admin['admin_name']])
				->update([
					'admin_password'	=>	to_encrypt($data['admin_password'],$data['admin_name']),
					'admin_login_ip'	=>	get_client_ip(),
					'admin_login_time'	=>	time()
				]);
			// 返回登录成功信息
			$result['message'] = '登录成功';
			$result['status'] = true;
			$result['data'] = $admin;
		} catch(\Exception $e) {
			$result['message'] .= $e->getMessage();
		}
		return $result;
	}
}
