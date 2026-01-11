<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{:GetVar('webtitle')}</title>
    <meta name="keywords" content="{:GetVar('keywords')}" />
    <meta name="description" content="{:GetVar('description')}" />
    <meta name="renderer" content="webkit" />
    <link rel="stylesheet" href="__CSS2__/bootstrap.min.css">
    <link rel="stylesheet" href="__CSS2__/reset.css">
    <link rel="stylesheet" href="__CSS2__/icon.css">
    <link rel="stylesheet" href="__CSS2__/header.css">
    <link rel="stylesheet" href="__CSS2__/main.css">
    <link rel="stylesheet" href="__CSS2__/footer.css">

</head>
<body>
<script>
    var WebConfigs = {
        "ROOT" : "__ROOT__",
        'IMG' : "__IMG__",
    }
</script>
<include file="Public/newheader" />
<script type="text/javascript" src="__ROOT__/resources/js/way.min.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/common.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/index.js"></script>
<script src="__JS2__/require.js" data-main="__JS2__/homePage"></script>
<div class="main">
    <div class="container">
        <div class="row">
            <div class="col-xs-3 main_left padding_0">
                <ul class="magin_left_list indexcplist" >



                    <volist name="bncaipiao" id="cp">
                        <li>
                            <switch name="cp.typeid">
                                <case value="k3">
                                    <a href="__ROOT__/Game.k3?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont"><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="lhc">
                                    <a href="__ROOT__/Game.lhc?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont" style="color:#07b39e"><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="ssc">                                                                                                                               <a href="__ROOT__/Game.ssc?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont special " ><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="pk10">
                                    <a href="__ROOT__/Game.pk10?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont " style="color:#f22751" ><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="keno">
                                    <a href="__ROOT__/Game.keno?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont" style="color:#fc5826" ><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="x5">
                                    <a href="__ROOT__/Game.x5?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont"><img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px"/></i>
                                </case>
                                <case value="dpc">
                                    <a href="__ROOT__/Game.dpc?code={$cp[name]}" title="{$cp[ftitle]}">
                                        <i class="iconfont <?php if(strstr($cp['name'],'3d')){}else{}?>">
                                            <?php if(strstr($cp['name'],'3d')){ ?>
                                                <img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px">
                                            <?php } ?>

                                            <?php if(strstr($cp['name'],'pl3')){ ?>
                                                <img src="__ROOT__/resources/images/lot_img/{$cp.name}.png" style="width:38px">
                                            <?php } ?></i>
                                </case>
                            </switch>
                            <em>{$cp[title]}</em>
                            <span>{$cp[ftitle]|msubstr='0','6','utf-8',''}</span>
                            </a>
                        </li>
                    </volist>
                </ul>
            </div>
            <div class="col-xs-6 main_middle">
                <div class="middle_swiper">
                    <div id="myCarousel" class="carousel slide" data-ride="carousel">
                        <ol class="carousel-indicators">
                            <li class="active" data-target="#myCarousel" data-slide-to="0"></li>
                            <li data-target="#myCarousel" data-slide-to="1"></li>
                            <li data-target="#myCarousel" data-slide-to="2"></li>
                            <li data-target="#myCarousel" data-slide-to="3"></li>
                            <li data-target="#myCarousel" data-slide-to="4"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="item active">
                                <a href="{:U('Activity/index')}"><img src="__IMG__/indexbanner1.jpg" alt=""></a>
                            </div>
                            <div class="item">
                                <a href="{:U('Activity/index')}"><img src="__IMG__/indexbanner2.jpg" alt=""></a>
                            </div>
                            <div class="item">
                                <a href="{:U('Activity/index')}"><img src="__IMG__/indexbanner3.jpg" alt=""></a>
                            </div>
                            <div class="item">
                                <a href="{:U('Activity/index')}"><img src="__IMG__/indexbanner4.jpg" alt=""></a>
                            </div>
                            <div class="item">
                                <a href="{:U('Activity/index')}"><img src="__IMG__/indexbanner5.jpg" alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--
                    <div class="middle_swiper">
                        <div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="100">
                                            <ol class="carousel-indicators">
                                                <li class="active" data-target="#myCarousel" data-slide-to="0"></li>
                                                <li data-target="#myCarousel" data-slide-to="1"></li>
                                            </ol>
                                            <div class="carousel-inner">
                                                <div class="item active">
                                                    <a href=""><img src="img/banner1.png" alt=""></a>
                                                </div>
                                                <div class="item">
                                                    <a href=""><img src="img/banner2.png" alt=""></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    -->
                <div class="middle_tab main_common_bor">
                    <div class="tab_menu">
                        <ul class="tab_menu_box row margin_0">
                            <volist name="bncaipiao" id="value" key="key">
                                <eq name="value[name]" value="jsk3">
                                    <li class=" active col-xs-4">{$value['title']}</li>
                                </eq>
                                <eq name="value[name]" value="cqssc">
                                    <li class="col-xs-4">{$value['title']}</li>
                                </eq>
                                <eq name="value[name]" value="sh11x5">
                                    <li class="col-xs-4">{$value['title']}</li>
                                </eq>
                            </volist>

                        </ul>
                        <div class="tab_menu_content">
                            <ul class="tab_content_box">
                                <volist name="bncaipiao" id="value" key="key" >
                                    <eq name="value[name]" value="jsk3">
                                        <li style="display:block;">
                                            <div class="sum">
                                                <span class="sum{$value[opencode]|implode=',',###|substr=0,1}"></span>
                                                <i>+</i>
                                                <span class="sum{$value[opencode]|implode=',',###|substr=2,1}"></span>
                                                <i>+</i>
                                                <span class="sum{$value[opencode]|implode=',',###|substr=4,1}"></span>
                                                <i>=</i>
                                                <em>{$value[opencode]|array_sum}</em>
                                            </div>
                                            <p class="words">
                                                <span>当前期：第<em>{$value['expect']}</em>期</span>
                                                <span>匹配单号：第<em>{$value[opencode]|implode=',',###}</em></span>
                                                <span>和值：<em>{$value[opencode]|array_sum}</em></span>
                                                <span>形态：
												<a href="javascript:void(0);">{$value['daxiao']}</a>
												<a href="javascript:void(0);">{$value['danshuang']}</a>
											</span>
                                            </p>
                                        </li>
                                    </eq>
                                    <eq name="value[name]" value="cqssc">
                                        <li>
                                            <div class="clearfix">
                                                <div class="five_sumber pull-left">
                                                    <em>{$value[opencode][0]}</em>
                                                    <em>{$value[opencode][1]}</em>
                                                    <em>{$value[opencode][2]}</em>
                                                    <em>{$value[opencode][3]}</em>
                                                    <em>{$value[opencode][4]}</em>
                                                </div>
                                                <a href="__ROOT__/Game.ssc?code={$value[name]}" class="bet pull-left">立即投注</a>
                                            </div>
                                            <p class="words">
                                                <span>当前期：第<em>{$value['expect']}</em>期</span>
                                                <span>匹配单号：第<em>{$value[opencode]|implode=',',###}</em></span>
                                            </p>
                                        </li>
                                    </eq>
                                    <eq name="value[name]" value="sh11x5">
                                        <li>
                                            <div class="clearfix">
                                                <div class="five_sumber pull-left">
                                                    <em>{$value[opencode][0]}</em>
                                                    <em>{$value[opencode][1]}</em>
                                                    <em>{$value[opencode][2]}</em>
                                                    <em>{$value[opencode][3]}</em>
                                                    <em>{$value[opencode][4]}</em>
                                                </div>
                                                <a href="__ROOT__/Game.x5?code={$value[name]}" class="bet pull-left">立即投注</a>
                                            </div>
                                            <p class="words">
                                                <span>当前期：第<em>{$value['expect']}</em>期</span>
                                                <span>匹配单号：第<em>{$value[opencode]|implode=',',###}</em></span>
                                            </p>
                                        </li>
                                    </eq>
                                </volist>
                            </ul>
                        </div>
                    </div>
                </div>

                <!--                <div class="middle_tab main_common_bor">
                    <div class="tab_menu">
                        <ul class="tab_menu_box row margin_0">
                            <assign name="i" value='0' />
                            <volist name="bncaipiao" id="value" key="key" offset="0" length='3'>
                                <li class=" <eq name='i' value='0'> active </eq> col-xs-2">{$value['title']}</li>
                                <?php /*$i++;*/?>
                            </volist>
                        </ul>
                        <div class="tab_menu_content">
                            <ul class="tab_content_box">
                                <assign name="j" value='0' />
                                <volist name="bncaipiao" id="value" key="key" offset="0" length='3'>
                                    <li style="display: <eq name='j' value='0'>block<else />none</eq>;">
                                        <div class="sum">
                                            <span class="sum{$value[opencode]|implode=',',###|substr=0,1}"></span>
                                            <i>+</i>
                                            <span class="sum{$value[opencode]|implode=',',###|substr=2,1}"></span>
                                            <i>+</i>
                                            <span class="sum{$value[opencode]|implode=',',###|substr=4,1}"></span>
                                            <i>=</i>
                                            <em>{$value[opencode]|array_sum}</em>
                                        </div>
                                        <p class="words">
                                            <span>当前期：第<em>{$value['expect']}</em>期</span>
                                            <span>匹配单号：第<em>{$value[opencode]|implode=',',###}</em></span>
                                            <span>和值：<em>{$value[opencode]|array_sum}</em></span>
											<span>形态：
												<a href="javascript:void(0);">{$value['daxiao']}</a>
												<a href="javascript:void(0);">{$value['danshuang']}</a>
											</span>
                                        </p>
                                    </li>
                                    <?php /*$j++;*/?>
                                </volist>
                            </ul>
                        </div>
                    </div>
                </div>
                -->

            </div>

            <style>

                .main_left span{
                    float: right;
                    padding-right: 10px;
                }

                a:link, a:hover, a:visited{
                    text-decoration: none;
                }

                .main_right>.login{
                    background: #f6f6f6 none repeat scroll 0 0;
                    height: 468px;
                    width: 250px;
                }

                .main_right>.login>.login-box{
                    height: 80px;
                }

                .login-box > .not-login,.login-box > .login{
                    color: #333;
                    margin: 0 20px;
                }

                .login-box > .not-login > .login-btn-box{
                    height: 26px;
                    line-height: 36px;
                }

                .not-login > .welcome{
                    color: rgb(51, 51, 51);
                    height: 32px;
                    margin-top: 25px;
                    text-align: center;
                    font-size: 12px;
                }

                .not-login > .welcome > label{
                    font-weight: 400;
                }

                .login-box > .not-login>.login-btn-box> a{
                    text-align: center;
                    padding: 3px 10px;
                    color: #fff;
                    margin: 0 10px;
                    width: 80px;
                    height: 28px;
                    font-size: 12px;
                }

                .login-user-info {
                    color: #333;
                    font-size: 12px;
                    margin-top: 12px;
                }

                .login-user-info .user {
                    float: left;
                }

                .login-user-info .quit {
                    color: #999;
                    float: right;
                }

                .login .info-cont {
                    height: 27px;
                    line-height: 27px;
                    padding: 0;
                }

                .info-cont label{
                    float: left;
                }

                .info-cont .show_money,.info-cont .hide_money{
                    border: 1px dashed #ccc;
                    height: 22px;
                    line-height: 20px;
                    text-align: center;
                    width: 92px;
                    display: inline-block;
                    font-weight: 400;
                }

                .info-cont .recharge-btn{
                    height: 25px;
                    width: 72px;
                    background-color: #F13131;
                    font-size: 12px;
                    line-height: 25px;
                    padding: 0px;
                    margin-left: 20px;
                }

                .login .info-list .m14 {
                    color: #d8d8d8;
                    margin: 0 8px;
                }

                .login .info-list a {
                    color: #333;
                }

                .login .info-list a:hover {
                    color: #f13131;
                    text-decoration: none;
                }

                .login .info-list > label{
                    font-weight: 400;
                }

                .help-tab-box{
                    margin: 10px 0px 0;
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .help-tab-box > .help-tab{
                    border-bottom: 1px solid #dbdbdb;
                    height: 28px;
                    line-height: 28px;
                    margin-bottom: 6px;
                    padding-left: 10px;
                    padding-right: 10px;
                }

                .help-tab>li.on{
                    border-bottom: 2px solid #f13131;
                    height: 27px;
                    float: left;
                    margin-right: 16px;
                    position: relative;
                    text-align: center;
                    width: 60px;
                }

                .help-tab > li.on >a{
                    color: rgb(51, 51, 51);
                    display: inline-block;
                    font-size: 14px;
                }

                .help-tab-box > ul{
                    margin-top: 0px;
                    padding-left: 10px;
                    padding-right: 10px;
                }

                .mobile-box{
                    background-position: 0 -140px;
                    height: 190px;
                    margin: 5px 0px 0;
                    width: 100%;
                    padding-left: 15px;
                    padding-right: 15px;
                }

                .mobile-box > .mobile-box-text{
                    border-top: 1px solid #dbdbdb;
                    padding-top: 10px;
                    color: rgb(51, 51, 51);
                    height: 40px;
                    text-align: center;
                    width: 100%;
                    font-size: 14px;
                    margin-top: 15px;
                }

                .red {
                    color: red;
                    font-weight: 400;
                }

                .mobile-box div{
                    text-align: center;
                }

                .mobile-box img{
                    height: 142px;
                    margin: 10px 25px;
                    width: 142px;
                }

                .row > .drawing{
                    width: 232px;
                    overflow: hidden;
                }

                .draw-box{
                    width: 100%;
                }

                .draw-box > .title-top,.news-title{
                    border-bottom: 1px solid #e9e9e9;
                    height: 30px;
                    line-height: 30px;
                }

                .draw-box > .title-top > .notice-tit,.news-title>.news-tit{
                    float: left;
                    font-weight: bold;
                    font-size: 16px;
                    margin-top: 0px;
                    color: black;
                }

                .notice-tab{
                    float: right;
                    height: 30px;
                    line-height: 30px;
                }

                .notice-tab li {
                    color: #333;
                    cursor: pointer;
                    float: left;
                    margin-right: 12px;
                    text-align: center;
                    width: 24px;
                }

                .notice-tab li.on {
                    border-bottom: 2px solid #f13131;
                    height: 29px;
                    position: relative;
                }

                .notice-tab li a:link, .notice-tab li a:active {
                    color: #333;
                }
                .notice-tab li a:hover {
                    color: #f13131;
                    text-decoration: none;
                }

                .notice-main{
                    height: 400px;
                    position: relative;
                    overflow: hidden;
                }

                .notice-list li {
                    line-height: 22px;
                    margin: 0 10px;
                    border-bottom: 1px dotted #ccc;
                }

                .notice-list li.li-line {
                    /*background-position: 0 -350px;*/
                    height: 1px;
                    line-height: 1px;
                    overflow: hidden;
                    vertical-align: middle;
                    width: 210px;

                }

                .notice-list .lot-name {
                    color: #999;
                    float: left;
                    margin-top: 12px;
                }
                .notice-list .lot-name a:link, .notice-list .lot-name a:visited, .notice-list .lot-name a:active {
                    color: #333;
                    font-family: "微软雅黑","Microsoft Yahei";
                    font-size: 14px;
                    font-weight: bold;
                }
                .notice-list .lot-name a:hover {
                    color: #f13131;
                    text-decoration: none;
                }
                .notice-list span.term {
                    color: #666;
                    float: right;
                    margin-top: 12px;
                }
                .notice-list span.term a {
                    color: #999;
                }
                .notice-list .redball {
                    color: #f13131;
                    float: left;
                    font-weight: bold;
                    margin-right: 5px;
                }
                .notice-list .blueball {
                    color: #4495ff;
                    display: inline;
                    float: left;
                    font-weight: bold;
                    margin-left: 6px;
                }
                .notice-list li.last {
                    border-bottom: 0 none;
                    margin-bottom: 12px;
                    overflow: hidden;
                }
                .draw-contents .fr {
                    color: #d8d8d8;
                }
                .draw-contents .fr a:link, .draw-contents .fr a:visited, .draw-contents .fr a:active {
                    color: #666;
                }
                .draw-contents .fr a:hover {
                    color: #f13131;
                    text-decoration: none;
                }

                .clear {
                    clear: both;
                }

                .fr {
                    float: right;
                }

                .lottery-news-box>.news-content{
                    width: 500px;
                    overflow: hidden;
                    position: relative;
                }

                .news-content>.news-banner{
                    position: relative;
                    width: 492px;
                    /*height: 100px;*/
                    margin-top: 5px;
                    overflow: hidden;
                }

                .news-banner > ul > li{
                    margin-top: 5px;
                }

                .news-banner > ul > li img{
                    max-height:100px;
                    max-width:500px;
                }

                .winning-box .myScroll{
                    height:177px;
                    margin:10px;
                    padding:0px;
                    overflow: hidden;
                }

                .myScroll ul{
                    overflow: hidden;
                }

                .myScroll > ul >li {
                    line-height: 25px;
                    height:25px;
                    width: 223px;
                    overflow: hidden;
                    padding-left: 10px;
                }

                .myScroll > ul >li > b{
                    color: black;
                }

                .myScroll > ul > li >em{
                    float:right;
                    display: inline-block;
                    color: red;
                }

                .row .ranking-box{
                    margin-top: 15px;
                    width: 230px;
                    padding: 0px 10px 0;
                    height: 210px;
                    overflow: hidden;
                }

                .ranking-box table{
                    font-size: 12px;
                    border-collapse: collapse;
                    border-spacing: 0;
                }

                .ranking-box table td{
                    padding-top: 5px;
                }

                .ranking-box table td.tc{
                    text-align: center;
                }

                .ranking-box table td.tr{
                    text-align: right;
                }

                .ranking-box table span.top1_num{
                    width: 18px;
                    height: 18px;
                    line-height: 18px;
                    color: #fff;
                    text-align: center;
                    display: inline-block;
                    margin: 0 2px;
                    background-color: #f36309;
                }
                .ranking-box table span.top_num{
                    width: 18px;
                    height: 18px;
                    line-height: 18px;
                    color: #fff;
                    text-align: center;
                    display: inline-block;
                    margin: 0 2px;
                    background-color: #afafaf;
                }
            </style>

            <div class="col-xs-3 main_right padding_0">
                <div class="login clearfix">

                    <div class="login-box">
                        <if condition="is_array($userinfo)">
                            <div class="login">
                                <div class="login-user-info clearfix">
                                    <label class="user" id="span_user_username">Hi，{$userinfo.username}</label>
                                    <a href="{:U('Public/LoginOut')}" rel="nofollow" class="quit js-trigger-logout">退出</a>
                                </div>
                                <div class="info-cont clearfix">
                                    <label class="show_money">
                                        <em class="smallmoney" style="color:#F70B0F;">{$userinfo['balance']}</em>
                                        <i class="iconfont refresh_money">&#xe602;</i>
                                        <em class="hide_money_btn">隐藏</em>
						            </label>
                                    <label class="hide_money" style="display:none;">
							            已隐藏
                                        <em class="show_money_btn">显示</em>
						            </label>

                                    <label>
                                        <a href="{:U('Account/quickRecharge')}" class="btn btn-danger recharge-btn icon">充 值</a>
                                    </label>
                                </div>
                                <div class="info-list clearfix">
                                    <label><a href="{:U('Member/betRecord')}" target="_blank">下单记录</a></label>
                                    <label class="m14">|</label>
                                    <label><a href="{:U('Account/dealRecord')} " target="_blank">交易记录</a></label>
                                    <label class="m14">|</label>
                                    <label><a href="{:U('Member/ziliao')}" target="_blank">个人信息</a></label>
                               </div>
                            </div>
                            <else />
                            <div class="not-login">
                                <div class="welcome">
                                    <label>Hi，欢迎来到幸运商户网</label>
                                </div>
                                <div class="login-btn-box">
                                    <a href="{:U('Home/Public/login')}" class="btn bg_red">
                                        <i class="iconfont">&#xe61e;</i>
                                        登 录
                                    </a>
                                    <a href="{:U('Home/Public/register')}" class="btn bg_org">
                                        用户注册
                                    </a>
                                </div>
                            </div>
                        </if>
                        <div class="help-tab-box">
                            <ul class="help-tab">
                                <li class="on">
                                    <a href="javascript:void(0)" class="user-help">下单帮助</a>
                                </li>
                            </ul>
                            <ul class="help-ul">
                                <li>
                                    <a href="{:U('News/lists',array('catid'=>33))}" target="_blank">如何注册商户网会员？</a>
                                </li>
                                <li>
                                    <a href="{:U('News/lists',array('catid'=>33))}" target="_blank">在线支付的方式有哪些？</a>
                                </li>
                            </ul>
                        </div>

                        <div class="mobile-box">
                            <div class="mobile-box-text">
                                <label class="red">手机下单，轻轻松松变土豪！</label>
                            </div>
                            <div>
                                <img src="__ROOT__/resources/images/mobile_qrcode.png" alt="">
                            </div>
                        </div>
                    </div>

                    <!--
                    <if condition="is_array($userinfo)">
                        <div class="succees_box">
                            <p class="user_name">HI,{$userinfo.username}</p>
                            <p class="user_balance">余额：{$userinfo.balance}</p>
                            <p class="user_c_t">
                                <a href="{:U('Account/quickRecharge')}" class="btn bg_red">充 值</a>
                                <a href="{:U('Account/withdrawals')}" class="btn bg_org">提 现</a>
                            </p>
                        </div>
                        <else />

                    </if>
                    -->

                </div>

            </div>
        </div>

        <div class="row" style="margin-top: 24px;">
            <div class="col-xs-3 padding_0 drawing">
                <div class="draw-box">
                    <div class="title-top">
                        <h2 class="notice-tit">
                            <strong>开奖公告</strong>
                        </h2>
                        <ul class="notice-tab">
                            <li class="tab_g on">
                                <a href="javascript://" class="color333" title="热门">热门</a>
                            </li>
                            <li class="tab_g">
                                <a href="javascript://" class="color333" title="低频">低频</a>
                            </li>
                            <li class="tab_o">
                                <a target="_blank" href="{:U('Index/lottery')}" class="color333" title="更多">更多</a>
                            </li>
                        </ul>
                    </div>
                    <div class="notice-main">
                        <div class="draw-contents">
                            <ul class="notice-list">
                                <volist name="bncaipiao" id="value" key="key" >
                                    <if condition="$value['typeid'] eq 'ssc'">
                                        <li>
                                            <span class="lot-name"><a href="__ROOT__/Game.ssc?code={$value[name]}">{$value['title']}</a></span>
                                            <span class="term">{$value['expect']}期</span>
                                            <span class="clear"></span>
                                            <div class="clear"></div>
                                            <div class="redball">{$value[opencode][0]}</div>
                                            <div class="redball">{$value[opencode][1]}</div>
                                            <div class="redball">{$value[opencode][2]}</div>
                                            <div class="redball">{$value[opencode][3]}</div>
                                            <div class="redball">{$value[opencode][4]}</div>
                                            <br>
                                            <div class="fr">
                                                <span>2018-11-02 13:02:00</span> |
                                                <a href="__ROOT__/Trend.trend_{$$value.typeid}.code.{$value.name}.do" target="_blank">走势</a> |
                                                <a href="__ROOT__/Game.ssc?code={$value.name}" target="_blank">投注</a>
                                            </div>
                                            <div class="clear"></div>
                                        </li>
                                    </if>
                                </volist>
                            </ul>
                        </div>
                        <div class="draw-contents" style="display: none;">
                            <ul class="notice-list">
                                <volist name="bncaipiao" id="value" key="key">
                                    <if condition="$value['typeid'] eq 'dpc'">
                                        <li>
                                            <span class="lot-name"><a href="__ROOT__/Game.ssc?code={$value[name]}">{$value['title']}</a></span>
                                            <span class="term">{$value['expect']}期</span>
                                            <span class="clear"></span>
                                            <div class="clear"></div>
                                            <div class="redball">{$value[opencode][0]}</div>
                                            <div class="redball">{$value[opencode][1]}</div>
                                            <div class="redball">{$value[opencode][2]}</div>
                                            <br>
                                            <div class="fr">
                                                <span>2018-11-02 13:02:00</span> |
                                                <a href="__ROOT__/Trend.trend_{$$value.typeid}.code.{$value.name}.do" target="_blank">走势</a> |
                                                <a href="__ROOT__/Game.ssc?code={$value.name}" target="_blank">投注</a>
                                            </div>
                                            <div class="clear"></div>
                                        </li>
                                    </if>
                                </volist>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 main_middle">
                <div class="lottery-news-box">
                    <div class="news-title clearfix">
                        <h2 class="news-tit pull-left"><strong>商户资讯</strong></h2>
                    </div>
                    <div class="news-content">
                        <div class="news-banner">
                            <ul>
                                <li>
                                    <img src="__ROOT__/resources/images/banner-01.png" alt="">
                                </li>
                                <li>
                                    <img src="__ROOT__/resources/images/banner-01.png" alt="">
                                </li>
                                <li>
                                    <img src="__ROOT__/resources/images/banner-01.png" alt="">
                                </li>
                                <li>
                                    <img src="__ROOT__/resources/images/banner-01.png" alt="">
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-3 main_right padding_0">
                <div class="winning-box">
                    <div class="news-title clearfix">
                        <h2 class="news-tit pull-left"><strong>最新中奖</strong></h2>
                    </div>
                    <div class="news-content myScroll">
                        <ul class="news-scroll">
                            <volist name="list2" id="value">
                                <li>
                                    <?php echo str_replace_cn($value['username'],1,3);?>喜中 <b>{$value['k3name']}</b>
                                    <em>￥{$value['okamount']}.00</em>
                                </li>
                            </volist>
                        </ul>
                    </div>
                </div>

                <div class="ranking-box">
                    <div class="news-title clearfix">
                        <h2 class="news-tit pull-left"><strong>中奖排行</strong></h2>
                    </div>
                    <div class="news-content">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <colgroup>
                                <col width="10%">
                                <col width="40%">
                                <col width="50%">
                            </colgroup>
                            <tbody>
                                <volist name="list" offset="0" id="value" key="k">
                                    <tr class="top">
                                        <td class="tc">
                                            <lt name="k"  value="3">
                                                <span class="top1_num">{$k}</span>
                                                <else />
                                                <span class="top_num">{$k}</span>
                                            </lt>

                                        </td>
                                        <td class="tr"><?php echo str_replace_cn($value['username'],1,3);?></td>
                                        <td class="tr ">{$value['okamount']}.00元</td>
                                    </tr>
                                </volist>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<include file="Public/footer" />

<div class="loginCengBox">
    <div class="loginCeng">
        <div class="loginCengH">
            <h3>温馨提示</h3>
            <span class="loginCengClose">
				<i class="iconfont icon-guanbi-copy"></i>
			</span>
        </div>
        <div class="loginCengB">

        </div>
        <div class="loginCengF">
            <button type="submit" >确定</button>
        </div>
    </div>
</div>

<script type="text/javascript" src="__ROOT__/resources/js/scroll.js"></script>
<script>
    $(function(){
        $('.myScroll').myScroll({
            speed: 40, //数值越大，速度越慢
            rowHeight: 25 //li的高度
        });

        $('.notice-tab li.tab_g').hover(function(){
            //获取当前的索引
            //去掉全部的on class
            $('.notice-tab li').removeClass('on');
            $(this).addClass('on');
            var index = $('.notice-tab li').index(this);
            $('.notice-main .draw-contents').hide();
            $('.notice-main').find('.draw-contents').eq(index).show();
        });
    });
</script>
</body>
</html>