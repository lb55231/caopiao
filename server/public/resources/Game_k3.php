<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{:GetVar('webtitle')}</title>

<meta name="keywords" content="{:GetVar('keywords')}" />
<meta name="description" content="{:GetVar('description')}" />
<meta name="renderer" content="webkit" />
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/reset.css" />
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/layout.css" />
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/artDialog.css" />
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/k3.css" />
<link rel="stylesheet" href="__CSS2__/bootstrap.min.css">
<link rel="stylesheet" href="__CSS2__/reset.css">
<link rel="stylesheet" href="__CSS2__/icon.css">
<link rel="stylesheet" href="__CSS2__/header.css">
<link rel="stylesheet" href="__CSS2__/footer.css">
<link rel="stylesheet" href="__CSS__/style.css"> 
<link rel="stylesheet" href="__CSS2__/main.css">
<link rel="stylesheet" href="__CSS__/common.css">
<script>
var WebConfigs = {
	webtitle:"{$webconfigs.webtitle}",
	kefuthree:"{$webconfigs.kefuthree}",
	kefuqq:"{$webconfigs.kefuqq}",
	ROOT : "__ROOT__"
};
</script>
<script>
<?php echo "var k3lotteryrates = ".json_encode($rates,JSON_UNESCAPED_UNICODE);?>
</script>

<!--[if lt IE 9]>
<script src="__ROOT__/resources/js/html5shiv.js"></script>
<![endif]-->

</head>

<body>
<style>	
	.j_lottery_time .shij span{
		color: #fff;
		font-size: 36px;
	}
	.cz_logo>a>img{
		width: 60px;
		height:60px;
	}
	.start_video{
       position: absolute;
    left: -24px;
    border-radius: 5px;
    top: 80px;
    width: 40px;
    height: auto;
    text-align: center;
    background-color: red;
    color: white;
    z-index: 100;
    line-height: 16px;
    padding: 10px 0px;
    border-top-right-radius: 0px;
    border-bottom-right-radius: 0px;
    cursor: pointer;
    display: none;
    }
    #video_box{
        overflow: hidden;
        position: fixed;
    left: -100vw;
    top: 0px;
    z-index: 1000;
    }
    #video_box #child{
          width: 718px;
    height: 470px;
    margin: 0 auto;
    display: block;
    position: relative;
    border-radius: 5px;
    border: 5px solid white;
    box-shadow: 0 0 10px white;
    }
    .close_video_button{
        width: 60px;
    text-align: center;
    margin: 20px auto;
    border-radius: 3px;
    display: block;
    color: white;
    font-size: 14px;
    cursor: pointer;
    }
    .parent_chile{
         width: 718px;
        height: 470px;
    margin: 0 auto;
    top: 50%;
    margin-top: -283px;
    position: relative;
    }

</style>
 <style type="text/css">
      .mychat {
    position: fixed;
    bottom: 0;
    right: 0;
    height: 100vh;
    width: 350px;
    background-color: #fff;
    transform-origin: bottom left;

    box-shadow: 0px 0px 7px 1px #ababab;
    outline: none;
    border: 0;
    transform-origin: bottom right;
    z-index: 9998 !important;
}
</style>
 <include file="Public/header" />
<script type="text/javascript" src="__ROOT__/resources/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/artDialog.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/member.page.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/way.min.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/jquery.history.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/common.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/index.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/k3.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js2/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/appchat-chat-index.css" />
    <link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/index1.css" />
    <link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/index.css" />
    <!--workmand的js-->
    <script type="text/javascript" src="__ROOT__/resources/js/chat/swfobject.js"></script>
    <script type="text/javascript" src="__ROOT__/resources/js/chat/web_socket.js"></script>
    <script type="text/javascript" src="__ROOT__/resources/js/chat/jquery-sinaEmotion-2.1.0.js"></script>
<!--<script src="__JS2__/require.js" data-main="__JS2__/homePage"></script>-->

<div id="video_box" style="width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.8);">
			
		</div>
		 <div class="mychat">
 
     <div class="chatbar">
    <!---->
    <div class="chatwin type-normal">
        <div class="chat">
            <div class="lay-relative">
                <!---->
                <div class="profile">
                    <div class="inner" style="animation-duration: 0.3s;">
                        <div class="avatar" ><img src="{$info['head']}" alt="" id="userhead"></div>
                        <p><span class="txt-nick">北京pk10</span></p>
                        <p>当前等级: <img src="__ROOT__/resources/images/chat/icon_member01.gif" alt="" class="head_level_img"></p>
                        <div>
                            <p>
                                <input type="file" id="upload_head" style="display: none">
                                <a href="javascript:void(0)" class="u-btn1">关闭</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="chat-header">
                    <div class="ttl"><span> 聊天室</span></div>
                </div>
                <div class="list" style="bottom: 98px;" >
                    <div class="lay-scroll" style="padding-top: 45px;" id="content_parent">
                       
                        <volist name="chat_list" id="vo1" key="k1">
                
                        <div class="Item {$vo1['class']}">
                            <div class="lay-block">
                                <div class="avatar" ><img src="{$vo1['head']}" alt="84***20"></div>
                                <div class="lay-content">
                                    <div class="msg-header">
                                        <h4>{$vo1['name']}</h4> <span><img src="{$vo1['level_img']}" alt="普通会员"></span> <span class="MsgTime">{$vo1['create_at']}</span></div>
                                    <div class="Bubble" style="{$vo1['style']}">
                                        <p><span style="white-space: pre-wrap; word-break: break-all;">{$vo1['content']}</span></p>
                                    
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    
                        </volist>

                    </div>
                    <div class="controls" style="top: 38px;">
                        <a href="javascript:void(0)" class="ListCtrl roll_screen"><i class="iconfont" style="vertical-align: -1px;"></i>滚屏</a>
                        <a href="javascript:void(0)" class="ListCtrl clear_screen"><i class="iconfont" style="vertical-align: 0px;"></i>清屏</a>
                    </div>
                    <div class="chat-announce">
                        <div class="ttl"><i class="iconfont icon-icon100"></i> 公告:</div>
                        <div class="scroll">
                            <marquee scrollamount="3">
                                <ol>
                      
                                     <volist name="gglist" id="vo" key="k" offset="0" length='1'>
          <li>{$vo.title}</li>
         </volist>
                                </ol>
                            </marquee>
                        </div>
                    </div>
                </div>
                <div class="compose">
                    <div class="control-bar">
                        <div class="el-popover"  x-placement="top-start">

                            <div class="emoji-container">
                                <i class="Emoji emoji-smile" data-id="[smile]"></i>
                                <i class="Emoji emoji-laughing" data-id="[laughing]"></i>
                                <i class="Emoji emoji-blush" data-id="[blush]"></i>
                                <i class="Emoji emoji-heart_eyes" data-id="[heart_eyes]"></i>
                                <i class="Emoji emoji-smirk" data-id="[smirk]"></i>
                                <i class="Emoji emoji-flushed" data-id="[flushed]"></i>
                                <i class="Emoji emoji-grin" data-id="[grin]"></i>
                                <i class="Emoji emoji-kissing_smiling_eyes" data-id="[kissing_smiling_eyes]"></i>
                                <i class="Emoji emoji-wink" data-id="[wink]"></i>
                                <i class="Emoji emoji-kissing_closed_eyes" data-id="[kissing_closed_eyes]"></i>
                                <i class="Emoji emoji-stuck_out_tongue_winking_eye" data-id="[stuck_out_tongue_winking_eye]"></i>
                                <i class="Emoji emoji-sleeping" data-id="[sleeping]"></i>
                                <i class="Emoji emoji-worried" data-id="[worried]"></i>
                                <i class="Emoji emoji-sweat_smile" data-id="[sweat_smile]"></i>
                                <i class="Emoji emoji-cold_sweat" data-id="[cold_sweat]"></i>
                                <i class="Emoji emoji-joy" data-id="[joy]"></i>
                                <i class="Emoji emoji-sob" data-id="[sob]"></i>
                                <i class="Emoji emoji-angry" data-id="[angry]"></i>
                                <i class="Emoji emoji-mask" data-id="[mask]"></i>
                                <i class="Emoji emoji-scream" data-id="[scream]"></i>
                                <i class="Emoji emoji-sunglasses" data-id="[sunglasses]"></i>
                                <i class="Emoji emoji-thumbsup" data-id="[thumbsup]"></i>
                                <i class="Emoji emoji-clap" data-id="[clap]"></i>
                                <i class="Emoji emoji-ok_hand" data-id="[ok_hand]"></i>
                            </div>
                            <div x-arrow="" class="popper__arrow" style="left: 15.5px;"></div>
                        </div>
                        <span></span>
                        <a href="javascript:void(0)" title="发送表情" class="btn-control"><img style="width: 26px;" src="__ROOT__/resources/images/chat/biaoqing.png" /></a><label for="imgUploadInput" style="display: none;"><span title="上传图片" class="btn-control"><i class="iconfont icon-erjiyemian-liaotianduihua-danchuangtianjiatupian"></i> <input id="imgUploadInput" type="file" accept=".jpg, .png, .gif, .jpeg, image/jpeg, image/png, image/gif" style="width: 0.1px; height: 0.1px; opacity: 0; position: absolute; top: -20px;"></span></label>
                        <!---->
                        <!---->
                        <!---->
                    </div>
                    <div class="typing">
                        <div class="txtinput el-textarea {if $info['is_temporary']==1}is-disabled{/if}">
                            <textarea  id="textarea_content" placeholder="输入内容" {if $info['is_temporary']==1}disabled   {/if} type="textarea" autosize="[object Object]" rows="2" autocomplete="off" validateevent="true" class="el-textarea__inner" style="height: 54px;"></textarea>
                        </div>
                        <div class="sendbtn" onclick="onSubmit()">
                            <a href="javascript:void(0)" class="u-btn1">发送</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
 </div>
 <!-- end -->
<section class="container wapper mysection" id="gamepage" style="width:1030px!important;position: relative;">
	<span onclick="strat_video()" class="start_video">
		开<br/>启<br/>动<br/>画
	</span>
	
	<div class="open_containers g_Time_Section">
        <!--商品logo-->
        <div class="cz_logo">
            <h2 class="lottery_title_h2" way-data="showLotteryTitle.name">---</h2>
            <a href="javascript:void(0)" >
<!--                <i class="icon-fucaikuai3 iconfont color_red common_lottery_icon">-->
<!--					-->
<!--				</i>-->
				<img src="/resources/images/lot_img/" alt="">
            </a>
        </div>
         <script type="text/javascript">
		function strat_video(){
			$("#video_box").animate({left:'0px'},800);
		}
		function close_video(){
			$("#video_box").animate({left:'-100vw'},700);
		}

        if($('#is_chat').html() == '1'){
                    $('.mychat').css('display','block');
            }else{
                     $('.mychat').css('display','none');
            }

			mian_video();
			
			
			
	function mian_video(){
		var type = $('.lottery_title_h2').html();

			if($('#is_video').html() == '1'){
			if(type == '---'){
				setTimeout(function(){
				mian_video();

			},300)
			}else if(type == '北京快3'){
				$('.start_video').css("display","block");
			var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10033?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}else if(type == '湖北快3'){
				$('.start_video').css("display","block");
				var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10032?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}else if(type == '河北快3'){
				$('.start_video').css("display","block");
				var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10028?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}else if(type == '吉林快3'){
				$('.start_video').css("display","block");
				var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10027?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}else if(type == '广西快3'){
				$('.start_video').css("display","block");
				var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10026?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}else if(type == '安徽快3'){
				$('.start_video').css("display","block");
				var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10030?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
				$('#video_box').append(str);
			}
        }
	}
		
	</script>
        <!--商品logo-->
        <!--商品开奖倒计时-->
        <div class="cz_daojishi">
            <div class="open_issue">距&nbsp;&nbsp;
				<b class="c_red" id="f_lottery_info_number" way-data="showExpect.currFullExpect">---</b>&nbsp;&nbsp;期投注截止还有：
			</div>
            <div class="j_lottery_time" servertime="" style="font-size: 22px; color: rgb(255, 255, 255);">
				<div class="shij">
                	<span way-data="gametimes.h">00</span>
                    :
                	<span way-data="gametimes.m">00</span>
                    :
                	<span way-data="gametimes.s">00</span>
                </div>
			</div>
        </div>
        <!--商品开奖倒计时-->

        <!--商品匹配单号-->
         <div class="cz_openNumb">

            <div class="open_issue">第&nbsp;&nbsp;<b class="c_red" way-data="showExpect.lastFullExpect" id="f_lottery_info_lastnumber" firstissueno="" style="display:none">---</b><b class="c_red" way-data="showExpect.lastShortExpect" firstissueno="">---</b>&nbsp;&nbsp;订单匹配结果：</div>
            <div class="open_number" style="display:none">
                <input type="hidden" value="1,1,2" id="j_openNum"><!--匹配单号效果赋值-->
                <ul id="openNum_list">
					<li class="open_numb_gif"></li>
					<li class="open_numb_gif"></li>
					<li class="open_numb_gif"></li>
				</ul>
            </div>
            <div class="open_result">
                <div class="open_daxiao" style="font-size: 16px;text-align:center"></div>
                <div class="open_danshuang" style="font-size: 16px;text-align:center"></div>
            </div>
        </div>
        <!--商品匹配单号-->
        <!--商品匹配单号-->
    </div>
    
	<div class="lottery_playContainer">
		<div class="system_lottery_box">
			<span class="prev">
				<i class="iconfont icon-a866"></i>
			</span>
			<ul class="system_lottery" style="width: 1506px;">
				<!--商品代码-->
				<volist name="k3list" id="vo">
					<li <if condition="$vo['name'] eq $lotteryname">class="curr"</if> lotteryname="{$vo.name}">
						<a href="__ROOT__/Game.k3?code={$vo.name}">{$vo.title}</a>
					</li>
				</volist>	
			</ul>
			<span class="next">
				<i class="iconfont icon-a866"></i>
			</span>
        </div>

		
<style>
    .g_Number_Section ul.g_img_Section li {margin: 12px 0px;}
    .g_Number_Section ul.g_img_Section li a{background:none}
    .g_Number_Section ul.g_img_Section li img{width:50px;height:50px}
    .g_Number_Section ul.g_img_Section li a{width:auto;}
    .g_Number_Section ul.g_img_Section li a.txtimg{
        box-shadow: 0 1px 5px #d4d4d4;
        background: -webkit-gradient(linear,left top,left bottom,color-stop(0,#fff),color-stop(90%,#f1efef),to(#f7f7f7));
        background: linear-gradient(180deg,#fff,#f1efef 90%,#f7f7f7);
        border-radius: 5px;
        border: 1px solid #c0c5d2;
    }
    </style>

    <section id="gameBet" class="cl">
		<section class="gameBet_balls">
			<div class="gameBet_left l">
			<if condition="$nowcpinfo['iswh'] eq 0">
				<div class="bet-num-box ">
					<div class="k3hzzx" style="display:block">
						<div class="g_Number_Section">
						<ul class="g_img_Section">
						<li><a playid="k3hz3" ball-type="k3hzzx" ball-number="3" href="javascript:void(0)" class="ball_number" peilv="189.00"><img src="/resources/k4/5.jpg"></a></li>
						<li><a playid="k3hz4" ball-type="k3hzzx" ball-number="4" href="javascript:void(0)" class="ball_number" peilv="63.00"><img src="/resources/k4/7.jpg"></a></li>
						<li><a playid="k3hz5" ball-type="k3hzzx" ball-number="5" href="javascript:void(0)" class="ball_number" peilv="31.50"><img src="/resources/k4/11.jpg"></a></li>
						<li><a playid="k3hz6" ball-type="k3hzzx" ball-number="6" href="javascript:void(0)" class="ball_number" peilv="18.90"><img src="/resources/k4/10.jpg"></a></li>
						<li><a playid="k3hz7" ball-type="k3hzzx" ball-number="7" href="javascript:void(0)" class="ball_number" peilv="12.60"><img src="/resources/k4/12.jpg"></a></li>
						<li><a playid="k3hz8" ball-type="k3hzzx" ball-number="8" href="javascript:void(0)" class="ball_number" peilv="9.00"><img src="/resources/k4/14.jpg"></a></li>
						<li><a playid="k3hz9" ball-type="k3hzzx" ball-number="9" href="javascript:void(0)" class="ball_number" peilv="7.56"><img src="/resources/k4/15.jpg"></a></li>
						<li><a playid="k3hz10" ball-type="k3hzzx" ball-number="10" href="javascript:void(0)" class="ball_number" peilv="7.00"><img src="/resources/k4/13.jpg"></a></li>
						<li><a playid="k3hz11" ball-type="k3hzzx" ball-number="11" href="javascript:void(0)" class="ball_number" peilv="7.00"><img src="/resources/k4/1.jpg"></a></li>
						<li><a playid="k3hz12" ball-type="k3hzzx" ball-number="12" href="javascript:void(0)" class="ball_number" peilv="7.56"><img src="/resources/k4/2.jpg"></a></li>
						<li><a playid="k3hz13" ball-type="k3hzzx" ball-number="13" href="javascript:void(0)" class="ball_number" peilv="9.00"><img src="/resources/k4/16.jpg"></a></li>
						<li><a playid="k3hz14" ball-type="k3hzzx" ball-number="14" href="javascript:void(0)" class="ball_number" peilv="12.60"><img src="/resources/k4/8.jpg"></a></li>
						<li><a playid="k3hz15" ball-type="k3hzzx" ball-number="15" href="javascript:void(0)" class="ball_number" peilv="18.90"><img src="/resources/k4/4.jpg"></a></li>
						<li><a playid="k3hz16" ball-type="k3hzzx" ball-number="16" href="javascript:void(0)" class="ball_number" peilv="31.50"><img src="/resources/k4/6.jpg"></a></li>
						<li><a playid="k3hz17" ball-type="k3hzzx" ball-number="17" href="javascript:void(0)" class="ball_number" peilv="63.00"><img src="/resources/k4/9.jpg"></a></li>
						<li><a playid="k3hz18" ball-type="k3hzzx" ball-number="18" href="javascript:void(0)" class="ball_number" peilv="189.00"><img src="/resources/k4/3.jpg"></a></li>
                       <li><a class="ball_number txtimg" playid="k3hzbig" ball-type="k3hzzx" ball-number="大" href="javascript:void(0)" peilv="1.95">精品</a></li>
                       <li><a class="ball_number txtimg" playid="k3hzsmall" ball-type="k3hzzx" ball-number="小" href="javascript:void(0)" peilv="1.95">普货</a></li>
                       <li><a class="ball_number txtimg" playid="k3hzodd" ball-type="k3hzzx" ball-number="单" href="javascript:void(0)" peilv="1.95">单/件</a></li>
                       <li><a class="ball_number txtimg" playid="k3hzeven" ball-type="k3hzzx" ball-number="双" href="javascript:void(0)" peilv="1.95">双/件</a></li>
                         </ul>
					<div style="text-align: right;">						
					       <span id="orderlist_clear" style="display:inline-block;padding: 10px;text-align: center;font-size: 12px;background: #ea6a31;borde:none;color:#fff;padding: 4px 15px;font-size: 14px;border-radius: 4px;">
					           <img src="__ROOT__/resources/images/icon/icon_19.png">&nbsp;&nbsp;清空单号
					       </span>
					</div>
					</div>
					</div>
					<div class="k3sthtx" style="display:none">
						<p class="text-c pt-10 pb-10">10元购买6个三同号(111,222,333,444,555,666)投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3sthtx style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
							<a class="ball_number" playid="k3sthtx" ball-number="三同号通选" href="javascript:void(0)">三同号通选</a>
						</div>
					</div>
					
					<div class="k3sthdx" style="display:none;">
						<p class="text-c pt-10 pb-10">至少选择1个三同号投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3sthdx style="display:inline">赔率读取中...</span>倍。</p>
					<div class="g_Number_Section">
					 <ul>
						<li><a class="ball_number" playid="k3sthdx" ball-number="111" href="javascript:void(0)">111</a></li>
						<li><a class="ball_number" playid="k3sthdx" ball-number="222" href="javascript:void(0)">222</a></li>
						<li><a class="ball_number" playid="k3sthdx" ball-number="333" href="javascript:void(0)">333</a></li>
						<li><a class="ball_number" playid="k3sthdx" ball-number="444" href="javascript:void(0)">444</a></li>
						<li><a class="ball_number" playid="k3sthdx" ball-number="555" href="javascript:void(0)">555</a></li>
						<li><a class="ball_number" playid="k3sthdx" ball-number="666" href="javascript:void(0)">666</a></li>
						</ul>
					</div>
					</div>
		
					<div class="k3sbthbz" style="display:none">
						<p class="text-c pt-10 pb-10">至少选择3个号码投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3sbthbz style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<ul>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="1" href="javascript:void(0)">1</a></li>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="2" href="javascript:void(0)">2</a></li>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="3" href="javascript:void(0)">3</a></li>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="4" href="javascript:void(0)">4</a></li>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="5" href="javascript:void(0)">5</a></li>
							<li><a class="ball_number" playid="k3sbthbz" ball-number="6" href="javascript:void(0)">6</a></li>
					 	</ul>
						 </div>
					</div>
					<div class="k3slhtx" style="display:none">
						<p class="text-c pt-10 pb-10">10元购买4个三连号（123、234、345、456）投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3slhtx style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<a class="ball_number" playid="k3slhtx" ball-number="三连号通选" href="javascript:void(0)">三连号通选</a>
						</div>
					</div>
		
					<div class="k3slhdx" style="display:none">
						<p class="text-c pt-10 pb-10">10元购买1个三连号单选(123,234,345,456)投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3slhdx style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<ul>
							<li><a class="ball_number" playid="k3slhdx" ball-number="123" href="javascript:void(0)">123</a></li>
							<li><a class="ball_number" playid="k3slhdx" ball-number="234" href="javascript:void(0)">234</a></li>
							<li><a class="ball_number" playid="k3slhdx" ball-number="345" href="javascript:void(0)">345</a></li>
							<li><a class="ball_number" playid="k3slhdx" ball-number="456" href="javascript:void(0)">456</a></li>
					 	</ul>
						 </div>
					</div>
		
					<div class="k3ethfx" style="display:none">
						<p class="text-c pt-10 pb-10">10元购买1个二同号(11*,22*,33*,44*,55*,66*)投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3ethfx style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<ul>
						<li><a class="ball_number" playid="k3ethfx" ball-number="11" href="javascript:void(0)">11</a></li>
						<li><a class="ball_number" playid="k3ethfx" ball-number="22" href="javascript:void(0)">22</a></li>
						<li><a class="ball_number" playid="k3ethfx" ball-number="33" href="javascript:void(0)">33</a></li>
						<li><a class="ball_number" playid="k3ethfx" ball-number="44" href="javascript:void(0)">44</a></li>
						<li><a class="ball_number" playid="k3ethfx" ball-number="55" href="javascript:void(0)">55</a></li>
						<li><a class="ball_number" playid="k3ethfx" ball-number="66" href="javascript:void(0)">66</a></li>
					 </ul>
					 </div>
					</div>
		
					<div class="k3ethdx" style="display:none">
						<p class="text-c pt-10 pb-10">选择1个相同号码和1个不同号码投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3ethdx style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<ul>
						<li><a class="ball_number" playid="k3ethdx" ball-number="11" href="javascript:void(0)">11</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="22" href="javascript:void(0)">22</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="33" href="javascript:void(0)">33</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="44" href="javascript:void(0)">44</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="55" href="javascript:void(0)">55</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="66" href="javascript:void(0)">66</a></li>
					 </ul>
						<ul>
						<li><a class="ball_number" playid="k3ethdx" ball-number="1" href="javascript:void(0)">1</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="2" href="javascript:void(0)">2</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="3" href="javascript:void(0)">3</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="4" href="javascript:void(0)">4</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="5" href="javascript:void(0)">5</a></li>
						<li><a class="ball_number" playid="k3ethdx" ball-number="6" href="javascript:void(0)">6</a></li>
					 </ul>
					 </div>
					</div>
		
					<div class="k3ebthbz" style="display:none">
						<p class="text-c pt-10 pb-10">至少选择2个号码投注，选号与匹配单号一致即中奖<span class="ball_aid" rate_k3ebthbz style="display:inline">赔率读取中...</span>倍。</p>
						<div class="g_Number_Section">
						<ul>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="1" href="javascript:void(0)">1</a></li>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="2" href="javascript:void(0)">2</a></li>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="3" href="javascript:void(0)">3</a></li>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="4" href="javascript:void(0)">4</a></li>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="5" href="javascript:void(0)">5</a></li>
							<li><a class="ball_number" playid="k3ebthbz" ball-number="6" href="javascript:void(0)">6</a></li>
					 	</ul>
						 </div>
					</div>
				</div> 
				<div class="addtobet" style="display:none;">
					<button class="addtobetbtn" type="button">添加到投注列表</button>
				</div><div class="g_Chase_Section">
				    <div class="money-units"><a class="unit selected">元</a></div> 
				    <div class="gw_num">
				        <em class="jian"><b>-</b></em><input type="number" value="2" class="num1 multxNumber"><em class="add"><b>+</b></em>
				    </div> 
				    <div class="multiple">Multiple</div> <div class="mr"><span>2元</span></div>
				    <br/><br/>
					<div class="chase_Program" style="float: left;clear: both;width:720px;height:55p;x">
						<div class="p_chase" style="float:left">选中
							<i class="c_green fw_600" way-data="ytotal_money_zhushu" id="f_gameOrder_lotterys_num">0</i> 件， 
							金额 <i class="c_org fw_600">¥<em id="f_gameOrder_amount" way-data="ytotal_money">0</em></i> 元  
						</div>
						<div style="float:right">
						    <span id="f_submit_order" style="display:inline-block;padding: 10px;text-align: center;font-size: 12px;background: #ea6a31;borde:none;color:#fff;padding: 2px 15px;font-size: 14px;border-radius: 4px;">直接下单</span>
						</div>
					</div>
				</div>   
				 <style>
				.g_Chase_Section .money-units{
				    float:left;
				}
				.g_Chase_Section .money-units .unit {
				    padding: 0 5px;
				    line-height: 26px;
				    border-radius: 2px;
				    border: 1px solid #fff;
				    -webkit-border-radius: 50%;
				    -moz-border-radius: 50%;
				    margin-right: 5px;
				    -webkit-box-shadow: 0 2px 6px #d3d4d6;
				    box-shadow: 0 2px 6px #d3d4d6;
				    display: inline-block;
				    text-align: center;
				    font-size: 14px;
				    color: #7a7a7a;
				    background: -webkit-gradient(linear,left top,left bottom,color-stop(10%,#fff),color-stop(71%,#e3e4e7),to(#f0f0f0));
				    background: linear-gradient(#fff 10%,#e3e4e7 71%,#f0f0f0);
				    -webkit-transition: all 0s ease!important;
				    transition: all 0s ease!important;
				    overflow: hidden;
				}
				.g_Chase_Section .money-units .selected {
				    color: #fff;
				    background: #0092dd;
				}
				.g_Chase_Section .gw_num {
				    border-radius: 2px;
				    border: 1px solid #dbdbdb;
				    margin-top: 2px;
				    margin-left: 10px;
				    float: left;
				    width: 110px;
				    line-height: 26px;
				    overflow: hidden;
				}
				.g_Chase_Section .multiple {
				    line-height: 34px;
				    margin-left: 4px;
				    margin-right: 7px;
				    float: left;
				}
				.g_Chase_Section .gw_num em {
				    background: #eee;
				    font-size: 19px;
				    width: 26px;
				    color: #7a7979;
				    border-right: 1px solid #dbdbdb;
				    cursor: pointer;
				    
				}
				.g_Chase_Section .gw_num em {
				    background: #eee;
				    font-size: 19px;
				    width: 26px;
				    color: #7a7979;
				    border-right: 1px solid #dbdbdb;
				    cursor: pointer;
				}
				.g_Chase_Section .gw_num .num1, .g_Chase_Section .gw_num em {
				    display: block;
				    height: 26px;
				    float: left;
				    text-align: center;
				    font-style: normal;
				    
				}
				.g_Chase_Section .gw_num .num1 {
				    font-size: 14px;
				    line-height: 24px;
				    border: 0;
				    width: 56px;
				}
				.g_Chase_Section .mr {
				    line-height: 34px;
				    margin-right: 7px;
				    float: left;
				    margin-left: 20px;
				    border: 1px solid #ccc;
				    padding: 0 10px;
				    cursor: pointer;
				    position: relative;
				}
				.g_Chase_Section {
				    height:70px;
				}
				</style>    
				  
				
			<else />
			<img src="__ROOT__/resources/images/k3cpcz.png" />
			</if>
			</div>

			
		</section>
		
        <!--选号区域右侧-->
		<!--<section class="gameBet_openlists">
			<div class="jinqi">
				<div class="title" style="height:30px; line-height:30px; border-bottom:1px solid #ddd">
                    <p class="pull-left" style="margin-left:10px;">
                        <img src="__ROOT__/resources/images/jbei.jpg" />开奖公告
                    </p>
                    <p class="pull-right" style="margin-right:10px;">
                        <a href="{:U('Game/trend',['code'=>$lotteryname])}">形态走势</a>
                    </p>
                </div>
				<div class="lishi">
				<table>
					<tbody class="text-c"></tbody>
				</table>
				</div>
			</div>
		</section>-->
    </section>
<!--下单记录---->

</section>
</div>
<include file="Public/footer" />

<div id="submitComfirebox" style="display:none">
    <div class="submitComfire">	<ul class="ui-form"><li><label for="question1" class="ui-label">商品：</label><span class="ui-text-info" way-data="showExpect.shortname">--</span></li>		<li><label for="question1" class="ui-label">期号：</label><span class="ui-text-info">第 <span way-data="showExpect.currFullExpect" class="mark">---</span> 期</span></li>		<li><label for="answer1" class="ui-label">详情：</label>		<div id="Orderdetaillist" class="textarea" style="font-size:12px;">		</div>		</li>		<li><label for="question2" class="ui-label">付款总金额：</label><span class="ui-text-info"><span id="Orderdetailtotalprice" class="mark">0.00</span>元</span></li>		<li><label for="question2" class="ui-label">付款帐号：</label><span way-data="user.username" class="ui-text-info mark">---</span></li>	</ul>	<p class="text-note">	</p>	<p class="text-note">	</p></div>
</div>

<div id="submitComfireboxaaa" style="display:none">
    <div class="submitComfire">
    <ul class="ui-form">
    <li>
        <label for="question1" class="ui-label">商品：</label>
        <span class="ui-text-info" way-data="showExpect.shortname">--</span>
    </li>
    <li>
        <label for="question1" class="ui-label">期号：</label>
        <span class="ui-text-info">第 <span way-data="showExpect.currFullExpect" class="mark">---</span> 期</span>
    </li>
    <li>
        <label for="answer1" class="ui-label">详情：</label>
        <div id="Orderdetaillist" class="textarea" style="font-size:12px;">		</div>
    </li>
    <li>
        <label for="question2" class="ui-label">付款总金额：</label>
        <span class="ui-text-info"><span id="Orderdetailtotalprice" class="mark">0.00</span>元</span>
    </li>
    <li>
        <label for="question2" class="ui-label">付款帐号：</label>
        <span way-data="user.username" class="ui-text-info mark">---</span>
    </li>
    </ul>
    </div>
</div>
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
<div id="getBillInfobox" style="display:none">
<div class="submitComfire">
<ul class="ui-form">
<li style="width:50%; float:left"><label for="question1" class="ui-label">商品：</label><span class="mark" way-data="BillInfo.cptitle">--</span></li>
<li style="width:50%; float:left"><label for="question1" class="ui-label">期号：</label><span class="mark">第 <span way-data="BillInfo.expect" class="mark">--</span> 期</span></li>
<li style="width:50%; float:left"><label for="question1" class="ui-label">玩法：</label><span class="mark" way-data="BillInfo.playtitle">--</span></li>
<li style="width:50%; float:left"><label for="question1" class="ui-label">赔率：</label><span way-data="BillInfo.mode" class="mark">--</span></li>
<li><label for="answer1" class="ui-label">投注号码：</label><span class="mark" way-data="BillInfo.tzcode">--</span></li>
<li style="width:50%; float:left"><label for="question2" class="ui-label">单注金额：</label><span class="mark" way-data="BillInfo.amount">--</span></li><li style="width:50%; float:left"><label for="question2" class="ui-label">投注注数：</label><span class="mark" way-data="BillInfo.itemcount">--</span></li>
<li style="width:50%; float:left"><label for="question2" class="ui-label">收益金额：</label><span class="mark" way-data="BillInfo.okamount">--</span></li><li style="width:50%; float:left"><label for="question2" class="ui-label">中奖注数：</label><span class="mark" way-data="BillInfo.okcount">--</span></li>


<li style="width:50%; float:left"><label for="question2" class="ui-label">匹配单号：</label><span class="mark" way-data="BillInfo.opencode">--</span></li><li style="width:50%; float:left"><label for="question2" class="ui-label">中奖状态：</label><span id="BillInfo_isdraw" way-data="BillInfo.state">--</span></li>
</ul>
</div>
</div><script>
$('.add').click(function(){
     let num = Number($('.multxNumber').val());
     num +=1;
     $('.multxNumber').val(num);
     $('.mr span').text(num + '元');
     lottery_price(num);
})
$('.jian').click(function() {
     let num = Number($('.multxNumber').val());
     if(num>1){
         num -=1;
     $('.multxNumber').val(num);
     $('.mr span').text(num + '元');
     lottery_price(num);
     }
})
$('.multxNumber').change(function(){
    let num = Number($('.multxNumber').val());
    $('.mr span').text(num + '元');
    lottery_price(num);
})
</script>
<script>
	 function winningScroll(obj) {
			var height = $(obj).find('li:first').outerHeight();
			var str = -height + 'px';
			var index = 0;
 
			$(obj).animate({'marginTop' : str},3000,function (){
				$(this).css('marginTop','0px').find('li:first').appendTo($(this));
			})
		}

	function openwindow(url,name,iWidth,iHeight) {
		var url; //转向网页的地址;
		var name; //网页名称，可为空;
		var iWidth; //弹出窗口的宽度;
		var iHeight; //弹出窗口的高度;
		//window.screen.height获得屏幕的高，window.screen.width获得屏幕的宽
		var iTop = (window.screen.height-30-iHeight)/2; //获得窗口的垂直位置;
		var iLeft = (window.screen.width-10-iWidth)/2; //获得窗口的水平位置;
		window.open(url,name,'height='+iHeight+',,innerHeight='+iHeight+',width='+iWidth+',innerWidth='+iWidth+',top='+iTop+',left='+iLeft+',toolbar=no,menubar=no,scrollbars=auto,resizeable=no,location=no,status=no');
	}
	//更新图片
	$('.cz_logo>a>img').attr('src',$('.cz_logo>a>img')[0].src+getQueryString('code')+'.png');
	//玩法说明
	$('.helps').click(function () {
		openwindow("{:U('Game/howtoplay', array('name'=>$nowcpinfo['name'],'cz'=>ACTION_NAME))}",'',1058,870);
	})
	//中奖信息scroll
	var myar = setInterval("winningScroll('.ranking_scroll')",5000);
	$('.ranking_scroll').hover(function (){ 
		clearInterval(myar);
	},function () {
		myar = setInterval("winningScroll('.ranking_scroll')",5000);
	})
	// 我的账户信息
	var timer1 = null;
	$('.my_account,.user_login_info2_list').mouseover(function (){
		if(timer1){
			clearTimeout(timer1);
		}
		$('.user_login_info2_list').show();
	})
	$('.my_account,.user_login_info2_list').mouseout(function (){
		timer1 = setTimeout(function (){
			$('.user_login_info2_list').hide();
		},300)
	})
	// 全部商户
	var timer2 = null;
	$('.allLottery,.backLeftLottery').mouseover(function (){
		if(timer2){
			clearTimeout(timer2);
		}
		$('.backLeftLottery').show();
	})
	$('.allLottery,.backLeftLottery').mouseout(function (){
		timer2 = setTimeout(function (){
			$('.backLeftLottery').hide();
		},300)
	})
	//余额切换
	$('.hide_money_btn').click(function () {
		$('.show_money').hide();
		$('.hide_money').show();
	})
	$('.show_money_btn').click(function () {
		$('.show_money').show();
		$('.hide_money').hide();
	})
	//余额刷新
	var index  = 0;
	$('.refresh_money').click(function () {
		index++;
		var sum = index*360;
		$(this).css('transform','rotate('+sum+'deg)');
	})
	//个人信息和昨日返佣榜以及中奖信息的名片显示
	$("[data-toggle='popover']").popover({
	trigger: "hover",
	delay: {hide: 100}
	}).on('shown.bs.popover', function (event) {
			var that = this;
			$('.popover').on('mouseenter', function () {
					$(that).attr('in', true);
			}).on('mouseleave', function () {
					$(that).removeAttr('in');
					$(that).popover('hide');
			});
	}).on('hide.bs.popover', function (event) {
			if ($(this).attr('in')) {
					event.preventDefault();
			}
	});
</script>
<style>
	.looding{
		width:100%;
		height:200%;
		z-index: 999;
		background: rgba(0,0,0,0.7);
		position: absolute;
		color:#333;
		top:0;
		left:0;
		text-align:center
	}
	.looding span{
		z-index: 9999;
		background: #ffffff;
		text-align:center;
		font-size:20px;
		color:#000;
		display: block;
		width:200px;
		height:50px;
		line-height: 50px;
		border-radius: 5px;
		position: fixed;
		top: 50%;
		left: 50%;
		margin-top: -25px;
		margin-left: -100PX;
	}
</style>
<div class="looding"  style="display:none;">
	<span>正在处理数椐... <img src="__IMG__/addloading.gif" width="23" height="23" alt=""></span>
<script type="text/javascript" src="__ROOT__/resources/js/chat/webchat.js"></script>
</div>
</body>
</html>