<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Config as ConfigModel;
use think\Loader;

class Config extends Base
{
	public function config()
	{
		$this->view->assign('pagetitle', '系统配置');
		$config = ConfigModel::all(function($query) {
			$query->order(['config_deletable'=>'asc','config_name'=>'asc']);
		});
		$this->view->assign('list',$config);
		return $this->view->fetch();
	}

	/**
	 * 对现有配置项进行修改
	 * @return [type] [description]
	 */
	public function do_config_edit()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
		$result['data'] = input();
		
		$config = new ConfigModel;
		$validate = Loader::validate('Config');
		if(!$validate->check($result['data'])) {
			$result['message'] = $validate->getError();
			return $result;
		}
		try {
			$result['rows'] = $config->isUpdate(true)->allowField(true)->save([
				'config_value'		=>	$result['data']['config_value'],
			], ['config_name'=>$result['data']['config_name'],'config_key'=>$result['data']['config_key']]);
			if($result['rows']) {
				$result['status'] = true;
				$result['message'] = '操作成功';
			}
		} catch(\Exception $e) {
			$result['message'] = $e->getMessage();
		}
		return $result;
	}

	/**
	 * 添加新的配置项
	 * @return [type] [description]
	 */
	public function do_config_add()
	{
		$result = ['status'=>false,'message'=>'操作失败','data','rows'=>0];
		$result['data'] = input();
		
		$config = new ConfigModel;
		$validate = Loader::validate('Config');
		if(!$validate->check($result['data'])) {
			$result['message'] = $validate->getError();
			return $result;
		}
		try {
			$result['rows'] = $config->isUpdate(false)->allowField(true)->save([
				'config_name'		=>	$result['data']['config_name'],
				'config_key'		=>	$result['data']['config_key'],
				'config_value'		=>	$result['data']['config_value'],
			]);
			if($result['rows']) {
				$result['status'] = true;
				$result['message'] = '操作成功';
			}
		} catch(\Exception $e) {
			$result['message'] = $e->getMessage();
		}
		return $result;
	}

	/**
	 * 删除配置项，依据config_key
	 * @return [type] [description]
	 */
	public function do_config_delete()
	{
		$result = ['status'=>false,'message'=>'操作失败：','data','rows'=>0];
		$key = input('?key') ? input('key') : '';
		$config = ConfigModel::get(['config_key'=>$key]);
		$result['data'] = $config;
		if(empty($config)) {
			$result['message'] .= "参数错误";
			return $result;
		}
		if($config->config_deletable==0) {
			$result['message'] .= "不允许删除";
			return $result;
		}
		try {
			$result['rows'] = ConfigModel::where(['config_key'=>$key])->delete();	// 执行真删除
			$result['status'] = true;
			$result['message'] = '操作成功';
		} catch(\Exception $e) {
			$result['message'] .= $e->getMessage();
		}
		return $result;
	}
}