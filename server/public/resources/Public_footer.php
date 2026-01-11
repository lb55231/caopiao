<footer class="footer" style="clear:both">
    
    <div class="footer_other">
        <div class="container">
            <p class="footer_link">
                <a href="{:U('News/lists',['catid'=>30,'showid'=>3])}?About ">关于我们</a>
                <a href="{:U('News/lists',['catid'=>30,'showid'=>56])}?About">联系我们</a>
                <a href="{:U('News/lists',['catid'=>30,'showid'=>57])}?About">商务合作</a>
                <a href="{:U('News/lists',['catid'=>30,'showid'=>58])}?About">法律声明</a>
                <a href="{:U('News/lists',['catid'=>30,'showid'=>59])}?About">隐私声明</a>
            </p>
            <p class="footer_copyright">
                Copyright ©  {:GetVar('webtitle')}  Reserved | 18+
            </p>
        </div>
    </div>
</footer>
<if condition="$Think.cookie.showgg eq '1' && $Think.session.userinfo neq ''">
<div class="notice">
    <div class="noticCon">
        <h3>网站最新公告 <a class="iconfont icon-guanbi-copy closeNotice"></a></h3>
     <ul>
         <volist name="gglist" id="vo" key="k" offset="0" length='1'>
          <li><a href={:U('Member/ggshow',array('aid'=>$vo['id']))}>{$vo.title}<br>[{$vo.oddtime|date="Y-m-d H:i:s",###}]</a></li>
         </volist>
    </ul>
    </div>
</div>
 </if> 
<script>
    $('.notice').find('.closeNotice').click(function (){
     
        $('.notice').hide();
        $.ajax({
            url : "{:U('Common/closegg')}",
            type:"POST",
        })
    })
</script>
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
