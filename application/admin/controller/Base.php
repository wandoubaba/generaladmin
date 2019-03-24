<?php
namespace app\admin\controller;

use app\common\controller\General;
use think\Controller;
use think\Session;
use think\Request;
use app\admin\model\Menu as MenuModel;
use app\admin\model\AdminLog as AdminLogModel;


class Base extends General
{
	public function _initialize()
	{
		parent::_initialize();
        // 检查访问权限
        $this->check_authority();
        // 初始化后台菜单
        $this->view->assign('menu_list', $this->get_menu_list(true));
        // 后台标题等参数
        $this->view->assign('title', $this->get_config('admin_title'));
        $this->view->assign('keywords', $this->get_config('admin_keywords'));
        $this->view->assign('description', $this->get_config('admin_description'));
        $this->view->assign('admin_footer', $this->get_config('admin_footer'));
	}

    /**
     * 检查用户权限
     * @return [type] [description]
     */
    protected function check_authority()
    {
        /**
         * 检测当前操作模块与用户权限是否对应
         * 如果不对应，则跳转至用户首页
         * admin/Home控制器允许所有已登录用户访问
         * admin/Index控制器允许所有用户访问
         * 其他控制器中的方法需要有对应权限才可以访问
         */
        
        // 获取当前正访问的模块、控制器、方法，全部转换为小写
        $path_now = [
            strtolower(Request::instance()->module()),
            strtolower(Request::instance()->controller()),
            strtolower(Request::instance()->action())
        ];
        // 组合“模块/控制器字符串”
        $mc = $path_now[0].'/'.$path_now[1];
        // 设定权限检查的验证结果，默认为false
        $permissible = false;
        $url = url('admin/home/index');
        $msg = '没有权限';
        if( !Session::has('admin_infor') ) {
            // 未登录用户只能访问admin模块中的Index控制器
            if( $path_now[0]!='admin' || $path_now[1]!='index' ) {
                $msg = '请先登录';
                $url = url('admin/index/index');
                // $this->error('请先登录',url('admin/index/index'));
            }
        } else {
            // 先区分“模块/控制器”字符串
            switch ($mc) {
                case 'admin/index':
                    // admin模块中的Index控制器无条件允许访问
                case 'admin/home':
                    // admin模块中的Myself控制器对已登录用户无条件允许访问
                case 'admin/myself':
                    // admin模块中的Self控制器对已登录用户无条件允许访问
                    $permissible = true;
                    break;
                default:
                    $admin = Session::get('admin_infor');   // 读出session中的admin_infor
                    $role = Session::get('admin_role');     // 读出session中的admin_role
                    $menus = Session::get('admin_menus');   // 读出session中的admin_menu
                    if($admin->admin_super) {
                        // admin_super位为1或名称不admin的用户具备一切权限
                        $permissible = true;
                    } else {
                        // 在admin_menus中查询当前要访问的url，如果存在说明有权限
                        if($menus) {
                            foreach($menus as $menu) {
                                // 把数据库中记录的菜单路由字符串拆分为数组
                                $path = explode("/", strtolower($menu->menu_route));
                                // 用array_diff方法比较权限与当前访问路径
                                if(!empty($menu->menu_route) && array_diff($path_now,$path)==array()) {
                                    // 权限数组和当前访问路径相同时，array_diff($path_now,$path)==array()返回true
                                    $permissible = true;    // 有权限访问，将验证标志位设为true
                                }
                            }
                        }
                    }
                    break;
            }
        }
        /**
         * 记录操作日志到数据库
         * TODO: 对密码进行处理
         */
        $adminlog = new AdminLogModel;
        $adminlog->data([
            'admin_name'        =>  Session::has('admin_infor')?Session::get('admin_infor')->admin_name:'',
            'log_path'          =>  Request::instance()->path(),
            'log_ip'            =>  get_client_ip(),
            'log_input'         =>  stripos(serialize(input()),'password')?'{hided}':serialize(input()),    // 如果数据项中有密码，则不对数据进行记录
            'log_permissible'   =>  $permissible,
        ]);
        $adminlog->save();
        if(!$permissible) {
            // 无权访问则跳转到后台首页
            $this->error($msg, $url);
            // exit;
        }
        return $permissible;
    }

	/**
     * 根据用户权限初始化菜单的方法
     * @param  integer $visible 默认为true时仅查询可见菜单，false查询全部菜单
     * @return [type]           返回菜单和权限集合
     */
	protected function get_menu_list($visible=false)
    {
        // 默认false查询可见和隐藏菜单，当参数为true时则查询可见菜单
        $vi_rule = $visible ? ['menu_visible'=>1] : [];
        $id_rule = 'in';    // 默认用where menu_id in ()的方式查询菜单
        if(Session::get('admin_infor')->admin_super) {
            // 对于超级管理员来说就用where menu_id not in (null)的方式查询全部菜单
            $id_rule = 'not in';
        }
        // 判断是否有菜单权限
        $menuids = Session::has('admin_menus') ? array_column(Session::get('admin_menus'), 'menu_id') : [];

		// 在有权限的ID中查询“显示”的父级清单
    	$list = MenuModel::where($vi_rule)
            ->where(['menu_father_id'=>0])
            ->where(['menu_id'=>[$id_rule, $menuids]])
            ->order(['menu_visible'=>'desc','menu_sn'=>'asc','menu_route'=>'asc'])
            ->select();
    	// 遍历父级菜单，1：取得对应的子菜单，2：取得对应的角色
    	foreach($list as $n=>$val) {
            // 声明一个空数组用于保存可访问的角色
            $role_list_father = $id_rule == 'not in' ? $val->roles : [];
            if(empty($role_list_father)) {
                // 遍历当前菜单项对应的所有角色，要从中找出允许访问的并保存到role_list_father中
                foreach($val->roles as $role_val) {
                    $menu_ids_1 = array_column($role_val->menus,'menu_id'); // 数据库中角色可访问的菜单id
                    $menu_ids_2 = array_column(Session::get('admin_menus'), 'menu_id'); // 这个账号可访问的菜单id
                    // 两个id数组取差集，如果差集为空，说明这个角色是可访问的
                    if(empty(array_diff($menu_ids_1, $menu_ids_2))) {
                        // 把角色保存到数组中
                        array_push($role_list_father,$role_val);
                    }
                }
            }
            // 遍历角色完成，把可访问的角色数据赋值给list
            $list[$n]['role'] = $role_list_father;
            // 准备查询当前父菜单下的子菜单
    		$child = MenuModel::where($vi_rule)
                ->where(['menu_father_id'=>$val['menu_id']])
                ->where(['menu_id'=>[$id_rule, $menuids]])
                ->order(['menu_visible'=>'desc','menu_sn'=>'asc','menu_route'=>'asc'])
                ->select();
    		if($child) {
                // 如果包含子菜单，开始遍历每一个子菜单，去处理角色
                foreach($child as $m=>$child_val) {
                    // 声明一个子菜单允许角色的数级
                    $role_list_child = $id_rule == 'not in' ? $child_val->roles : [];
                    if(empty($role_list_child)) {
                        // 遍历每一个子菜单对应的角色
                        foreach($child_val->roles as $role_val) {
                            $menu_ids_1 = array_column($role_val->menus,'menu_id'); // 数据库中角色可访问的菜单id
                            $menu_ids_2 = array_column(Session::get('admin_menus'), 'menu_id'); // 这个账号可访问的菜单id
                            // 两个id数组取差集，如果差集为空，说明这个角色是可访问的
                            if(empty(array_diff($menu_ids_1, $menu_ids_2))) {
                                // 把角色保存到数组中
                                array_push($role_list_child,$role_val);
                            }
                        }
                    }
                    // 遍历完成，把可访问角色赋值给子菜单
                    $child[$m]['role'] = $role_list_child;
                }
	    	}
            // 将子菜单附到父菜单列表中
            $list[$n]['menu_child']=$child;
    	}
    	// 对unset后的数组要重建索引（不做也可以）
    	$list = array_values($list);
        return $list;
    }
}