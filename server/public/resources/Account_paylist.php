				 <?php 
				    if(strstr($_SERVER['PHP_SELF'],"quickRecharge")) {
						$quickRecharge = 'class="active"';
					};
				    if(strstr($_SERVER['PHP_SELF'],"zfbRecharge")) {
						$zfbRecharge = 'class="active"';
					};
				    if(strstr($_SERVER['PHP_SELF'],"wxRecharge")) {
						$wxRecharge = 'class="active"';
					};
				    if(strstr($_SERVER['PHP_SELF'],"jbfRecharge")) {
						$jbfRecharge = 'class="active"';
					};
					if(strstr($_SERVER['PHP_SELF'],"tgRecharge")){
						$tgRecharge = 'class="active"';
					}
					if(strstr($_SERVER['PHP_SELF'],"recharge")){
				 		$recharge = 'class="active"';
				 	}
				 
				 ?>
				<ul class="tab clearfix recharge_main_tab">
				    
					 <li {$zfbRecharge} style="width:120px;"><a href="{:U('Account/zfbRecharge')}" style="width:100px;">支付宝扫码充值</a></li>
					 <li {$wxRecharge}><a href="{:U('Account/wxRecharge')}">微信扫码充值</a></li>
		 
					 
					 <li {$recharge}><a href="{:U('Account/recharge')}">银行转账</a></li>
					<!--<li style="width:120px;"><a href="{:U('Account/fourRecharge')}" style="width:100px;">四合一在线充值</a></li>-->
				</ul>