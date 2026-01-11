<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>{:GetVar('webtitle')}</title>
    <meta name="keywords" content="{:GetVar('keywords')}" />
    <meta name="description" content="{:GetVar('description')}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >

    <link rel="stylesheet" href="__CSS2__/bootstrap.min.css">
    <link rel="stylesheet" href="__CSS2__/reset.css">
    <link rel="stylesheet" href="__CSS2__/icon.css">
    <link rel="stylesheet" href="__CSS2__/header.css">
    <link rel="stylesheet" href="__CSS2__/userInfo.css">
    <link rel="stylesheet" href="__CSS2__/recharge.css">
    <link rel="stylesheet" href="__CSS2__/footer.css">
    <link rel="stylesheet" href="http://at.alicdn.com/t/font_lo77yrw5tt8adcxr.css">
    <link rel="stylesheet" type="text/css" href="__CSS__/artDialog.css" />

</head>
<body>
<style>
    .me-infor {
        overflow: hidden;
        margin: 10px 0;
    }
    .me-infor .infor-xx {
        padding: 10px 20px;
        margin: 0 0 10px;
        border: 1px solid #e3e3e3;
    }
    .me-infor .infor-xx ul li {
        display: block;
        height: 36px;
        line-height: 36px;
        padding: 3px 0;
    }
    .me-infor .infor-xx h6{
        float:left;
    }
    .mark {
        color: #f33d3d;
    }
</style>
<include file="Public/header" />
<script type="text/javascript" src="__ROOT__/resources/js/way.min.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/common.js"></script>
<script src="__JS2__/require.js" data-main="__JS2__/homePage"></script>
<div class="vip_info clearfix container">
    <include file="Member/side" />
    <div class="pull-right vip_info_pan">
        <div class="vip_info_title">
            我要充值
        </div>
        <div class="vip_info_content recharge_main">
            <include file="Account/paylist" />
            <form  method="post" action="{:U('Home/Account/recharge')}" onSubmit="return checkform(this)">
                <div class="common_border_box1 choiceBank_box">
                    <div class="choiceMoney">
                        <span class="choiceMoney_l">充值金额：</span>
                        <input type="number" name="amount" id="amount" placeholder="最低充值{$Allmsg.minmoney|floor}元" class="common_input">
                    </div>
                    <div class="choiceMoney">
                        <span class="choiceMoney_l">充值帐号：</span>
                        <input type="text" name="member" value="{$userinfo['username']}" class="common_input" readonly="readonly">
                    </div>
                    <div class="choiceBank clearfix">
                        <span class="choiceMoney_l pull-left">充值方式：</span>

                        <if condition="($tgpay_type eq 0) or ($tgpay_type eq 1)">
                            <span class="collectBank_ra checked">
                            <i class="iconfont icon-yuanquanxuanzhong"></i>
                            <input type="radio" name="paytype" value="1" checked="checked" style="display:none;">
                            <svg class="icon" aria-hidden="true">
							    	<use xlink:href="#icon-weixin-copy"></use>
								</svg>
                            <em>微信</em>
                            <div class="r_right">
									<span class="r_right_con">√</span>
								</div>
                        </span>
                        </if>

                        <if condition="($tgpay_type eq 0) or ($tgpay_type eq 2)">
                            <span class="collectBank_ra">
                            <i class="iconfont icon-yuanquanxuanzhong"></i>
                            <input type="radio" name="paytype" value="2" checked="checked" style="display:none;">
                            <svg class="icon" aria-hidden="true">
                                <use xlink:href="#icon-zhifubao"></use>
                            </svg>
                            <em>支付宝</em>
                            <div class="r_right">
                                <span class="r_right_con">√</span>
                            </div>
                        </span>
                        </if>

                    </div>
                    <button type="button"  class="btn common_btn nextbtn">充值</button>
                    <div class="prompt">
                        <?=$Allmsg['remark'];?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<include file="Public/footer" />
<script type="text/javascript">
    //alt('hello');
    $('.nextbtn').click(function () {
        if($('input[name=amount]').val()=="") {
            alt('请输入充值金额');return false;
        }
        if($('input[name=paytype]:checked').val()==""){
            alt('请选择支付方式');return false;
        }

        $.post("{:U('Home/Account/post_tg_pay')}",{
            amount      : $('#amount').val(),
            paytype     : $("input[name='paytype']:checked").val(),
        },function(res){
            alt(res.message);
            if(res.code == '1'){
                window.location.href = res.url;
            }
        },'json');
    });
</script>
</body>