<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 17/8/6
 * Time: 上午2:45
 */

namespace check\exception;

use think\exception\Handle;

class ApiHandleException extends Handle
{

    /**
     * http 状态码
     * @var int
     */
    public $httpCode = 500;

    public function render(\Exception $e)
    {

        if (config('app_debug') == true) {
            return parent::render($e);
        }
        if ($e instanceof ApiException) {
            $this->httpCode = $e->httpCode;
        }
        return show([], 0, $e->getMessage(), [], $this->httpCode);
    }
}