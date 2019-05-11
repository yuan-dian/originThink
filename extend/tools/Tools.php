<?php
/**
 * Created by originThink
 * Date: 2019/5/11
 * Time: 17:09
 * Author: 原点 467490186@qq.com
 */

namespace tools;

use tools\office\XLSXWriter;

class Tools
{
    /**
     * 导出Excel文件
     * @param $data 需要导出的数据
     * @param array $header 标题头
     * $header 示例（标题=>数据格式） array(
    'c1-text'=>'string',//text
    'c2-text'=>'@',//text
    'c3-integer'=>'integer',
    'c4-integer'=>'0',
    'c5-price'=>'price',
    'c6-price'=>'#,##0.00',//custom
    'c7-date'=>'date',
    'c8-date'=>'YYYY-MM-DD',
    );
     * @param string $filename 文件名
     */
    public static function download_excel($data, $header = [], $filename = 'output.xlsx')
    {
        header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer = new XLSXWriter();
        if ($header) {
            $writer->writeSheetHeader('Sheet1', $header);
        }
        $writer->writeSheet($data);
        $writer->writeToStdOut();
        exit;
    }

}