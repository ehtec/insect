<?php

require('sendmessage.php');

foreach($_POST as $key => $value){
  //echo "$key<br/>";
  //sleep(0.5);
  sendmessage("$key");
  //sleep(0.5);
}

//if(array_key_exists('test',$_POST)){
//	sendmessage('I was clicked');
//}
?>
