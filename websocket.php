<?php
class webSocket{

private $redis=null;

private $ws=null;


 //构造函数
   function __construct(){
 	 $this->redis=new Redis();  
      $this->ws=new swoole_websocket_server('0.0.0.0',9500);	    
	 //连接服务
	 $this->redis->connect('127.0.0.1','6379',1);

	 $this->ws->on('open',function($ws,$res){
		   // var_dump($res);
         
	 });

	 //监听websocket消息事件
	 $this->ws->on('message',function($ws,$job){

              //var_dump($job);
		    $json=$job->data;
		    $data=json_decode($json,1);
		    /*登录部分开始*/
		    if($data['login']==1){ 
               $this->openid=$data['qq_openid'];
		      if(!empty($this->openid)){
		       $status=$this->redis->zscore("user_list",$this->openid);;//获取在线用户表
			  //判断此用户当前端口是否还在使用
			  $check="";
			  foreach ($this->ws->connections as $fd) {
					if($fd==$status){
					 $check=1;
					}
                 }

			   if($status==true && $check==1){
				//关闭重复登录客户端	
                    echo "重复登录！";
			     $msg=array("close"=>1,"msg"=>"您已经在别地登录!");
				$json=json_encode($msg);
				$this->send(0,$job->fd,$json);
                    exit;
			   }else{
			   //redis记录用户信息,除了qq_openid,其他参数都会变
				 $arr=array(
                         "fd"=>$job->fd,
					"name"=>$data['user_info']['nickname'],
				     "figureurl"=>$data['user_info']['figureurl'],
					"time"=>date("Y-m-d H:i:s",time()),
					"json"=>$json
			      );
			   $this->redis->hmset($this->openid,$arr); //记录用户信息

                  //登录成功后先记录到在线用户set集合user_list，然后获取在线用户信息列表
                  $this->redis->zAdd('user_list',$job->fd,$this->openid);
			   $user_list_json=$this->json_user_list();
			   $this->send(1,0,$user_list_json); //将在线用户列表发给所有人

			   }
 
			  }
		    
		    }
		    /*聊天部分开始*/
		    if(!empty($data['to_fd'])){
				  $to_fd=$data['to_fd']; //给谁
				  $msg=$data['msg'];     //内容
				  $fromfd= $job->fd;
				  
			    if($to_fd=="all"){
			       $is_all=1;
			    }
		      $msg_arr['type']="msg";
                $msg_arr['is_all']=$is_all?:0;
			 $msg_arr['from']=$fromfd;
			 $msg_arr['msg']=$msg;
			 $msg_arr['time']=date("Y-m-d H:i:s");
			 $msg_json=json_encode($msg_arr);
                $this->send($is_all,$to_fd,$msg_json);
		    }  


		  // echo "Message:{$job->data}\n";

	 });
   
	 //监听websocket链接关闭事件
	 $this->ws->on('close',function($ws,$fd){

       echo "client-{$fd} is closed\n";
       $close_user=$this->redis->zrangebyscore("user_list",$fd,$fd);
       //var_dump($close_user);
       $this->redis->zrem("user_list",$close_user[0]);//设置登录状态未登录	 
       	
       var_dump($close_user);
      //刷新好友列表

	 });



      $this->ws->start();
   }


 //给客户端发送数据
 private function send($is_all,$fd,$msg){
        if ($is_all) {  
            foreach ($this->ws->connections as $fd) {  
                $this->ws->push($fd, $msg);  
            }  
        } else {  
            $this->ws->push($fd, $msg);  
        }  
 }

 //获取所有在线用户信息
 private function json_user_list(){
        $user_list=$this->redis->zrangebyscore('user_list', 0, 10000000);
	   $list=array();
	   foreach($user_list as  $k=>$v){
		 $arr[$v]=$this->redis->hgetall($v);
	   }
	   $list['type']="user_list";
	   $list['user_list']=$arr;
	   return json_encode($list);
 }

}




$server=new webSocket;





?>
