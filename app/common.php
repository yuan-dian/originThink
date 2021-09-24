<?php
// 应用公共文件

if (!function_exists('sign')) {
    function sign($data)
    {
        // 数据类型检测
        if (!is_array($data)) {
            $data = (array)$data;
        }
        ksort($data); // 排序
        $code = http_build_query($data); // url编码并生成query字符串
        $sign = sha1($code); // 生成签名
        return $sign;
    }
}

/**
 * 将返回的数据集转换成树
 * @param array $list 数据集
 * @param string $pk 主键
 * @param string $pid 父节点名称
 * @param string $child 子节点名称
 * @param integer $root 根节点ID
 * @return array          转换后的树
 */
if (!function_exists('list_to_tree')) {
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}

if (!function_exists('get_user_id')) {
    function get_user_id()
    {
        return session('user_auth.uid');
    }
}

function alert_error($msg = '', $url = null, $time = 3)
{
    if (is_null($url)) {
        $url = 'parent.location.reload();';
    } else {
        $url = 'parent.location.href=\'' . $url . '\'';
    }
    if (request()->isAjax()) {
        $str = [
            'code' => 0,
            'msg' => $msg,
            'url' => $url,
            'wait' => $time,
        ];
        $response = think\Response::create($str, 'json');
    } else {
        $str = '<script type="text/javascript" src="/layui/layui.js"></script>';
        $str .= '<script>
                    layui.use([\'layer\'],function(){
                       layer.msg("' . $msg . '",{icon:"5",time:' . ($time * 1000) . '},function() {
                         ' . $url . '
                       });
                    })
                </script>';
        $response = think\Response::create($str, 'html');
    }
    return $response;
}

function show($data, $code = 1, $msg = '', $param = [], $httpCode = 200)
{
    $json = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];
    if ($param) {
        $json = array_merge($json, $param);
    }
    return json($json, $httpCode);
}
