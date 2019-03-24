<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Db;

class Home extends Base
{
    public function index()
    {
    	$mysqlversion = Db::query('select version() as ver;')[0]['ver'];
        $this->view->assign('mysqlversion',$mysqlversion);
    	return $this->view->fetch();
    }
}
