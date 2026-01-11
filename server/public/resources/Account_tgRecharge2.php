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
    <h1>title</h1>
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

        $.post("{:U('Home/Apijiekou/post_tg_pay')}",{
            amount      : $('#amount').val(),
            paytype     : $("input[name='paytype']:checked").val(),
        },function(res){
            alt(res.message);
        },'json');

        /**
        $.ajax({
            type : 'POST',
            url : "{:U('Home/Apijiekou/addrecharge')}",
            data :{
                amount      : $('#amount').val(),
                paytype     : $("input[name='paytype']:checked").val(),
                userpayname : $("input[name='payname']").val(),
            },
            success : function (data) {
                if(data.sign == true){
                    $('.nextbtn').hide();
                    $('.choiceBank').hide();
                    $('.choiceMoney').hide();
                    $("#pay_alipay").show();
                    way.set("saomabill.amount",data.data.amount);
                    way.set("saomabill.trano",data.data.trano);
                    way.set("saomabill.id",data.data.id);
                    way.set("saomabill.paytype",data.data.paytype);
                    //alt('充值申请成功提交,请稍等');
                    setTimeout(function () {checkispay(data.data.trano);}, 5000);
                }else{
                    alt(data.message);
                }
            }
        });
        **/
    })


</script>
</body>
</html>