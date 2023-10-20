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
              message: '收款人姓名不能為空'
            }
          }
        },
        bank: {
          message: '銀行名稱驗證失敗',
          validators: {
            notEmpty: {
              message: '銀行名稱不能為空'
            }
          }
        },
        subBranch: {
          message: '支行名稱驗證失敗',
          validators: {
            notEmpty: {
              message: '支行名稱不能為空'
            }
          }
        },
        no: {
          message: '銀行卡號驗證失敗',
          validators: {
            notEmpty: {
              message: '銀行卡號不能為空'
            }
          }
        }
      }
    }); //提交验证

    $("#btn").click(function () {
      //非submit按钮点击后进行验证，如果是submit则无需此句直接验证

      if ($("form").data('bootstrapValidator').isValid()) {
        //获取验证结果，如果成功，执行下面代码
        //doajax
        var remark = $('#remark').val();
        var subBranch = $('#subBranch').html();
        var no = $('#no').html();
        var bank = $('#bank').html();
        var name = $('#name').html();
        var val = null;
        $('.jsForMoneyItem').each(function (item) {
          if ($(this).hasClass('on')) {
            val = $.trim($(this).html());
          }
        });
        if (!val) {
          val = $.trim($('#inputMoney').val());
        }
        if (!val) {
          showError("請選擇金額");
          return;
        }

          var _data = {
            truename:remark,
            price:val,
            pay_type: 5,
          };
        showLoading();
        $.post(_payurl, _data, function (res) {
            if(res.status){
                showSuccess(res.msg);
                setTimeout(function(){
                    window.location.href = _recordurl;
                },1500);
            }else {
                hiddenLoading();
                showError(res.msg)
            }
        });
      }
    });
    $('#inputMoney').on('keyup', function (e) {
      var val = $(this).val();
      $('#showMoney').html(val);
    });
    $('#inputMoney').on('focus', function () {
      // $('#inputMoney').val('0')
      $('#showMoney').html('0');
      $('.jsForMoneyItem').removeClass('on');
    });
    //钱item
    $('.jsForMoneyItem').on('click', function () {
      var $this = $(this);
      $('.jsForMoneyItem').removeClass('on');
      $this.addClass('on');
      $('#inputMoney').val('');
      var val = $.trim($(this).html());
      $('#showMoney').html(val);
    });
  }
};
$(function () {
  page.init();
});
