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
<link rel="stylesheet" href="__CSS2__/ssc.css">
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
<script type="text/javascript" src="__ROOT__/resources/js/jquery-1.9.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/appchat-chat-index.css" />
    <link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/index1.css" />
    <link rel="stylesheet" type="text/css" href="__ROOT__/resources/css/chat/index.css" />
    <!--workmand的js-->
    <script type="text/javascript" src="__ROOT__/resources/js/chat/swfobject.js"></script>
    <script type="text/javascript" src="__ROOT__/resources/js/chat/web_socket.js"></script>
    <script type="text/javascript" src="__ROOT__/resources/js/chat/jquery-sinaEmotion-2.1.0.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/artDialog.js"></script>
<!--[if lt IE 9]>
<script src="__ROOT__/resources/js/html5shiv.js"></script>
<![endif]  fucai3d  -->

</head>

<body>
 
<style>	
	.j_lottery_time .shij span{
		color: #fff;
		font-size: 36px;
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
<include file="Public/gameHeader" />
<script type="text/javascript" src="__ROOT__/resources/js/way.min.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/jquery.history.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/common.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/index.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js/member.page.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js2/tabGameData.js"></script>
<script type="text/javascript" src="__ROOT__/resources/main/fc3d.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js2/fc3dtabGame.js"></script>
<script type="text/javascript" src="__ROOT__/resources/js2/bootstrap.min.js"></script>
<!--<script src="__JS2__/require.js" data-main="__JS2__/homePage"></script>-->
<div id="video_box" style="width: 100vw; height: 100vh; background-color: rgba(0,0,0,0.8);">
            
        </div>
<section class="container wapper" id="gamepage" style="width:1030px!important;position: relative;">
    <span onclick="strat_video()" class="start_video">
        开<br/>启<br/>动<br/>画
    </span>
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
                            <textarea  id="textarea_content" {if $info['is_temporary']==1}disabled  placeholder="输入内容" {/if} type="textarea" autosize="[object Object]" rows="2" autocomplete="off" validateevent="true" class="el-textarea__inner" style="height: 54px;"></textarea>
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
	<div class="open_containers g_Time_Section">
        <!--商品logo-->
        <div class="cz_logo">
            <h2 class="lottery_title_h2" way-data="showLotteryTitle.name">---</h2>
            <a href="javascript:void(0)" >
                <i class="icon-fucai3d iconfont common_lottery_icon" style="color:#00b7ee;width: 60px;height: 60px;font-size: 60px;margin-top: 0px;"></i>
            </a>
        </div>
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

            <div class="open_issue">第&nbsp;&nbsp;<b class="c_red" way-data="showExpect.lastFullExpect" id="f_lottery_info_lastnumber" firstissueno="">---</b>&nbsp;&nbsp;期匹配单号为：</div>
            <div class="open_number">
                <input type="hidden" value="1,1,2" id="j_openNum"><!--匹配单号效果赋值-->
                <ol id="ssc_winning_sum">
					<li class="ssc_winning_sum_gif" way-data="showExpect.openCode1"></li>
                    <li class="ssc_winning_sum_gif" way-data="showExpect.openCode2"></li>
                    <li class="ssc_winning_sum_gif" way-data="showExpect.openCode3"></li>
				</ol>
            </div>

        </div>
        <!--商品匹配单号-->
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
        console.log(type);
             if($('#is_video').html() == '1'){
            if(type == '---'){
                setTimeout(function(){
                mian_video();   

            },300)
            }else if(type == '福彩3D'){

                $('.start_video').css("display","block");
            var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/fc3DVideo/index.html?10041?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
                $('#video_box').append(str);
            }else if(type == '排列三'){

                $('.start_video').css("display","block");
            var str = '<div class="parent_chile"><iframe id="child" src="https://kj.kai861.com/view/video/fc3DVideo/index.html?10043?1682010.co" width="100%" scrolling="no" height="100%" frameborder="0"></iframe><img src="__ROOT__/resources/images/arraw_left.png" class="close_video_button" onclick="close_video()" /></div>';
                $('#video_box').append(str);
            }
        }
    }
 </script>
	<div class="lottery_playContainer">
		<div class="system_lottery_box">
			<span class="prev">
				<i class="iconfont icon-a866"></i>
			</span>
			<ul class="system_lottery" style="width: 1506px;">
				
				<volist name="ssclist" id="vo">
					<li <if condition="$vo['name'] eq $lotteryname">class="curr"</if> lotteryname="{$vo.name}">
						<a href="__ROOT__/Game.dpc?code={$vo.name}">{$vo.title}</a>
					</li>
				</volist>	
			</ul>
			<span class="next">
				<i class="iconfont icon-a866"></i>
			</span>
        </div>

		<div class="play_select_insert" id="j_play_select">
			<ul class="play_select_tit">
				<li lottery_code="fc3d_3x" class="curr">三星</li>
				<li lottery_code="fc3d_q2">前二</li>
				<li lottery_code="fc3d_h2">后二</li>
				<li lottery_code="fc3d_1x">一星</li>
				<li lottery_code="fc3d_dsds">大小单双</li> 
			</ul>
		</div>
	
  
    <section id="gameBet" class="cl">
		<section class="gameBet_balls">
			<div class="gameBet_left l">
			<if condition="$nowcpinfo['iswh'] eq 0">
				<!--玩法二级选项开始-->
				<div class="bet_filter_box">
				
				</div>
				<!--玩法二级选项结束-->
				<!--玩法详细说明区域-->
				<div class="play_select_prompt" style="padding:10px 0;">
					<i class="iconfont c_org"></i>
					<span way-data="tabDoc"></span>
				</div>
				<div class="g_Number_Section" style="width: 720px;padding: 15px 0;">
				</div>
				<div class="selectMultiple">
					<span class="select_zhushu">
						您选择了
						<em class="zhushu">0</em>
						注,
					</span>
					<div class="selectMultipleNumber">
						<i class="reduce">-</i>
						<input type="tel" value="1" class="selectMultipInput" onKeypress="return (/[\d]/.test(String.fromCharCode(event.keyCode)))">
						<i class="add">+</i>
					</div>
					倍
					<select class="selectMultipleCon">
						<option value="1">元</option>
						<option value="0.1">角</option>
						<option value="0.01">分</option>
					</select>
					<span class="selectMultipleOld">
						共
						<em class="selectMultipleOldMoney">
							0.00
						</em>
						元
					</span>
				</div>
				<!--玩法详细说明区域-->
				<div class="addtobet">
					<button class="addtobetbtn" type="button">确认选号</button>
				</div>
				<div class="xiad-left">
				<dl class="yBettingLists">
					
				</dl>
				</div>     
				<div class="g_Chase_Section">
					<div class="chase_Program">
						<p class="p_chase">方案注数
							<i class="c_green fw_600" way-data="ytotal_money_zhushu" id="f_gameOrder_lotterys_num">0</i> 注， 
							金额 <i class="c_org fw_600">¥<em id="f_gameOrder_amount" way-data="ytotal_money">0</em></i> 元  
						</p>
					</div>
				</div>   
				<div class="xiad-righ">
					<ul>
						<li class="li22"><span id="f_submit_order" style="cursor: pointer;">
							<img src="__ROOT__/resources/images/icon/icon_06.png">&nbsp;&nbsp;确认投注</span>
						</li>
						<li class="li22 li23"><span id="orderlist_clear" style="cursor: pointer;">
							<img src="__ROOT__/resources/images/icon/icon_19.png">&nbsp;&nbsp;清空单号</span>
						</li>
					</ul>
				</div>
			<else />
			<img src="__ROOT__/resources/images/k3cpcz.png" />
			</if>
			</div>
		
			
		</section>
		<!--选号区域右侧-->
        <div class="gameBet_right">
            <!--今日匹配单号-->
            <div class="right_infsoBlock">
                <div class="title">
                    <span class="fl">开奖公告</span>
                    <span class="fr">
                    <a target="_blank" class="open_lotteryNumb_chart yopen_explain"  href="{:U('Trend/trend_dpc',array('code'=>$nowcpinfo['name']))}">走势图</a>
                    |
                    <a href="javascript:void(0);" class="yopen_explain helps">玩法说明</a>
                    </span>
                </div>
                <div class="block_container lishi">
                    <table id="fn_getoPenGame" border="0px" cellpadding="0" cellspacing="0">
                        <colgroup>
                            <col width="93px" />
                            <col width="50px" />
                            <col width="40px" />
                            <col width="59px" />
                        </colgroup>
                        <thead>
                        <tr>
                            <th>期数</th>
                            <th>奖号</th>
                            <th>开奖时间</th>
                        </tr>
                        </thead>
                        <tbody class="tbody text-c">
                        <!--开奖期号-->
                        <!--匹配单号-->
                        <!--和值-->
                        <!--大小-->
                        <!--单双-->
                        </tbody>
                    </table>
                </div>
            </div>
            <!--今日匹配单号-->

            <!--最新中奖会员-->
            <div class="least_luckMember">
                <div class="title">
                    <span>中奖信息</span>
                    <em class="to_update">中奖信息实时更新</em>
                </div>
                <div class="ranking_scroll_box" style="height:435px;">
                <ul class="ranking_list sum_icon ranking_scroll">

                    <volist name="list2" id="value">
                    <li data-html="true" class="user_header" data-container="body" data-toggle="popover" data-placement="left" data-content="<div class=&quot;ceng&quot;><div class=&quot;media&quot;><div class=&quot;media-left&quot;><img src=&quot;__ROOT__{$value['face']}&quot; alt=&quot;&quot; class=&quot;media-boject img-circle&quot;><p></p></div><div class=&quot;media-body&quot;> <p class=&quot;margin_0&quot;>账号：<span>{$value['username']|substr_replace='**',1,2}</span></p><p class=&quot;margin_0&quot;>等级：<span>{$value['groupname']}</span></p><p class=&quot;margin_0&quot;>头衔：<span>{$value['touhan']}</span></p><p class=&quot;margin_0&quot;>累积中奖：<span>{$value['okamountcount']}</span></p></div>
                   <div class=&quot;media-footer&quot; style=&quot;padding-top:3px;&quot;>
                       <volist name="value['k3names']" offset="0" length="10"  id="val">
                         <a href=&quot;{:U('Game/k3')}?code={$val[name]}&quot; class=&quot;color_res&quot; ><span style=&quot;color:#333&quot;>{$val.title|substr=0,6}</span><i class=&quot;iconfont&quot;></i></a>
                       </volist>
                   </div></div></div>" data-original-title="" title="">
                        <div class="media clearfix">
                            <div class="media-left">
                                <img src="__ROOT__{$value['face']}" alt="" class="media-boject img-circle">
                            </div>
                            <div class="media-body">
                                <p class="margin_0">账号昵称：<span>{$value['username']|substr_replace='**',1,2}</span></p>
                                <p class="margin_0">收益金额：<em>{$value['okamount']}</em></p>
                            </div>
                        </div>
                    </li>
                    </volist>
                </ul>
                </div>
            </div>
            <!--最新中奖会员-->
        </div>
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
<section class="historylist mt-20">
	<div class="history-box">
		<div class="tabBd lot-tabBd" style="display:block">
			<table class="mem-biao" id="userBets">
				<thead>
				<tr>
					<th>订单号</th>
					<th>期号</th>
					<th>开奖号</th>
					<th>玩法</th>
					<th>赔率</th>
					<th>投注总额</th>
					<th>返佣</th>
					<th>下单时间</th>
					<th>状态</th>
				</tr>
				</thead>
				<tbody id="userBetsListToday"></table>
		</div>
		<div class="member-pag paging"></div>
	</div>
</section>
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
</div>
<div id="submitComfirebox" style="display:none">
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
				<div id="Orderdetaillist" class="textarea" style="font-size:12px;"></div>		
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
		<p class="text-note">	</p>	<p class="text-note">	</p>
	</div>
</div>
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

</div>
</body>
<script type="text/javascript" src="__ROOT__/resources/js/chat/webchat.js"></script>
</html>