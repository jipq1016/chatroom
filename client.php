<?php
session_start();

$_SESSION['swoole']='swoole';



$qq_openid=$_SESSION['qqOpenid'];
$qq_user=$_SESSION['qqUser'];

if($_GET['clear']==1){  //清空qq登录记录
	   $_SESSION['qqOpenid']="";
	   $_SESSION['qqUser']="";
        $qq_openid="";
	   $qq_user="";
}

$user['login']=1;
$user['qq_openid']=$qq_openid;
$user['user_info']=$qq_user;


$qq_user_json=json_encode($user);

?>
<!DOCTYPE html>  
<html>  
<head>  
    <meta charset="utf-8">  
    <title>聊天室客户端</title> 
   <script src="http://cdn.static.runoob.com/libs/jquery/1.10.2/jquery.min.js"></script>  
<script>
  var is_connect = false;
  var websocket = null;
  var is_log = false;
  var usr = '';

 function init(){
    var wsServer='ws://115.28.167.236:9500';
    websocket=new WebSocket(wsServer);
    websocket.onopen = function(evt){
    is_connect = true;
    console.log('connect!');

    var qq_user_json='<?php echo $qq_user_json?>';
    //post传qq用户信息
    //var fd=new FormData();
    //fd.append("type","post");
    //fd.append("data",qq_user_json);    
    //var json=JSON.stringify(qq_user_json);
    websocket.send(qq_user_json);
    };

    websocket.onclose = function(evt){
      console.log('close!');
    
    };

    websocket.onmessage = function(evt){
		  console.log('from server:'+evt.data);

            var json_data = JSON.parse(evt.data);  
            if(json_data.close==1){
		    alert(json_data.msg);
		    location.href="?clear=1";//清空保存的session
		  } 
            
		  if(json_data.type=="user_list"){
		    
	       123	  
		  
		  }


    };

    websocket.onerror = function (evt,e){
      console.log('Error occured:'+ evt.data);
    
    };

 }

 //qq登录后才连接服务器
<?php 
if($qq_openid){
?>  
init();
<?php
}
?>

function get_message(){
 var send_msg=$('#message').val();
 websocket.send(send_msg);

 $('.message_list').append(send_msg+'<br><br>');

}

function toLogin()
{
 //以下为按钮点击事件的逻辑。注意这里要重新打开窗口
 //否则后面跳转到QQ登录，授权页面时会直接缩小当前浏览器的窗口，而不是打开新窗口
 /*var A=window.open("Connect2.1/qq_login.php","TencentLogin", 
 "width=450,height=320,menubar=0,scrollbars=1,resizable=1,status=1,titlebar=0,toolbar=0,location=1");
  */
		  location.href="Connect2.1/qq_login.php";
			} 

			function closeQqWindow()
            {
                A.close();
                
		  }


</script>

<style>
.message_list{width:700px;height:300px;border:1px solid #000000;margin:10px;margin-left:210px;}
.member_list{float:left;width:200px;height:300px;border:1px solid #666666}
.message{width:500px;height:50px;}
</style>
</head>
<body>
<div class="main">
<div class="member_list">
<span>用户列表</span>

<div><a>全部</a></div>
</div>
<div class="message_list">
<p class="who">全部</p>
</div>
</div>
<br/><br/>
<input class='message' type='text' id="message">
<br/><br/>

<?php
if(empty($qq_openid)){
?>
<img src="http://qzonestyle.gtimg.cn/qzone/vas/opensns/res/img/bt_blue_76X24.png" onclick="toLogin()">
&nbsp;&nbsp;
<input type="button" value="提交" onclick="alert('qq登陆授权后，方可留言！');"/>
<?php
}else{
?>
<input type='button' onclick="get_message()" value="提交">
<?php
}
?>


</body>
</html> 
