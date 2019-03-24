<?php
#
# 开启自动时间戳，关闭update_time，不开启软删除的基础模型
# 支持create_time
# 不支持update_time、delete_time
#
namespace app\common\model;
use think\Model;

class BaseC extends Model
{
	// 开启自动写入时间戳 如果设置为字符串 则表示时间字段的类型
	protected $autoWriteTimestamp = true;
	// 关闭update_time字段
	protected $updateTime = false;
}