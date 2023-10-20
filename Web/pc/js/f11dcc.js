'use strict';

var page = {
    init: function init() {

        $('form').bootstrapValidator({
            //默认提示
            message: 'This value is not valid', // 表单框里右侧的icon
            submitHandler: function submitHandler(validator, form, submitButton) {
                // 表单提交成功时会调用此方法
                // validator: 表单验证实例对象
                // form jq对象 指定表单对象
                // submitButton jq对象 指定提交按钮的对象
            },
            fields: {
                name: {
                    message: '姓名驗證失敗',
                    validators: {
                        notEmpty: {
                            message: '姓名不能為空',
                        },
                    },
                },
                account: {
                    message: '賬號驗證失敗',
                    validators: {
                        notEmpty: {
                            message: '賬號不能為空',
                        },

                    },
                },
                oldPassword: {
                    message: '原密碼驗證失敗',
                    validators: {
                        notEmpty: {
                            message: '原密碼不能為空',
                        },
                    },
                },
                newPassword: {
                    message: '新密碼驗證失敗',
                    validators: {
                        notEmpty: {
                            message: '新密碼不能為空',
                        },
                    },
                },
                conPassword: {
                    message: '確認密碼驗證失敗',
                    validators: {
                        notEmpty: {
                            message: '確認密碼不能為空',
                        },
                        identical: { //比较是否相同
                            field: 'newPassword', //需要进行比较的input name值
                            message: '確認密碼跟新密碼不一致',
                        },
                    },
                },
            },
        }); //提交验证

        $('#btn').click(function () {
            //非submit按钮点击后进行验证，如果是submit则无需此句直接验证

            if ($('form').data('bootstrapValidator').isValid()) {
                //获取验证结果，如果成功，执行下面代码
                //doajax
                var name = $.trim($('#name').val());
                var account = $.trim($('#account').val());
                var oldPassword = $.trim($('#oldPassword').val());
                var newPassword = $.trim($('#newPassword').val());
                var conPassword = $.trim($('#conPassword').val());
                var _data = {
                    loginpwd: oldPassword,
                    password: newPassword,
                    cpassword: conPassword,
                };
                showLoading();
                $.post(_url, _data, function (res) {
                    if (res.type != 1) {
                        hiddenLoading();
                        showError(res.data);
                    } else {
                        showSuccess(res.data);
                        setTimeout(function () {
                            window.location.reload();
                        }, 1500);
                    }
                });
            }
        });
    },
};
$(function () {
    page.init();
});
