<?php
class webSocket{

private $redis=null;

private $ws=null;

private $openid=null;

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
		    if($data['login']==1){ 
               $this->openid=$data['qq_openid'];
		      if(!empty($this->openid)){
		       $status=$this->redis->sismember("user_list",$this->openid);;//获取在线用户表

			   if(empty($status)){
			   //redis记录用户信息,除了qq_openid,其他参数都会变
				 $arr=array(
					"fd"=>$job->fd,  //用户df
					"name"=>$data['user_info']['nickname'],
				     "figureurl"=>$data['user_info']['figureurl'],
					"time"=>date("Y-m-d H:i:s",time()),
					"json"=>$json
			      );
			   $this->redis->hmset($this->openid,$arr); //记录用户信息

                  //登录成功后先记录到在线用户set集合user_list，然后获取在线用户信息列表
                  $this->redis->sadd('user_list',$this->openid);
			   $user_list_json=$this->json_user_list();

			   $this->send(1,0,$user_list_json); //将在线用户列表发给所有人

			   }else{
				//关闭重复登录客户端	
                    echo "重复登录！";
			     $msg=array("close"=>1,"msg"=>"您已经在别地登录!");

                    $json=json_encode($msg);
				$this->send(0,$job->fd,$json);
			     $this->ws->close($job->fd);

			   }
 
			  }
		    
		    
		    
		    }


		  // echo "Message:{$job->data}\n";

	 });
   
	 //监听websocket链接关闭事件
	 $this->ws->on('close',function($ws,$fd){

	  echo "client-{$this->openid} is closed\n";
       $this->redis->srm("user_list",$this->openid);//设置登录状态未登录	 
	
    
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
        $user_list=$this->redis->smembers('user_list');
	   $list=array();
	   foreach($user_list $k=>$v){      
		 $arr[$v]=$this->redis->hgetall($v);
	   }
	   $list[type]="user_list";
	   $list[user_list]=$arr;
	   return json_encode($list);
 }

}




$server=new webSocket;





?>
