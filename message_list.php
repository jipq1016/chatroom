<?php
/**
 * list消息队列，保存消息记录
 */
$redis=new Redis();

$redis->connect("127.0.0.1","6379",1);

//连接数据库
$conn=mysqli_connect('127.0.0.1','jipq1016','Jipq_1016');
mysqli_select_db($conn,'myserver');
mysqli_query($conn,'set names "utf-8"');

while(1){
  if($redis->lsize('message_list')>0){	
  //取出队列最后一条数据,添加进数据库
  $last=$redis->rpop('message_list');
  $data=json_decode($last,1);
  $sql="insert into chatroom (`from`,`to`,`msg`,`datetime`) values ('".$data['from']."','".$data['to']."','".$data['msg']."','".$data['datetime']."')";
  $res=mysqli_query($conn,$sql);
  sleep(3);
  }else{
  sleep(3);
  }
}










?>
