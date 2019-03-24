<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Role as RoleModel;
use app\admin\model\RoleMenu as RoleMenuModel;
use think\Request;
use think\Loader;
use think\Db;
use think\Session;

class Menu extends Base
{
	// 渲染“菜单列表”页模板
    public function menu_list()
    {
    	$this->view->assign('pagetitle', '菜单列表');
        // 调用Base中的menu_init方法获取权限内的菜单
        $list = $this->get_menu_list(false);
        // 如果是普通管理员，取权限中的菜单数量，如果是超级管理员，取数据表中的记录总数
        $count = Session::has('admin_menus')? sizeof(Session::get('admin_menus')) : MenuModel::count();
        // 将count和list分配到页面中
        $this->view->assign('count', $count);
    	$this->view->assign('list',$list);

    	return $this->view->fetch();
    }

    // 渲染“添加菜单”页模板
    public function menu_add()
    {
    	$this->view->assign('pagetitle','添加菜单');
    	// 获取传递的fid参数，如果没有，则默认为0
    	$fid = input('?fid') ? input('fid') : 0;
    	// 把fid输出到页面，用于和列表项比较
    	$this->view->assign('fid',$fid);
    	// 准备父级菜单列表
    	$list = MenuModel::where(['menu_father_id'=>0])->order(['menu_sn'=>'asc'])->select();
    	// 将父级菜单数据输出到页面
    	$this->view->assign('list',$list);

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
            }
        }
        $this->view->assign('role_list', $role);

    	return $this->view->fetch();
    }

    // 渲染“编辑菜单”页模板
    public function menu_edit()
    {
    	$this->view->assign('pagetitle','编辑菜单');
    	// 取得传入的id
    	$id = input('?id') ? input('id') : 0;
    	$menu = MenuModel::get($id);
    	if($menu) {
    		// 准备父级菜单列表
    		$list = MenuModel::where(['menu_father_id'=>0])->order(['menu_sn'=>'asc'])->select();
    		$this->view->assign('list',$list);
            // 准备菜单角色列表
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
                }
            }
            $this->view->assign('role_list', $role);
            // 准备回填菜单角色
            $role_menu = RoleMenuModel::where(['menu_id'=>$id])->select();
            // 用array_column只取出$role_menu中的role_id列组成的数组
            $role_ids = array_column($role_menu, 'role_id');
            $this->view->assign('role_ids', $role_ids);
    		// 回填其他菜单信息
    		$this->view->assign('menu',$menu->getData()); // 用getData取得未用获取器前的原始值
    	} else {
    		$this->error('参数错误，将转至“添加”页','admin/menuControl/menu_add');
    	}
    	return $this->view->fetch();
    }



    // 执行“添加菜单”操作
    public function do_menu_add()
    {
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$data = input();
    	$data['menu_visible'] = input('?menu_visible') ? 1 : 0;
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Menu');
    	// 要求如果是子菜单则必须填写路由
    	if($data['menu_father_id']>0) {
    		$validate->rule(['menu_route'=>'require'])->message(['menu_route'=>'子菜单的路由不能为空']);
    	}
		if($validate->check($data)) {
			$menu = new MenuModel($data);
			try{
				$result['rows'] = $menu->isUpdate(false)->allowField(true)->save();
				if($result['rows'] == 1) {
                    // 菜单插入成功，开始验证是否需要关联角色
                    $result['message'] = '<li>菜单添加成功</li>';
                    if( input('?role_id')) {
                        // 准备角色数据
                        $list = [];
                        foreach($data['role_id'] as $role_id) {
                            array_push($list, [
                                'role_id'=>$role_id,
                                'menu_id'=>$menu->menu_id
                            ]);
                        }
                        // 实例化角色菜单模型
                        $role_menu = new RoleMenuModel;
                        // 批量写入数据
                        $data['menu_id'] = $role_menu->saveAll($list);
                        // 设置反馈信息
                        $result['message'] .= '<li>菜单权限设置成功</li>';
                    }
				}
                $result['status'] = true;
			} catch(\Exception $e) {
				$result['status'] = false;
				$result['message'] = $e->getMessage();
			}
		} else {
			$result['message'] = $validate->getError();
		}
		return $result;
    }

    // 执行“编辑菜单”操作
    public function do_menu_edit()
    {
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	$data = input();
    	$data['menu_visible'] = input('?menu_visible') ? 1 : 0;
    	// 用验证器对数据进行校验
    	$validate = Loader::validate('Menu');
    	// 要求如果是子菜单则必须填写路由
    	if($data['menu_father_id']>0) {
    		$validate->rule(['menu_route'=>'require'])->message(['menu_route'=>'子菜单的路由不能为空']);
    	}
		if($validate->check($data)) {
			try{
                $menu = new MenuModel;
                // 更新菜单信息
				$result['rows'] = $menu->isUpdate(true)->allowField(true)->save($data,['menu_id'=>$data['menu_id']]);
				if($result['rows']) {
                    $result['message'] = '<li>菜单更新成功</li>';
                    // 实例化角色菜单模型
                    $role_menu = new RoleMenuModel;
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
                        }
                    }
                    $roleids = array_column($role,'role_id');
                    // 删掉所有本菜单的可访问角色
                    $role_menu->where(['menu_id'=>$data['menu_id'],'role_id'=>['in',$roleids]])->delete();
                    if(input('?role_id')) {
                        // 准备角色菜单数据
                        $list = [];
                        foreach($data['role_id'] as $val) {
                            array_push($list, ['menu_id'=>$data['menu_id'],'role_id'=>$val]);
                        }
                        // 批量写入数据
                        if($list) {
                            $role_menu->saveAll($list);
                        }
                    }
                    // 设置反馈信息
                    $result['message'] .= '<li>菜单角色设置成功</li>';
					$result['status'] = true;
				}
			} catch(\Exception $e) {
				$result['status'] = false;
				$result['message'] = $e->getMessage();
			}
            $result['data'] = $data;
		} else {
			$result['message'] = $validate->getError();
		}
		return $result;
    }

    public function do_menu_visible()
    {
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];

    	$id = input('id');	// 获取页面传入的authority_id
		// 用异常处理的方式执行以下操作
		try {
			$menu = MenuModel::get($id);	// 查询authority_id对应的记录
            $menu['menu_visible'] = $menu->getData('menu_visible')==1 ? 0 : 1;
            $result['rows'] = $menu->isUpdate(true)
                ->allowField(true)
                ->save(['menu_visible'=>$menu->getData('menu_visible')],['menu_id'=>$id]);
            if($result['rows']) {
                $result['message'] = $menu['menu_name'].'模块已切换为“'.$menu['menu_visible'].'”状态';	// 生成友好的提示信息
                $result['status'] = true;
            }
			$result['data'] = $menu;
		} catch(\Exception $e) {
			$result['message'] = $e->getMessage();	//将异常信息赋值给$message
		}
		return $result;
    }

    // 执行“删除菜单”操作
    public function do_menu_delete($id)
    {
    	$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
    	try {
    		$result['data'] = MenuModel::get($id);
    		if($result['data']) {
    			if(MenuModel::where(["menu_father_id"=>$id])->count()>0) {
    				$result['status'] = false;
    				$result['message'] = '有子菜单存在，不允许删除';
    			} else {
					$result['rows'] = MenuModel::destroy(['menu_id'=>$id]);	// 执行软删除
					$result['status'] = true;
					$result['message'] = '操作成功';
				}
			} else {
				$result['status'] = false;
				$result['message'] = '不存在该记录';
			}
		} catch(\Exception $e) {
			$result['message'] = $e->getMessage();	// 获取异常信息
		}
		return $result;
    }

    // 执行“批量删除”操作
    public function do_menu_multidelete()
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
					$result_i = $this->do_menu_delete($id);
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
                    $result['message'] .= "<br/>".$result['rows']." of ".count($ids)."条数据删除成功。";    // 人性化提示，反馈操作数量
                    $result['message'] .= "<br/>".(count($ids)-$result['rows'])." of ".count($ids)."条数据删除失败。";  // 人性化提示，反馈操作数量
				}
				
			} catch(\Exception $e) {
				// 如果操作有异常，反馈失败记录数量和错误信息
				$result['message'] .= (count($authority_ids)-count($deleted_ids))."条数据删除失败，错误信息：".$e->getMessage();
			}
		}
		return $result;
    }
}
