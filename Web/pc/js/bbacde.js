'use strict';

var page = {
  init: function init() {

    $("form").bootstrapValidator({
      //默认提示
      message: 'This value is not valid',
      // 表单框里右侧的icon
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
              message: '姓名不能為空'
            }
          }
        },
        cp_extra1: {
            message: _cp_extra1_name + '驗證失敗',
            validators: {
                notEmpty: {
                    message: _cp_extra1_name + '不能為空'
                }
            }
        },
        account: {
          message: '賬號驗證失敗',
          validators: {
            notEmpty: {
              message: '賬號不能為空'
            }
          }
        },
        password: {
          message: '密碼驗證失敗',
          validators: {
            notEmpty: {
              message: '密碼不能為空'
            }
          },
          stringLength: { //长度限制
            min: 6,
            max: 18,
            message: '密碼長度必須在6到18位之間'
          }
        },
        confirmPwd: {
          message: '確認密碼驗證失敗',
          validators: {
            notEmpty: {
              message: '確認密碼不能為空'
            },
            identical: { //比较是否相同
              field: 'password', //需要进行比较的input name值
              message: '確認密碼跟密碼不一致'
            }
          }
        },
        withdrawal: {
          message: '提現密碼驗證失敗',
          validators: {
            notEmpty: {
              message: '提現密碼不能為空'
            }
          },
          stringLength: { //长度限制
            min: 6,
            max: 18,
            message: '提現密碼長度必須在6到18位之間'
          }
        }

      }
    }); //提交验证

    $("#btn").click(function () {
      //非submit按钮点击后进行验证，如果是submit则无需此句直接验证

      if ($("form").data('bootstrapValidator').isValid()) {
        //获取验证结果，如果成功，执行下面代码
        //   if(!$('#agree').is(':checked')){
        //       return showError('请同意用户协议');
        //   }
        //doajax
        var invitCode = $.trim($('#invitCode').val());
        var withdrawal = $.trim($('#withdrawal').val());
        var confirmPwd = $.trim($('#confirmPwd').val());
        var password = $.trim($('#password').val());
        var account = $.trim($('#account').val());
        var name = $.trim($('#name').val());
        var utel = $.trim($('#utel').val());
        var scard = $.trim($('#scard').val());
        var cp_extra1_ele = $('#cp_extra1');
        var cp_extra1 = '';
        if(account.length<5 || account.length>16){
			return showError('使用者名稱為5-16位數字或字母組成');
			return; 
		}
        if (!/(^[A-Za-z0-9]+$)/.test(account)) {
            return showError('會員賬號只能包含字母和數字');
          return;
        }
        if(!invitCode && _inviteNeed == '1'){
          return showError(_msg_invite_need);
        }
        if(!scard && _registerIdNeed == '1'){
          return showError(_msg_register_id);
        }
          if (cp_extra1_ele.length > 0) {
              cp_extra1 = $.trim(cp_extra1_ele.val());
              if (!cp_extra1) {
                  showError(cp_extra1_ele.attr('placeholder'));
                  return;
              }
          }
        var _data = {
            nickname:name,
            username:account,
            utel:utel,
            scard:scard,
            upwd:password,
            upwd2:confirmPwd,
            epwd:withdrawal,
            oid:invitCode
        };
        if(cp_extra1){
            _data.cp_extra1 = cp_extra1; 
        }
        showLoading();
        $.post(_regurl, _data, function (res) {
            if(res.type != 1){
                hiddenLoading();
                showError(res.data);
            }else{
              showSuccess(_msg_reg_success);
              setTimeout(function(){
                  window.location.href = _loginurl;
              },1500);
            }
        });
      }
    });
  }
};
$(function () {
  page.init();
});
