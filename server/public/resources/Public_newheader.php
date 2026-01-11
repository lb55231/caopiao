<script>
    var WebConfigs = {
        "ROOT" : "__ROOT__",
        'IMG' : "__IMG__",
    }
</script>
<script type="text/javascript" src="__JS__/jquery-3.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="__CSS__/artDialog.css" />
<link rel="stylesheet" type="text/css" href="__CSS__/headernav.css" />
<script type="text/javascript" src="__JS__/artDialog.js"></script>
<script type="text/javascript" src="/resources/js/way.min.js"></script>
<script type="text/javascript" src="/resources/main/common.js"></script>
<header class="header" style="height:30px;">
    <div class="container claerfix">
        <div class="pull-left">
            Hi，欢迎来到{:GetVar('webtitle')}！
        </div>

        <notempty name="userinfo.username">
            <div class="pull-right user_login_info">
                <ul>
                    <!--<p class="margin_0">性别：<span><eq name="userinfo['sex']" value="1">男</eq><eq name="userinfo['sex']" value="2">女</eq><eq name="userinfo['sex']" value="">保密</eq></span></p>-->
                    <li class="user_login_info1">
                        <a  href="{:U('Member/index')}" class="user_header" data-html="true" class="user_header" data-container="body" data-toggle="popover" data-placement="bottom"data-content='<div class="ceng"><div class="media"><div class="media-left"><a href="{:U('Member/index')}"><img src="__ROOT__{$userinfo.face}" alt="" class="media-boject img-circle"></a><p>{$userinfo.username}</p></div><div class="media-body" style="padding-bottom:10px;">
                <p class="margin_0">账号：<span>{$userinfo.username}</span></p>
                <p class="margin_0">等级：<span>{$userinfo.groupname}</span></p>
                <p class="margin_0">头衔：<span><eq name="userinfo['groupname']" value='代理'>总代理 <else />{$userinfo.touhan} </eq></span></p>
                <p class="margin_0">累积中奖：<span>{$Think.session.okamountcount}</span></p>
            </div>
            <div class="media-footer">
                <volist name="Think.session.k3names" id="value">
                    <a href="{:U('Game/k3')}?code={$value['cpname']}" title="{$value.cptitle}" class="color_res" style="font-size:5px;"><span style="color:#333;display: block;margin-top:4px;">{$value.cptitle|substr=0,6}</span><i class="iconfont">&#xe607;</i></a>
                </volist>
            </div></div></div>'>
    <img class="img-circle"  src="__ROOT__{$userinfo.face}" alt="">
    {$userinfo['username']}
    </a>
    <a class="user_info" style="display:none">
        0
    </a>
    <div class="info_sum_box" style="display: none;">
        <div class="info_sum clearfix">
            <a href="" class="pull-left">
                我的未读消息
                (<em>0</em>)
            </a>
            <a href="" class="pull-right">
                更多
            </a>
        </div>
    </div>
    </li>
    <li class="user_login_info2">
        <a href="{:U('Member/index')}" class="my_account">
            我的账户
            <i class="iconfont">&#xe6a1;</i>
        </a>
        <div class="user_login_info2_list" style="display:none;">
            <i class="user_login_info2_i"></i>
            <if condition="$userinfo.proxy eq '1'">
                <a href="{:U('Member/Agent')}">代理中心</a>
            </if>
            <a href="{:U('Member/betRecord')}">下单记录</a>
            <a href="{:U('Account/dealRecord')}">交易记录</a>
            <a href="{:U('Member/ziliao')}">个人信息</a>
            <a href="{:U('Member/index')}">安全中心</a>
        </div>
    </li>
    <li class="user_login_info3">
        余额：
        <span class="show_money">
							<em class="smallmoney" style="color:#F70B0F;">{$userinfo['balance']}</em>
							<i class="iconfont refresh_money">&#xe602;</i>
							<em class="hide_money_btn">隐藏</em>
						</span>
        <span class="hide_money" style="display:none;">
							已隐藏
							<em class="show_money_btn">显示</em>
						</span>
    </li>
    <li class="xima l">洗码：<span class="c-green" style="color:green;" way-data="user.xima">0</span></li>
    <li class="user_login_info4">
        <a href="{:U('Account/quickRecharge')}">充值</a>
    </li>
    <li class="user_login_info5">
        <a href="{:U('Account/withdrawals')}">提现</a>
    </li>
    <li class="user_login_info6">
        <a href="{:U('Public/LoginOut')}">退出</a>
    </li>
    <li>
        <a href="{:GetVar('kefuthree')}"    target="_blank"   class="keufBox" style="margin-left: 0px;"></a>
    </li>
    <li style="padding:0;line-height: 49px;">
        <a href="{:GetVar('kefuqq')}"    target="_blank">
        </a>
    </li>
    </ul>
    </div>
    <else/>
    <div class="pull-right user_login_info ">
        <a style="margin:0;float:left;" href="{:U('Public/login')}">亲，请登录</a>
        <em style="margin:0 3px;color:#ccc;float:left;">|</em>
        <a style="float:left;" href="{:U('Public/register')}">用户注册</a>
        <em style="margin:0 3px;color:#ccc;float:left;">|</em>
        <a style="float:left;" href="{:U('Agent/index')}" >代理中心</a>
<!--        <a href="{:GetVar('kefuthree')}"    target="_blank"   class="keufBox pull-left"></a>-->
<!--        <a href="{:GetVar('kefuqq')}"    target="_blank">-->
<!--            <img src="__ROOT__/resources/images/qq.gif" width="20" height="20" style="vertical-align: super;float:left;margin-top:4px;" />-->
<!--        </a>-->
    </div>
    </notempty>
    </div>
</header>

<script>
    var ISLOGIN = "{$userinfo.id}";
</script>

<style>
    .container > a.logo{
        margin:5px 0px;
        overflow: hidden;
        height: 80px;
        width: 300px;
    }

    .container > ul.header_login{
        height: 80px;
        overflow: hidden;
    }

    ul.header_login li{
        float: left;
        margin-top: 24px;
        position: relative;
        margin-right: 5px;
    }

    ul.header_login input{
        width: 135px;
        height: 27px;
        border-radius: 2px;
        border: 1px solid #ddd;
        background-color: #fff;
        color: #333;
        padding-left: 8px;
    }

    ul.header_login>li>a.btn{
        margin: 0;
        height: 26px;
        width: 72px;
        line-height: 26px;
        padding: 0px;
    }

    .head8{
        height: 40px;
    }

    .head8 .nav {
        height: 39px;
    }

    .head8 .nav .container{
        height: 37px;
    }

    .head8 .nav .container .navItem{
        height: 35px;
    }

    .head8 .nav .container .navItem li{
        height: 34px;
    }

    .head8 .nav .container .navItem a{
        height: 35px;
    }

    .head8 .navItem span{
        height: 39px;
    }

    .header_login a.btn{
        font-size: 12px;
    }

    .header_login .login{
        background-color: #f13131;
    }

    .header_login .register{
        background-color: #ff9726;
        color:white;
    }

</style>

<div class="container clearfix">
    <a href="/" class="logo pull-left">
        <img src="resources/images/logo-4.png" style="width: 200px;height: 70px;" />
        <img src="resources/images/logo_dream.png" style="width:79px;height:27px;margin-left:8px;"/>
    </a>

    <notempty name="userinfo.username">

        <else/>

        <ul class="header_login pull-left">
            <li><input type="text"  name="name"  placeholder="用户名"></li>
            <li><input type="password"  name="password"  placeholder="密码"></li>
            <li>
                <a href="javascript:void(0);" class="btn btn-danger login">登录</a>
                <a href="javascript:void(0);" class="btn btn-default register" onclick="location.href='{:U('Public/register')}'">注册</a>
            </li>
        </ul>

    </notempty>


    <img src="resources/images/kefu.png" width="75" height="75" alt="" style="margin-right: 10px;float:right;margin-top:5px;cursor: pointer;" onclick="">

</div>

<div class="header head8">
    <div class="nav">
        <div class="container fix">
<!--            <h3><a href="/"><img src="resources/images/logo3.png" /></a></h3>-->
            <!---->
            <ul class="navItem fix flr" style="position: relative;">
                <li class=""><a href="__ROOT__/">首页</a></li>
                <!---->
                <!-- <li class="" id="navZRVideo"><a href="{:U('Index/zrvideo')}">真人视讯</a></li>  -->
                <li class=""><a href="{:U('Index/lottery')}">下单大厅</a></li>
                <!---->
                <li class=""><a href="{:U('Activity/index')}">活动中心</a></li>
                <!---->
                <li class=""><a href="{:U('Index/mobile')}">手机下单</a></li>
                <!---->
                <li class=""><a href="{:U('Member/index')}">我的账户</a></li>
                <!---->
                <li class=""><a href="{:U('News/lists',array('catid'=>33))}">帮助指南</a></li>
                <!---->
                <span></span>
            </ul>
        </div>
    </div>
</div>



<script>
    $(function () {
        $('.refresh_money').click(function () {
            $.ajax({
                url:"{:U('Account/refreshmoney')}",
                type:'POST',
                success :function (data) {
                    $('.smallmoney').html(data);
                }
            })
        });

        $(".header_login a.login").click(function(){
            //loginCengBoxFn("123");
            //提交登录信息
            $.post("{:U('Public/logindo')}",{
                name:$("input[name='name']").val(),
                pass:$("input[name='password']").val()
            },function(json){
                console.log(json.message);
                if(json.sign==1){
                    loginCengBoxFn(json.message);
                    window.location.href = "{:U('Member.index')}";
                }else{
                    loginCengBoxFn(json.message);
                }
            },'json');
        });



    })
</script>
