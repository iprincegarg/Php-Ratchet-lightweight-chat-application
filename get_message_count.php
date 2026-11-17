<?php
$conn = mysqli_connect('localhost', 'yuvachat', 'Ireava#001', 'yuvachat');
$arr = array(); 
$data_arr=array();
$group_arr=array();

/* $ress=mysqli_query($conn,"SELECT gr_private_conversations.recipient_user_id,gr_site_users.username from gr_private_conversations LEFT JOIN gr_site_users ON gr_site_users.user_id=gr_private_conversations.recipient_user_id group by recipient_user_id");
while($fres=mysqli_fetch_assoc($ress)){
	$user_id = $fres['recipient_user_id'];
	$getres=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(private_chat_message_id) as tot_message FROM gr_private_chat_messages WHERE user_id IN(SELECT initiator_user_id FROM gr_private_conversations WHERE recipient_user_id=$user_id and initiator_user_id!=$user_id) and read_status=0"));
	$getres['username']=$fres['username'];
	$data_arr[]=$getres;
} */

$ress=mysqli_query($conn,"SELECT username,user_id FROM `gr_site_users` WHERE user_id IN(SELECT initiator_user_id FROM gr_private_conversations GROUP BY initiator_user_id) OR user_id IN(SELECT recipient_user_id FROM gr_private_conversations GROUP BY recipient_user_id)");
//while($fres=mysqli_fetch_assoc($ress)){
//	$user_id = $fres['user_id'];
//	$getres=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(private_chat_message_id) as tot_message FROM gr_private_chat_messages WHERE read_status=0 and private_conversation_id IN(select private_conversation_id from gr_private_conversations where recipient_user_id=$user_id OR initiator_user_id=$user_id) and user_id!=$user_id"));
	
//	$getres['username']=$fres['username'];
//	$data_arr[]=$getres;
//}


$ress=mysqli_query($conn,"SELECT gr_group_members.group_id,gr_site_users.username,gr_group_members.last_read_message_id FROM `gr_group_members` LEFT JOIN gr_site_users ON gr_site_users.user_id=gr_group_members.user_id GROUP BY gr_group_members.user_id,gr_group_members.group_id ORDER BY gr_group_members.user_id ASC");
//while($fres=mysqli_fetch_assoc($ress)){
//	$group_id = $fres['group_id']; $last_read_message_id = $fres['last_read_message_id'];
//	$getres=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(group_message_id) as tot_message FROM `gr_group_messages` WHERE group_id=$group_id AND group_message_id>$last_read_message_id ORDER BY `group_message_id` DESC"));
//	$getres['username']=$fres['username'];
//	$group_arr[]=$getres;
//}
//print_r($group_arr); exit;


$arr['status']='Success'; 
$arr['private_unread_message']=$data_arr; 
$arr['group_unread_message']=$group_arr; 
$arr['message']='Get all unread message successully';

echo json_encode($arr);
?>