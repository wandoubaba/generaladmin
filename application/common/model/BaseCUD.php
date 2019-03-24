<?php
#
# 开启自动时间戳和软删除的基础模型
# 支持create_time、update_time、delete_time
#
namespace app\common\model;
use think\Model;
use traits\model\SoftDelete;

class BaseCUD extends Model
{
	// 导入软删除方法集
	use SoftDelete;
	// 开启自动写入时间戳 如果设置为字符串 则表示时间字段的类型
	protected $autoWriteTimestamp = true;
}