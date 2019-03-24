<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Role as RoleModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\RoleMenu as RoleMenuModel;
use think\Request;
use think\Loader;
use think\Session;

class Role extends Base
{
	// 渲染角色列表视图
	public function role_list()
	{
		$this->view->assign('pagetitle','角色管理');
		// 查询角色数据
		$role = RoleModel::all();
		// 用模型的多对多关联方式查询每个角色对应的菜单
		foreach($role as $n=>$val) {
			// array_column只获取对象数组中的menu_id列
			// array_diff返回(数组1-数组2)的差集
			if(Session::has('admin_menus') && array_diff(
				array_column($val->menus,'menu_id'), 
				array_column(Session::get('admin_menus'), 'menu_id'))
			) {
				// 过滤掉权限高于当前权限的角色
				unset($role[$n]);
			} else {
				$role[$n]['child'] = $val->menus;
			}
		}
		$role = array_values($role);	// 对数组重建索引（不做也可以）
		// 把记录数分配到页面
		$this->view->assign('count',count($role));
		// 把数据分配到页面
		$this->view->assign('list',$role);
		return $this->view->fetch();
	}

	// 渲染添加角色视图
	public function role_add()
	{
		$this->view->assign('pagetitle', '添加角色');
		// 查询父级菜单项
    	$list = $this->get_menu_list();
    	// 把菜单数据分配到页面
    	$this->view->assign('list',$list);

    	return $this->view->fetch();
	}

	// 渲染编辑角色视图
	public function role_edit()
	{
		$this->view->assign('pagetitle', '编辑角色');
		$id = input('id');
		$role = RoleModel::get($id);
		if($role) {
	    	$list = $this->get_menu_list();
	    	// 根据role_id查询对应的menu_id
	    	$menu_ids = RoleMenuModel::where(['role_id'=>$id])->column('menu_id');
	    	// 把菜单数据分配到页面
	    	$this->view->assign('list', $list);
	    	// 角色数据分配到页面
	    	$this->view->assign('role', $role);
	    	// 角色对应菜单数据回显到页面
	    	$this->view->assign('menu_ids', $menu_ids);
	    } else {
	    	$this->error('参数错误，将转至“添加”页','admin/menuControl/menu_add');
	    }
	    return $this->view->fetch();
	}


	// 执行添加角色操作
	public function do_role_add()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
		// 读取所有表单数据
    	$data = input();
    	$result['data'] = $data;
    	$v = $this->validate($data,'Role');
    	if(true!==$v) {
    		$result['message'] .= $v;
    	} else {
    		//数据验证通过，准备执行插入
    		$role = new RoleModel($data);
    		try{
    			// 插入role表
				$result['rows'] = $role->isUpdate(false)->allowField(true)->save();
				// 如果插入role表成功，则开始执行role_menu的遍历和写入
				$result['message'] = '<li>角色添加成功</li>';
				if($result['rows'] == 1 && input('?menu_id')) {
					// 角色插入成功，开始验证是否需要关联菜单
					// 准备菜单数据
					$list = [];
					foreach($data['menu_id'] as $menu_id) {
						array_push($list, [
							'role_id'=>$role->role_id,
							'menu_id'=>$menu_id
						]);
					}
					// 实例化角色菜单模型
					$role_menu = new RoleMenuModel;
					// 批量写入数据
					$data['menu_id'] = $role_menu->saveAll($list);
					// 设置反馈信息
					$result['message'] .= '<li>菜单权限设置成功</li>';
				}
				$result['status'] = true;
			} catch(\Exception $e) {
				$result['status'] = false;
				$result['message'] .= $e->getMessage();
			}
    	}
    	return $result;
	}

	// 执行编辑角色和角色菜单操作
	public function do_role_edit()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
		// 读取所有表单数据
    	$data = input();
    	$v = $this->validate($data,'Role');
    	if(true!==$v) {
    		$result['message'] = $v;
    	} else {
    		//数据验证通过，准备执行插入
    		$role = new RoleModel;
    		// 用异常处理的方式去执行数据操作
    		try{
    			// 保存角色数据
			 	$result['rows'] = $role->isUpdate(true)->allowField(true)->save($data,['role_id'=>$data['role_id']]);
				if($result['rows']) {
					// 角色更新成功，开始验证是否需要关联菜单
					$result['message'] = '<li>角色更新成功</li>';
					// 实例化角色菜单模型
					$role_menu = new RoleMenuModel;
					// 删掉所有本角色的菜单
					$role_menu->where('role_id','=',$data['role_id'])->delete();
					if(input('?menu_id')) {
						// 准备角色菜单数据
						$list = [];
						foreach($data['menu_id'] as $val) {
							array_push($list, ['role_id'=>$data['role_id'],'menu_id'=>$val]);
						}
						// 批量写入数据
						if($list) {
							$role_menu->saveAll($list);
						}
					}
					// 设置反馈信息
					$result['message'] .= '<li>菜单权限设置成功</li>';
					$result['status'] = true;
				}
			} catch(\Exception $e) {
				$result['status'] = false;
				$result['message'] .= $e->getMessage();
			}
			$result['data'] = $data;
    	}
    	return $result;
	}

	public function do_role_delete($id)
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	try {
    		$result['data'] = RoleModel::get($id);
    		RoleMenuModel::where(['role_id'=>$id])->delete();
    		RoleModel::where(['role_id'=>$id])->delete();
    		$result['status'] = true;
    		$result['message'] = '角色删除成功';
    	} catch(\Exception $e) {
    		$result['message'] .= $e->getMessage();
    	}
    	return $result;
	}

	public function do_role_multidelete()
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
				// 遍历$ids数组中的元素
				foreach($ids as $id) {
					$result_i = $this->do_role_delete($id);
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