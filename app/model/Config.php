<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/11/28
 * Time: 15:16
 */

namespace app\model;

use think\Model;

class Config extends Model
{
    protected $autoWriteTimestamp = true;
    protected $json = ['value'];
    protected $jsonAssoc = true;
}