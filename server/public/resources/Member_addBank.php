
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
	<link rel="stylesheet" href="__CSS2__/updatePass.css">
	<link rel="stylesheet" href="__CSS2__/footer.css">
	<link rel="stylesheet" href="__JS2__/layer/skin/default/layer.css">
	<link rel="stylesheet" href="http://at.alicdn.com/t/font_bnvu6xzx1198uxr.css">
	<style type="text/css">
		.money_box .mysele{
		color: white;
		background-color: #f72222 !important;
	}
	.money_box{
		width: 500px;
    padding-bottom: 20px;
    display: inline-block;
    font-size: 0px;
    margin-top: 20px;
    border-bottom-style: dashed;
    border-bottom-width: 1px;
    border-bottom-color: #d0cdcd;
	}
	</style>
</head>
<body>
 <include file="Public/header" />
 <script src="__JS2__/require.js" data-main="__JS2__/homePage"></script>

		<div class="update_pass">
	<div class="container-fluid">
		<div style="text-align:center;">
			
<div class="money_box" style="border-bottom-style: ; font-size: 0px;margin-top: 20px;">
	<button onclick="sele_click('0')" class="mytype mysele" style="padding: 2px 25px;outline: none;background-color: white;border: 1px solid red;font-size:14px;border-right: 0px; ">银行卡</button>
	<button onclick="sele_click('1')" class="mytype" style="padding: 2px 25px;outline: none;background-color: white;border: 1px solid red;font-size:14px;">支付宝</button>
</div>
<script type="text/javascript">
	$('.money_box .mytype').on('click', function() {
				$(this).addClass('mysele').siblings().removeClass('mysele');
			})

	function sele_click(t){
		if(t == '0'){
			$('#myzfb').css("display","none");
			$('#myyhk').css("display","block");
		}else if(t == '1'){
			$('#myyhk').css("display","none");
			$('#myzfb').css("display","block");
		}
	}
</script>
		</div>
		<!-- start -->
		<form id="myzfb" action="{:U('Member/addBank')}" class="update_form" method="post" style="margin-top:50px;display: none;text-align: center;">
			<input type="text" name="stype" style="display: none;" value="z">
			<div class="clearfix drop_menu" style="margin-bottom:22px;">
				<div>
					<span class="" >本人姓名：</span>
					<div class="form-group accountname" style="text-align:left;">
						 {$userinfo.userbankname}
					</div>
				</div>
				<div>
				<span class="">支付宝账号：</span>
				<div class="form-group drop_menu">
					<input id="zfb" type="text" name="zfb" placeholder="请输入支付宝账号">
				</div>

				</div>
			</div>
			<div class="clearfix drop_menu">
			<div class="answer">
					<span>资金密码：</span>
					<input type="password" id="bankTradPwd2" name="safepass" placeholder="请输入资金密码">
			</div>
			</div>
		
			<button class="btn common_btn save_pass" onclick="userbindbankcard2();" type="button">提交</button>
		</form>
		<!-- end -->
		<form id="myyhk" action="{:U('Member/addBank')}" class="update_form" method="post" style="margin-top:10px;text-align: center;">
			<input type="text" name="stype" style="display: none;" value="y">
			<div class="clearfix drop_menu" style="margin-bottom:22px;">
				<div>
					<span class="">本人姓名：</span>
					<div class="form-group accountname" style="text-align:left;">
						 {$userinfo.userbankname}
					</div>
				</div>
				<div>
				<span class="">开户银行：</span>
				<div class="form-group drop_menu">
					<input id="sysBankCard" type="text" name="bankname" placeholder="请输入银行">
				</div>
				</div>
			</div>
			<div class="clearfix drop_menu" style="margin-bottom:22px;">
			<div>
				<span class="">开户省份：</span>
				<div class="form-group drop_menu">
					<input id="s_province" type="text" name="province" placeholder="请输入省份" style="width: 233px !important;text-align: left; border: 1px solid #cecece !important; height: 30px !important;">
				</div>
				</div>
				<div>
				<span class="">开户城市：</span>
				<div class="form-group drop_menu">
					<input id="s_city" type="text" name="city" placeholder="请输入城市" style="width: 233px !important;text-align: left; border: 1px solid #cecece !important; height: 30px !important;">
				</div>
				</div>
			</div>
			<div class="clearfix drop_menu">
			<div class="clearfix drop_menu">
				<div class="answer">
					<span>开户网点：</span>
					<input type="text" id="bankBranch" name="bankbranch" placeholder="请输入开户网点">
				</div>
			</div>
			</div>
			<div class="clearfix drop_menu">
			<div class="answer">
					<span>银行卡号：</span>
					<input type="text"  id="bankCardNum" name="banknumber" placeholder="请输入卡号">
			</div>
			</div>
			<div class="clearfix drop_menu">
			<div class="answer">
				<span>确认卡号：</span>
				<input type="text"  id="regBankCardNum" name="rebanknumber" placeholder="请输入卡号">
			</div>
			</div>
			<div class="clearfix drop_menu">
			<div class="answer">
					<span>资金密码：</span>
					<input type="password" id="bankTradPwd" name="safepass" placeholder="请输入资金密码">
			</div>
			</div>
			<button class="btn common_btn save_pass" onclick="userbindbankcard();" type="button">提交</button>
		</form>
	</div>
	</div>
	<style type="text/css">
		.drop_menu input{
			padding-left: 5px !important;
		}
	</style>
 <include file="Public/footer" />
 <script>
	 var userbindbankcard = function(){

		 var url = '__ROOT__/Apijiekou.' + 'userbindbankcard'; 
				 var bankCode = $("#sysBankCard").val();
		         var accountname = $(".accountname").html();
				 var bankCardNumber = $("#bankCardNum").val();
				 var regbankCardNumber = $("#regBankCardNum").val();
				 var province = $("#s_province").val();
				 var city = $("#s_city").val();
		 
				 var bankTradPwd = $("#bankTradPwd").val();
				 // 07-11 add 开户行网点
				 var bankBranch = $("#bankBranch").val();
				 bankBranch = bankBranch?bankBranch:"";
				 if (bankCode.length < 1) {
					 alt("请选择银行卡");return false;
				 } else if (province=="省份" || city=="地级市") {
					 alt("请选择开户省市");return false;

				 } else if (bankCardNumber.length < 1) {
					 alt("请输入银行卡号");return false;

				 } else if (regbankCardNumber.length < 1) {
					 alt("请输入确认银行卡号");return false;
				 } else if (regbankCardNumber != bankCardNumber) {
					 alt("两次卡号输入的不一致，请重新输入");return false;
				 } else if (bankTradPwd.length < 1) {
					 alt("请输入资金密码");return false;
				 }
				 var bankAddress = province + "-" + city;
				 $.post(url,{
				 	"stype": "y",
					 "bankCardNumber": bankCardNumber,
					 "accountname": accountname,
					 "bankAddress": bankAddress,
					 "bankTradPwd": bankTradPwd,
					 "bankCode": bankCode,
					 "regbankCardNumber": regbankCardNumber,
					 "bankBranch": bankBranch
				 }, function(json){
					 if(json.sign){
						 alt('银行绑定成功',1);
						 window.location.href="{:U('Member/bankcard')}";
					 }else{
						 alt(json.message,-1);
						 return false;
					 }
					 return false;
				 },'json');
	 }
	 var userbindbankcard2 = function(){

		 var url = '__ROOT__/Apijiekou.' + 'userbindbankcard'; 

				 var zfb = $("#zfb").val();
		         var accountname = $(".accountname").html();
		 		 var bankTradPwd = $("#bankTradPwd2").val();
		 		 console.log(bankTradPwd);
				 if (zfb.length < 1) {
					 alt("请输入支付宝账号");return false;
				 }  else if (bankTradPwd.length < 1) {
					 alt("请输入资金密码");return false;
				 }
				 console.log('ok');
				 $.post(url,{
				 	 "stype": "z",
					 "zfb": zfb,
					 "accountname": accountname,
					 "bankTradPwd": bankTradPwd
				 }, function(json){
					 if(json.sign){
						 alt('绑定成功',1);
						 window.location.href="{:U('Member/bankcard')}";
					 }else{
						 alt(json.message,-1);
						 return false;
					 }
					 return false;
				 },'json');
	 }
 </script>
</body>
</html>