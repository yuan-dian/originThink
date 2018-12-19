<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace taglib;

use think\template\TagLib;

/**
 * CX标签库解析类
 * @category   Think
 * @package  Think
 * @subpackage  Driver.Taglib
 * @author    liu21st <liu21st@gmail.com>
 */
class Form extends Taglib
{

    // 标签定义
    protected $tags = [
        'select' => ['attr' => 'name,list,value,option', 'close' => 0],
        'radio' => ['attr' => 'name,list,value,option', 'close' => 0],
        'checkbox' => ['attr' => 'name,list,value,option', 'close' => 0]
    ];

    /**
     * select 选择框
     * 格式：
     * {form:select name="think" list="['1'=>'a','2'=>'b']" value="$value" option="['class'=>'aa']"}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagSelect($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : '""';
        $list = isset($tag['list']) ? $tag['list'] : 'array()';
        if (isset($tag['value'])) {
            if (strpos($tag['value'], '$') !== false) {
                $value = $tag['value'];
            } else {
                $value = "'" . $tag['value'] . "'";
            }
        } else {
            $value = '""';
        }
        $value = 'isset(' . $value . ')?' . $value . ':""';
        $option = isset($tag['option']) ? $tag['option'] : '';
        $parseStr = '<?php 
        $name  = \'' . $name . '\'; 
        $list  = ' . $list . '; 
        $value = ' . $value . ';
        $tmp   ="";';
        if ($option) {
            $parseStr .= '
                $option = ' . $option . ' ;
                foreach ( $option as $k =>$v){
                  $tmp = $tmp.$k.\'="\'.$v.\'"\';
                }';
        }
        $parseStr .= '
        $html = \'<select name = "\'.$name.\'" \'.$tmp.\'>\';
        $options = array();
        foreach ( $list as $key => $val){
            $selected = \'\';
            if ( $value == $key ) {
                $selected = \'selected\';
            }
            $options[] = \'<option value="\'.$key.\'" \'.$selected.\'>\'.$val.\'</option>\';
        }
        $list = implode(\'\', $options);
        $html = $html.$list.\'</select>\';
        echo $html;
     ?>';
        return $parseStr;
    }

    /**
     * Radio 单选框
     * 格式：
     * {form:radio name="think" list="['1'=>'a','2'=>'b']" value="$value" option="['class'=>'aa']"}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagRadio($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : '""';
        $list = isset($tag['list']) ? $tag['list'] : 'array()';
        if (isset($tag['value'])) {
            if (strpos($tag['value'], '$') !== false) {
                $value = $tag['value'];
            } else {
                $value = "'" . $tag['value'] . "'";
            }
        } else {
            $value = '""';
        }
        $option = isset($tag['option']) ? $tag['option'] : '';
        $parseStr = '<?php 
            $name = \'' . $name . '\'; 
            $list = ' . $list . '; 
            $value = ' . $value . ';
            $tmp = "";';
        if ($option) {
            $parseStr .= '
                $option=' . $option . ';
                foreach ( $option as $k => $v){
                  $tmp = $tmp.$k.\' = "\'.$v.\'"\';
            }';
        }
        $parseStr .= '
            $radio = array();
            foreach ( $list as $key => $val){
                $checked = \'\';
                if ($value == $key) {
                    $checked = \'checked\';
                }
                $radio[] = \'<input type="radio" name="\'.$name.\'" value="\'.$key.\'" title="\'.$val.\'" \'.$checked.\' \'.$tmp.\'>\';
            }
            $list = implode(\'\', $radio);
            echo $list;
         ?>';
        return $parseStr;
    }

    /**
     * checkbox 复选框
     * 格式：
     * {form:checkbox name="think" list="['1'=>'a','2'=>'b']" value="$value" option="['class'=>'aa']"}
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagCheckbox($tag, $content)
    {
        $name = isset($tag['name']) ? $tag['name'] : '""';
        $list = isset($tag['list']) ? $tag['list'] : 'array()';
        if (isset($tag['value'])) {
            if (strpos($tag['value'], '$') !== false) {
                $value = $tag['value'];
            } else {
                $value = "'" . $tag['value'] . "'";
            }
        } else {
            $value = '""';
        }
        $option = isset($tag['option']) ? $tag['option'] : '';
        $parseStr = '<?php 
            $name = \'' . $name . '\'; 
            $list = ' . $list . '; 
            $value = ' . $value . ';
            $tmp = "";';
        if ($option) {
            $parseStr .= '
                $option = ' . $option . ';
                foreach ( $option as $k => $v){
                  $tmp = $tmp.$k.\' = "\'.$v.\'"\';
                }';
        }
        $parseStr .= '
            $radio = array();
            foreach ( $list as $key => $val){
                $checked = \'\';
                if ( $value == $key ){
                    $checked = \'checked\';
                }
                $radio[] = \'<input type="checkbox" name = "\'.$name.\'" value = "\'.$key.\'" title = "\'.$val.\'" \'.$checked.\' \'.$tmp.\'>\';
            }
            $list = implode(\'\', $radio);
            echo $list;
         ?>';
        return $parseStr;
    }
}
