<?php
namespace app\common\controller;

use think\Controller;
use app\admin\model\Config as ConfigModel;

class General extends Controller
{
	public function _initialize()
	{
		parent::_initialize();
		// 整站title, keywords, description
		$this->view->assign('title', $this->get_config('title'));
		$this->view->assign('keywords', $this->get_config('keywords'));
		$this->view->assign('description', $this->get_config('description'));
		// 备案号
		$this->view->assign('beian', $this->get_config('beian'));
		// 统计代码
		$this->view->assign('tongji', $this->get_config('tongji'));
	}

	/**
	 * 根据键名取得系统配置项的值
	 * @param  [type]  $key                    [指定config_key]
	 * @param  boolean $use_html_entity_decode [是否对结果进行html_entity_decode操作，默认为true]
	 * @return [type]                          [description]
	 */
    protected function get_config($key, $use_html_entity_decode=true)
    {
        $value = '';
        try{
            $config = ConfigModel::get(['config_key'=>$key]);
            $value = $use_html_entity_decode ? html_entity_decode($config->config_value) : $config->config_value;
        } catch (\Exception $e) {
            $value = $e->getMessage();
        }
        return $value;
    }
}