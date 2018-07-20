/**
 * Created by Administrator on 2017/5/4.
 */
/**
 * ajax表单提交
 * @param $form_node //jquery form表单
 * @param $form_data //表单数据, 如果$form_node不为空，则忽略此参数
 * @param $form_action //提交地址，如果$form_node不为空，则忽略此参数
 * @param $function //提示结束后执行的操作
 */
function ajaxSubmitAct() {
    var $form_node = arguments[0];
    var $form_data = $form_node == '' ? arguments[1] : $form_node.serialize();
    var $form_action = $form_node == '' ? arguments[2] : $form_node.attr('action');
    var $function = typeof(arguments[3]) != "undefined" ? arguments[3] : function () {
    };
    $.ajax({
        url: $form_action,
        type: 'POST',
        dataType: 'json',
        data: $form_data,
        success: function (data) {
            layer.msg(data.info, {time: 1000},  function () {
                $function
            });
        },
        error: function (data, status, e) {
            alert(data.info);
        }
    });
}