<?php
/**
 * Created by originThink
 * Author: 原点 467490186@qq.com
 * Date: 2018/9/3
 * Time: 9:36
 */

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
     * value 如果是表达式：可以在表达式前面加冒号（:）;示例：{form:select name="group_id" list="$grouplist" value = ":isset($list['group_id']) ? $list['group_id'] :1" }
     * @param $tag
     * @param $content
     * @return string
     */
    public function tagSelect($tag, $content)
    {
        $tag_select_name = isset($tag['name']) ? $tag['name'] : '""';
        $tag_select_list = isset($tag['list']) ? $tag['list'] : 'array()';
        $tag_select_value = isset($tag['value']) ? $tag['value'] : '';
        $tag_select_default = isset($tag['default']) ? $tag['default'] : 'array()';
        $tag_select_option = isset($tag['option']) ? $tag['option'] : '';
        if ($tag_select_value) {
            if (strpos($tag_select_value, '$') === false) {
                $tag_select_value = "'" . $tag_select_value . "'";
            } elseif (strpos($tag_select_value, ':') === 0) {
                $tag_select_value = trim($tag_select_value, ':');
            } else {
                $tag_select_value = 'isset(' . $tag_select_value . ' ) ? ' . $tag_select_value . ' : ""';
            }
        } else {
            $tag_select_value = '""';
        }
        $parseStr = '
        <?php 
            $tag_select_name  = \'' . $tag_select_name . '\'; 
            $tag_select_list  = ' . $tag_select_list . '; 
            $tag_select_value = ' . $tag_select_value . ';
            $tag_select_default = ' . $tag_select_default . ';
            $tmp   ="";';
        if ($tag_select_option) {
            $parseStr .= '
            $tag_select_option = ' . $tag_select_option . ' ;
            foreach ( $tag_select_option as $k =>$v){
              $tmp .= " ".$k.\'="\'.$v.\'"\';
            }';
        }
        $parseStr .= '
            $select_tem_html = \'<select name = "\'.$tag_select_name.\'" \'.$tmp.\'>\';
            $select_tem_options = array();
            if ($tag_select_default) {
                foreach ( $tag_select_default as $k => $v){
                    $select_tem_options[] = \'<option value="\'.$k.\'">\'.$v.\'</option>\';
                }
            }
            foreach ( $tag_select_list as $key => $val){
                $selected = \'\';
                if(is_array($tag_select_value)){
                    if(in_array($key,$tag_select_value)) $selected = \'selected\';
                } else {
                    if ( $tag_select_value == $key )  $selected = \'selected\';
                }
              
                $select_tem_options[] = \'<option value="\'.$key.\'" \'.$selected.\'>\'.$val.\'</option>\';
            }
            $select_tem_html = $select_tem_html.implode(\'\', $select_tem_options).\'</select>\';
            echo $select_tem_html;
            unset($select_tem_options);
            unset($select_tem_html);
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
        $tag_radio_name = isset($tag['name']) ? $tag['name'] : '';
        $tag_radio_list = isset($tag['list']) ? $tag['list'] : 'array()';
        $tag_radio_value = isset($tag['value']) ? $tag['value'] : '';
        $tag_radio_default = isset($tag['default']) ? $tag['default'] : 'array()';
        if ($tag_radio_value) {
            if (strpos($tag_radio_value, '$') === false) {
                $tag_radio_value = "'" . $tag_radio_value . "'";
            }elseif (strpos($tag_radio_value, ':') === 0) {
                $tag_radio_value = trim($tag_radio_value, ':');
            } else {
                $tag_radio_value = 'isset(' . $tag_radio_value . ') ? ' . $tag_radio_value . ': ""';
            }
        } else {
            $tag_radio_value = '""';
        }
        $tag_radio_option = isset($tag['option']) ? $tag['option'] : '';

        $parseStr = '
            <?php 
                $tag_radio_name = \'' . $tag_radio_name . '\'; 
                $tag_radio_list = ' . $tag_radio_list . '; 
                $tag_radio_value = ' . $tag_radio_value . '; 
                $tag_radio_default = ' . $tag_radio_default . '; 
                $tmp = "";';
        if ($tag_radio_option) {
            $parseStr .= '
                $tag_radio_option=' . $tag_radio_option . ';
                foreach ( $tag_radio_option as $k => $v){
                  $tmp .= " ".$k.\' = "\'.$v.\'"\';
            }';
        }
        $parseStr .= '
            $radio = array();
            if ($tag_radio_default) {
                foreach ( $tag_radio_default as $k => $v){
                    $radio[] = \'<input type="radio" name="\'.$tag_radio_name.\'" value="\'.$v.\'" title="\'.$val.\'" \'.$tmp.\'>\';
                }
            }
            foreach ( $tag_radio_list as $key => $val){
                $checked = \'\';
                if ($tag_radio_value == $key) {
                    $checked = \'checked\';
                }
                $radio[] = \'<input type="radio" name="\'.$tag_radio_name.\'" value="\'.$key.\'" title="\'.$val.\'" \'.$checked.\' \'.$tmp.\'>\';
            }
            $radio_html = implode(\'\', $radio);
            echo $radio_html;
            unset($radio_html,$radio);
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
        $tag_checkbox_name = isset($tag['name']) ? $tag['name'] : '';
        $tag_checkbox_list = isset($tag['list']) ? $tag['list'] : 'array()';
        $tag_checkbox_value = isset($tag['value']) ? $tag['value'] : '';
        $tag_checkbox_default = isset($tag['default']) ? $tag['default'] : 'array()';
        if ($tag_checkbox_value) {
            if (strpos($tag_checkbox_value, '$') === false) {
                $tag_checkbox_value = "'" . $tag_checkbox_value . "'";
            }elseif (strpos($tag_checkbox_value, ':') === 0) {
                $tag_checkbox_value = trim($tag_checkbox_value, ':');
            } else {
                $tag_checkbox_value = 'isset(' . $tag_checkbox_value . ') ?' . $tag_checkbox_value . ': ""';
            }
        } else {
            $tag_checkbox_value = "''";
        }
        $tag_checkbox_option = isset($tag['option']) ? $tag['option'] : '';
        $parseStr = '<?php 
            $tag_checkbox_name = \'' . $tag_checkbox_name . '\'; 
            $tag_checkbox_list = ' . $tag_checkbox_list . '; 
            $tag_checkbox_value = ' . $tag_checkbox_value . '; 
            $tag_checkbox_default = ' . $tag_checkbox_default . '; 
            $tmp = "";';
        if ($tag_checkbox_option) {
            $parseStr .= '
                $tag_checkbox_option = ' . $tag_checkbox_option . ';
                foreach ( $tag_checkbox_option as $k => $v){
                  $tmp .= " ".$k.\' = "\'.$v.\'"\';
                }';
        }
        $parseStr .= '
            $checkbox = array();
            if ($tag_checkbox_default) {
                foreach ( $tag_checkbox_default as $k => $v){
                    $checkbox[] = \'<input type="checkbox" name="\'.$tag_checkbox_name.\'" value="\'.$k.\'" title="\'.$v.\'" \'.$tmp.\'>\';
                }
            }
            foreach ( $tag_checkbox_list as $key => $val){
                $checked = \'\';
                if(is_array($tag_checkbox_value)){
                    if(in_array($key,$tag_checkbox_value)) $checked = \'checked\';
                } else {
                    if ( $tag_checkbox_value == $key )  $checked = \'checked\';
                }
                $checkbox[] = \'<input type="checkbox" name = "\'.$tag_checkbox_name.\'" value = "\'.$key.\'" title = "\'.$val.\'" \'.$checked.\' \'.$tmp.\'>\';
            }
            $checkbox_html = implode(\'\', $checkbox);
            echo $checkbox_html;
            unset($checkbox_html,$checkbox);
         ?>';
        return $parseStr;
    }
}
