   $(".btn-control").click(function(){
        if($(".el-popover").is(':hidden')){
            $(".el-popover").fadeIn(200)
        }else{
            $(".el-popover").fadeOut(0)
        }
    })
    //显示用户信息 
    $(".icon-user").click(function(){
        $(".profile").fadeIn(200)
    })
    //关闭用户信息
    $(".profile .u-btn1").click(function(){
        $(".profile").fadeOut(200)
    })
    //关闭聊天框
    $(".icon-guanbi").click(function(){
        //$(".chatbar").fadeOut(200)
        if(window.top==window.self){
        }else {
            window.parent.close_iframe();
        }
    });
    //清屏
    $('.clear_screen').click(function () {
        $('.Item').remove();
    });
    //滚屏
    $('.roll_screen').click(function () {
        foll_screen();
    })
    function foll_screen(){
        var scrollDom = document.getElementById('content_parent');
        scrollDom.scrollTop = scrollDom.scrollHeight;
    }
    //表情包点击
    $('.Emoji').click(function () {
        $(".el-popover").fadeOut(200);
        if(isyk){
            console.log('没有发言的权限');
            return false;
        }
        var name=$(this).attr('data-id');
        $("#textarea_content").insertContent(name);
    })
    //** 修改默认的等级图标
    function changing(){
        var levelimg = '';
        level=parseInt(level);
        switch (level) {
            case 1:
                levelimg='./resources/images/chat/vip1.png';
                break;
            case 2:
                levelimg='./resources/images/chat/vip2.png';
                break;
            case 3:
                levelimg='./resources/images/chat/vip3.png';
                break;
            case 4:
                levelimg='./resources/images/chat/vip4.png';
                break;
            case 5:
                levelimg='./resources/images/chat/vip5.png';
                break;
                default:
                levelimg='./resources/images/chat/vip6.png';
        }
        $('.head_level_img').attr('src',levelimg);
    }

    $(function() {
        connect();
        changing();
        /* 在textarea处插入文本--Start */
        (function($) {
            $.fn.extend({
                insertContent : function(myValue, t) {
                    var $t = $(this)[0];
                    if (document.selection) { // ie
                        this.focus();
                        var sel = document.selection.createRange();
                        sel.text = myValue;
                        this.focus();
                        sel.moveStart('character', -l);
                        var wee = sel.text.length;
                        if (arguments.length == 2) {
                            var l = $t.value.length;
                            sel.moveEnd("character", wee + t);
                            t <= 0 ? sel.moveStart("character", wee - 2 * t - myValue.length) : sel.moveStart( "character", wee - t - myValue.length);
                            sel.select();
                        }
                    } else if ($t.selectionStart
                        || $t.selectionStart == '0') {
                        var startPos = $t.selectionStart;
                        var endPos = $t.selectionEnd;
                        var scrollTop = $t.scrollTop;
                        $t.value = $t.value.substring(0, startPos)
                            + myValue
                            + $t.value.substring(endPos,$t.value.length);
                        this.focus();
                        $t.selectionStart = startPos + myValue.length;
                        $t.selectionEnd = startPos + myValue.length;
                        $t.scrollTop = scrollTop;
                        if (arguments.length == 2) {
                            $t.setSelectionRange(startPos - t,
                                $t.selectionEnd + t);
                            this.focus();
                        }
                    } else {
                        this.value += myValue;
                        this.focus();
                    }
                }
            })
        })(jQuery);
        /* 在textarea处插入文本--Ending */
    });


    if (typeof console == "undefined") {    this.console = { log: function (msg) {  } };}
    // 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
    // WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
    // 开启flash的websocket debug
    WEB_SOCKET_DEBUG = true;
     //var ws, name="{$info['gm_name']}", client_list={},level="{$info['level']}",chat=parseInt({$info["level"]})-1,isyk=parseInt({$info['is_temporary']});
    var ws, name="{$info['gm_name']}", client_list={},level="{$info['level']}",isyk=false;
    // 连接服务端
    function connect() {
        // 创建websocket
        ws = new WebSocket("ws://"+document.domain+":7272?id=123");
        // 当socket连接打开时，输入用户名
        ws.onopen = onopen;
        // 当有消息时根据消息类型显示不同信息
        ws.onmessage = onmessage;
        ws.onclose = function() {
            console.log("连接关闭，定时重连");
            connect();
        };
        ws.onerror = function() {
            console.log("出现错误");
        };
    }
    // 连接建立时发送登录信息
    function onopen(){
        var login_data = '{"type":"login","client_name":"'+name.replace(/"/g, '\\"')+'","room_id":"1"}';

        ws.send(login_data);
    }
    // 服务端发来消息时
    function onmessage(e)
    {
        console.log("服务器推送消息："+JSON.stringify(e.data))
        var data = JSON.parse(e.data);
        switch(data['type']){
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;;
            // 登录 更新用户列表
            case 'login':
                break;
            // 发言
            case 'say':
                say(data['from_client_id'], data['from_client_name'], data['content'], data['time'],data['level'],data['head']);
                break;
            // 用户退出 更新用户列表
            case 'logout':
                break;
            case 'send':
                send(data['time'],data['content']);
                break;

        }
    }
    // 提交对话
    function onSubmit() {
        //判断能否发送聊天记录
        var value=$.trim($('#textarea_content').val());
 var chat_seitch = 0;
        var chat_filter = $('#chat_filter').html().split(',');
           for(var a = 0;a<chat_filter.length;a++){
                 if(value.indexOf(chat_filter[a]) != -1){
                        chat_seitch++;
                 }
           } 
        
        if(value==''){
            console.log('不能为空');
            return false;
        }
        if(isyk){
            console.log('游客没有发言权');
            return false;
        }
        /*console.log('没有发言的权限1');*/
        var to_client_id = 'all';
        var to_client_name ='所有人';
        var head=$(".myhead").attr('src');
        var name=$("#myname").html();
        level = $('#level').html();
        ws.send('{"name":"'+name+'","level":"'+level+'","type":"say","to_client_id":"'+to_client_id+'","head":"'+head+'","to_client_name":"'+to_client_name+'","content":"'+value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'"}');
        //发送到后台数据库
        sendserver(1,value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r'));
        
        $('#textarea_content').val("");
    }
    /*
    * 把聊天记录存入数据库
    * */
    function htmlEscape(text){ 
  return text.replace(/[<>"&]/g, function(match, pos, originalText){
    switch(match){
    case "<": return "&lt;"; 
    case ">":return "&gt;";
    case "&":return "&amp;"; 
    case "\"":return "&quot;"; 
  } 
  }); 
}
    function  sendserver(type,content) {
    
        var data={};
        data.username=$('#myname').html();
        data.content=htmlEscape(content);
        data.type=type;
        data.head=$('.myhead').attr('src');
        $.ajax({
            type:'post',
            data:data,
            dataType:'json',
            url:'Game.add_chat.do',
            success:function (result) {
                
            },
            error:function (result) {

            }
        })

        
    }
    /**
     *
     * 服务器推送消息
     */
    function  send(time,content){
        var html='<div class="Item type-left">\n' +
            '                            <div class="lay-block">\n' +
            '                                <div class="avatar"><img src="../img/sys.png" alt="计划消息"></div>\n' +
            '                                <div class="lay-content">\n' +
            '                                    <div class="msg-header">\n' +
            '                                        <h4>计划消息</h4> <span class="MsgTime">{time}</span></div>\n' +
            '                                    <div class="Bubble type-system">{content}<br>聊天室禁止打广告发连接！在游戏中有任何问题请联系在线客服！全网首家边聊边投注的官网实力平台！</div>\n' +
            '                                </div>\n' +
            '                            </div>\n' +
            '                        </div>';
        time=time.replace(/\d+\-\d+\-\d+/,'');
        content=content.replace(/&lt;br&gt;/g,'<br>');
        html=html.replace(/\{content\}/,content).replace(/\{time\}/,time);
        $('.lay-scroll').append(html);
    }
    // 发言
    function say(from_client_id, from_client_name, content, time,level,head){
        //替换表情
        content=content.replace(/\[(\w+)\]/g,'<i class="Emoji emoji-$1"></i>');
        //替换图片
        content=content.replace(/\{img\}(.*?)\{img\}/,'<img src="$1">');
       //替换头像
       head=head.replace(/\\/g,'');
      /* console.log(content,head);
       return false;*/
       var styleOrdinary='background: linear-gradient(to right, rgb(25, 158, 216), rgb(25, 158, 216)); border-left-color: rgb(25, 158, 216); border-right-color: rgb(25, 158, 216); color: rgb(255, 255, 255);';
       var styleHigh='background: linear-gradient(to right, rgb(0, 255, 212), rgb(198, 119, 119)); border-left-color: rgb(198, 119, 119); border-right-color: rgb(0, 255, 212); color: rgb(18, 18, 18);';
        var htmlright='<div class="Item type-right">\n' +
            '                            <div class="lay-block">\n' +
            '                                <div class="avatar" style="cursor: pointer;">\n' +
            '                                    <img src="{head}" alt="">\n' +
            '                                </div>\n' +
            '                                <div class="lay-content"><div class="msg-header">\n' +
            '                                    <h4 style="cursor: pointer;">{name}</h4>\n' +
            '                                    <span>\n' +
            '                                        <img src="{levelimg}" alt="{levelname}">\n' +
            '                                    </span>\n' +
            '                                    <span class="MsgTime">\n' +
            '                                        {time}\n' +
            '                                    </span>\n' +
            '                                </div>\n' +
            '                                    <div class="Bubble" style="{styleold}"> ' +
            '<p><span style="white-space: pre-wrap; word-break: break-all;">{content}</span></p> <!----></div>\n' +
            '                            </div>\n' +
            '                        </div>';
        var htmlleft=' <div class="Item type-left">\n' +
            '                            <div class="lay-block">\n' +
            '                                <div class="avatar" ><img src="{head}" alt=""></div>\n' +
            '                                <div class="lay-content">\n' +
            '                                    <div class="msg-header">\n' +
            '                                        <h4 >{name}</h4> <span><img src="{levelimg}" alt="普通会员"></span> <span class="MsgTime">{time}</span></div>\n' +
            '                                    <div class="Bubble" style="{styleold}">\n' +
            '                                        <p><span style="white-space: pre-wrap; word-break: break-all;">{content}</span></p>\n' +
            '                                        <!---->\n' +
            '                                    </div>\n' +
            '                                </div>\n' +
            '                            </div>\n' +
            '                        </div>';
        var newlhtml;
        var levelimg;
        level=parseInt(level);
        switch (level) {
            case 1:
                levelimg='./resources/images/chat/vip1.png';
                break;
            case 2:
                levelimg='./resources/images/chat/vip2.png';
                break;
            case 3:
                levelimg='./resources/images/chat/vip3.png';
                break;
            case 4:
                levelimg='./resources/images/chat/vip4.png';
                break;
            case 5:
                levelimg='./resources/images/chat/vip5.png';
                break;
                default:
                levelimg='./resources/images/chat/vip6.png';
        }
        var name = $('#myname').html();
        time=time.replace(/\d+\-\d+\-\d+/,'');
        if(from_client_name==name){
            newlhtml=htmlright.replace(/\{name\}/,from_client_name).replace(/\{content\}/,content).replace(/\{time\}/,time).replace(/\{head\}/,head).replace(/\{levelimg\}/,levelimg);
        }else {
            newlhtml=htmlleft.replace(/\{name\}/,from_client_name).replace(/\{content\}/,content).replace(/\{time\}/,time).replace(/\{head\}/,head).replace(/\{levelimg\}/,levelimg);
        }
        //如果是5级会员使用不同的样式
        if(level==5){
            newlhtml=  newlhtml.replace(/\{styleold\}/,styleHigh);
        }else {
            newlhtml= newlhtml.replace(/\{styleold\}/,styleOrdinary);
        }
        $('.lay-scroll').append(newlhtml);
        foll_screen();
    }
    /**
     * 上传头像
     */
    $('#userhead').click(function () {
        //判断是否室游客如果是游客就不能上传图片
        if(isyk){
            console.log('游客没有权限上传头像');
            return false;
        }
        $('#upload_head').click();
    });
    $('#upload_head').change(function () {
        var formData = new FormData();
        var file= $('#upload_head')[0].files[0];
        console.log(file);
        if(!file){
            return false;
        }
        formData.append('file', file);
        formData.append('type','1');
        $.ajax({
            url: '{:url("Chat/uploadImg")}',
            type: 'POST',
            cache: false,
            data: formData,
            processData: false,
            contentType: false
        }).done(function(data) {
            if(data.code){
                $('#userhead').attr('src',data.data);
            }else {
                alert(data.msg);
            }
        }).fail(function(data) {
            alert('网络异常请稍后再试');
        });
    })
    /**
     *  发言上传图片
     */
    $('#imgUploadInput').change(function () {
        if(isyk){
            console.log('没有发言的权限');
            return false;
        }
        var formData = new FormData();
        var file= $('#imgUploadInput')[0].files[0];

        if(!file){
            return false;
        }
        formData.append('file', file);
        formData.append('type','2');
        $.ajax({
            url: '{:url("Chat/uploadImg")}',
            type: 'POST',
            cache: false,
            data: formData,
            processData: false,
            contentType: false
        }).done(function(data) {
            if(data.code){
               sendchatImg(data.data);
            }else {
                alert(data.msg);
            }
        }).fail(function(data) {
            alert(data.msg);
        });
    })
    function sendchatImg(img) {
        var to_client_id = 'all';
        var to_client_name ='所有人';
        var head=$("#userhead").attr('src');
        var value='{img}'+img+'{/img}';
        sendserver(2,value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r'));
        ws.send('{"level":"'+level+'","type":"say","to_client_id":"'+to_client_id+'","head":"'+head+'","to_client_name":"'+to_client_name+'","content":"'+value.replace(/"/g, '\\"').replace(/\n/g,'\\n').replace(/\r/g, '\\r')+'"}');

    }
    window.onload=function () {
        foll_screen();
    }


