<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role as RoleModel;
use think\Request;
use think\Loader;
use think\Db;
use think\Session;

class Admin extends Base
{
	// 渲染管理员列表视图
	public function admin_list()
	{
		$this->view->assign('pagetitle','管理员管理');
		// 读取admin数据
		$adminlist = AdminModel::all();
		foreach($adminlist as $n=>$admin_val) {
			if(Session::get('admin_infor')->admin_super) {
				$adminlist[$n]['role'] = $admin_val->role;
			} else {
				// 不是超级管理员
				// 要过滤掉超级管理员和高权限管理员
				if($admin_val->admin_name=='admin'||$admin_val->admin_super) {
					// 超级管理员，过滤
					unset($adminlist[$n]);
				} else {
					if($admin_val->role) {
						if(!empty(array_diff(array_column($admin_val->role->menus, 'menu_id'), array_column(Session::get('admin_menus'), 'menu_id')))) {
							// 高权限，删
							unset($adminlist[$n]);
						}
					}
				}
			}
		}
		$this->view->assign('list',$adminlist);
		$this->view->assign('count',count($adminlist));
		// 渲染页面
		return $this->view->fetch();
	}

	// 渲染添加管理员视图
	public function admin_add()
	{
		$this->view->assign('pagetitle', '添加管理员');
		// 准备role列表
		$role = RoleModel::all();
		foreach($role as $n=>$val) {
			// array_column只获取对象数组中的menu_id列
			// array_diff返回(数组1-数组2)的差集
			if(Session::has('admin_menus') && !empty(array_diff(
				array_column($val->menus,'menu_id'), 
				array_column(Session::get('admin_menus'), 'menu_id')))
			) {
				// 过滤掉权限高于当前权限的角色
				unset($role[$n]);
			} else {
				$role[$n]['child'] = $val->menus;
			}
		}
		$this->view->assign('list',$role);
		
		return $this->view->fetch();
	}

	public function admin_edit()
	{
		$this->view->assign('pagetitle', '编辑管理员');

		$id = input('?id') ? input('id') : 0;
    	$admin = AdminModel::get($id);
    	if($admin) {
			// 准备role列表
			$role = RoleModel::all();
			foreach($role as $n=>$val) {
				// array_column只获取对象数组中的menu_id列
				// array_diff返回(数组1-数组2)的差集
				if(Session::has('admin_menus') && !empty(array_diff(
					array_column($val->menus,'menu_id'), 
					array_column(Session::get('admin_menus'), 'menu_id')))
				) {
					// 过滤掉权限高于当前权限的角色
					unset($role[$n]);
				} else {
					$role[$n]['child'] = $val->menus;
				}
			}
			$this->view->assign('list',$role);
			$this->view->assign('admin', $admin);
    	} else {
    		$this->error('参数错误，将转至“添加”页','admin/adminControl/admin_add');
    	}
    	return $this->view->fetch();
	}

	public function admin_password()
	{
		$this->view->assign('pagetitle','修改管理员密码');
		$id = input('?id') ? input('id') : 0;
		$admin = AdminModel::get($id);
		if(!$admin) {
			$this->error('参数错误');
			return;
		}
		$this->view->assign('admin',$admin);
		return $this->view->fetch();
	}

	/**
	 * 执行添加管理员操作
	 * @return [type] [description]
	 */
	public function do_admin_add()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$data = input();
    	$data['admin_status'] = input('?admin_status') ? 1 : 0;
    	$result['data'] = $data;
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$v = $validate->scene('init')->check($data);
		if($v) {
			$admin = new AdminModel;
			if(input('?admin_super') && $data['admin_super']) {
				$result['message'] .= '不允许创建超级管理员';
			} else {
				try{
					// 调用save($data)方法可触发修改器
					$result['rows'] = $admin->isUpdate(false)->allowField(true)->save($data);
					if($result['rows'] == 1) {
						$result['status'] = true;
						$result['message'] = '操作成功';
						$result['data'] = $admin;
					}
				} catch(\Exception $e) {
					$result['status'] = false;
					$result['message'] = $e->getMessage();
				}
			}
		} else {
			$result['message'] = $validate->getError();
		}
		return $result;
	}

	/**
	 * 执行编辑管理员的操作
	 * @return [type] [description]
	 */
	public function do_admin_edit()
	{
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$data = input();
    	$data['admin_status'] = input('?admin_status') ? 1 : 0;	// 单独处理checkbox数据
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$admin = AdminModel::get(['admin_id'=>$data['admin_id']]);
		$admin->admin_email = $data['admin_email'];
    	$admin->admin_telephone = $data['admin_telephone'];
    	$admin->admin_role_id = $data['admin_role_id'];
    	$admin->admin_status = $data['admin_status'];
    	$admin->admin_description = $data['admin_description'];
    	$v = $validate->scene('nopassword')->check($data);
    	$result['data'] = $admin;
		if($v) {
			try{
				$result['rows'] = $admin->allowField(true)->isUpdate(true)->save($admin,['admin_id'=>$data['admin_id']]);
				if($result['rows']) {
					$result['status'] = true;
					$result['message'] = '操作成功';
				}
			} catch(\Exception $e) {
				$result['status'] = false;
				$result['message'] = $e->getMessage();
			}
		} else {
			$result['message'] = $validate->getError();
		}    	
		return $result;
	}

	/**
	 * 执行修改管理员密码的操作
	 * @return [type] [description]
	 */
	public function do_admin_password()
	{
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$data = input();
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Admin');
    	$admin = AdminModel::get(['admin_id'=>$data['admin_id']]);
    	if($admin->admin_name=='admin' || $data['admin_name']=='admin') {
    		$result['message'] = '不允许操作admin用户';
    		$result['status'] = false;
    	} else {
    		$admin->admin_name = $data['admin_name'];
    		$admin->admin_password = $data['admin_password'];
	    	$v = $validate->scene('password')->check($data);
	    	$result['data'] = $admin;
			if($v) {
				try{
					$result['rows'] = $admin->allowField(true)->isUpdate(true)->save($admin,['admin_id'=>$data['admin_id']]);
					if($result['rows']) {
						$result['status'] = true;
						$result['message'] = '操作成功';
					}
				} catch(\Exception $e) {
					$result['status'] = false;
					$result['message'] = $e->getMessage();
				}
			} else {
				$result['message'] = $validate->getError();
			}
    	}    	
		return $result;
	}

	/**
	 * 执行切换管理员可用状态操作
	 * @param  [type] $id [管理员ID]
	 * @return [type]     [description]
	 */
	public function do_admin_status($id)
	{
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$id = input('id');	// 获取页面传入的id
		// 用异常处理的方式执行以下操作
		try {
			$admin = AdminModel::get($id);	// 查询id对应的记录
			$admin['admin_status'] = $admin->getData('admin_status')==1 ? 0 : 1;
			if( ($admin->admin_super==1||$admin->admin_name=='admin') && Session::get('admin_infor')['admin_name']!='admin') {
				$result['message'] .= ' 不允许对超级管理进行操作';
				$result['status'] = false;
			} else {
				$result['rows'] = $admin->isUpdate(true)->allowField(true)->save(['admin_status' => $admin->getData('admin_status')],['admin_id'=>$id]);
				if($result['rows']) {
					$result['message'] = $admin['admin_name'].'管理员已切换为“'.$admin['admin_status'].'”状态';	// 生成友好的提示信息
					$result['status'] = true;
				}
			}
			$result['data'] = $admin;
		} catch(\Exception $e) {
			$result['message'] .= $e->getMessage();	//将异常信息赋值给$message
		}
		return $result;
	}

	/**
	 * 执行删除管理员的操作
	 * @param  [type] $id [管理员ID]
	 * @return [type]     [description]
	 */
	public function do_admin_delete($id)
	{
		$result = ['status'=>false,'message'=>'操作失败：','data','rows'=>0];
    	try {
    		$result['data'] = AdminModel::get($id);
    		if($result['data']) {
    			if($result['data']['admin_super']||$result['data']['admin_name']=='admin') {
    				$result['status'] = false;
    				$result['message'] .= '不允许删除超级管理员';
    			} else {
					$result['rows'] = AdminModel::destroy(['admin_id'=>$id]);	// 执行软删除
					$result['status'] = true;
					$result['message'] = '操作成功';
				}
			} else {
				$result['status'] = false;
				$result['message'] .= '不存在该记录';
			}
		} catch(\Exception $e) {
			$result['message'] .= $e->getMessage();	// 获取异常信息
		}
		return $result;
	}

	/**
	 * 执行批量删除管理员的操作
	 * @return [type] [description]
	 */
	public function do_admin_multidelete()
	{
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    			// 取得前台提交的module_ids数组并保存到数组中
		$ids = input('?ids') ? input('ids/a') : [];
		// 后台也判断一下提交的module_id个数
		if(count($ids)==0) {
			// 如果为0，则直接返回提示信息给前台
			$result['message'] = '没有选中任何数据';
		} else {
			// 用异常处理机制执行以下操作
			try {
				// 遍历$module_ids数组中的元素
				foreach($ids as $id) {
					$result_i = $this->do_admin_delete($id);
					if($result_i['status']) {
						$result['rows'] += 1;
					} else {
						$result['message'].=" ".$result_i['message'];
					}
				}
				if(count($ids)==$result['rows']) {
					$result['status'] = true;
					$result['message'] = $result['rows']."条数据被成功删除";
				} else {
					$result['message'] .= "<br/>".$result['rows']." of ".count($ids)."条数据删除成功。";	// 人性化提示，反馈操作数量
					$result['message'] .= "<br/>".(count($ids)-$result['rows'])." of ".count($ids)."条数据删除失败。";	// 人性化提示，反馈操作数量
				}
				
			} catch(\Exception $e) {
				// 如果操作有异常，反馈失败记录数量和错误信息
				$result['message'] .= (count($authority_ids)-count($deleted_ids))."条数据删除失败，错误信息：".$e->getMessage();
			}
		}
		return $result;
	}
}