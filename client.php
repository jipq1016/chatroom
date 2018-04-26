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
}else{
$user['login']=1;
$user['qq_openid']=$qq_openid;
$user['user_info']=$qq_user;
}

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
  var num= "";
  var qq_openid="<?php echo $qq_openid?>";
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
            
		  if(json_data.type=="user_list"){ //更新用户列表
             $(".member_list").html("<div onclick=select('all')>全部<span id='all_num'></span></div>");
		   user_list=new Array();
		   $.each(json_data.user_list,function(key,val){
				 var str="";
				 if(key==qq_openid){
				 str="style='display:none'";
				 }		    
		     user_list[val.fd]='<img src='+val.figureurl+'>'+val.name; 
			$(".member_list").append("<div "+str+" onclick='select("+val.fd+")'><img src="+val.figureurl+">"+val.name+"<span id='num_"+val.fd+"' class='red'></span></div>");  	    
		   
		   }) 
		  }
		  if(json_data.type=="msg"){
		     fromfd=json_data.from;
		     msg=json_data.msg;
			time=json_data.time;
			console.log(user_list);
		     $.each(user_list,function(k,v){
				  if(v!="" || v!="undefined"){ 
				   if(k==fromfd){
				     userdata=v;
				   }
				  }
			});

			if(json_data.is_all=="1"){
			str='<div class="all" style="display:none">'+userdata+'对大家说:'+msg+'<br>'+time+'</div>';
			count=$('#all_num').html();
			if(count==""){ count =0;}
			$('#all_num').html(parseInt(count)+1);
			}else{
			str='<div class="f_'+fromfd+'" style="display:none">'+userdata+':'+msg+'<br>'+time+'</div>';
			count=$('#num_'+fromfd).html();	
			if(count==""){ count =0;}
			$('#num_'+fromfd).html(parseInt(count)+1);//没有读取的用户信息
			}	

		    $(".message_list").append(str);	
			//当前对象
			num=$('#num').val();
		    if(num=="all"){
			$("div[class^='f_']").hide();	   
			$('.all').show();
		    }else{
		     $('.all').hide();
			$("div[class^='f_']").hide();	   
			$('.f_'+num).show();
		    }
 $('.message_list')[0].scrollTop= $('.message_list')[0].scrollHeight; 
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

//选择发送对象
function select(num){	   
  if(num=="all"){
    //发给所有人	   
    $('.who').html('全部');  
			$("div[class^='f_']").hide();	   
    $('.all').show();
    $('#all_num').html("");
  }else{
    //发给指定的人
    $('.who').html(user_list[num]);		
			$("div[class^='f_']").hide();	   
    $('.all').hide();
    $('.f_'+num).show();
    $('#num_'+num).html("");
  }
   
		    
		    
 $('.message_list')[0].scrollTop= $('.message_list')[0].scrollHeight; 

   $("#num").val(num);
}

//发送数据
function get_message(){
 var send_msg=$.trim($('#message').val());
 num=$("#num").val();
 if(send_msg==""){ return  }
 if(num==""){
 num="all";
 }
 var data={'to_fd':num,'msg':send_msg};
 var json=JSON.stringify(data);
 websocket.send(json);

 if(num!='all'){
 $('.message_list').append('<div class="f_'+num+' right">对他说：'+send_msg+'</div>');
 }else{
 //$('.message_list').append('<div class="all">'+send_msg+'</div>');
 }

 $('.message_list').scrollTop = $('.message_list').scrollHeight; 
 $("#message").val("");
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
.message_list{overflow:scroll;width:700px;height:300px;border:1px solid #000000;margin:10px;margin-left:210px;}
.member_list{overflow:scroll;float:left;width:200px;height:300px;border:1px solid #666666}
.message{width:500px;height:50px;}
.right{text-align:right};
.who{width:100%;}
#all_num{color:#ff0000}
.red{color:#ff0000}
</style>
</head>
<body>
<div class="main">
<div class="member_list">
<span>用户列表</span>


</div>
<div class="message_list">
<p class="who" align="center">全部</p>
</div>
</div>
<br/><br/>
<input class='message' type='text' id="message">
<br/><br/>
<input type="hidden" id="num" value="all">
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
