<?php
//error_reporting(0);
//$req = json_decode($_POST);
$data = $_POST['data'];
$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$password = 'helloworld123';
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
try {
        $dbh = new PDO($dsn,$user,$password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
        printf("DatabaseError: %s\n", $e->getMessage());
}

$currency = "HKD";
$business = "yangdian0503-facilitator@gmail.com";
$salt = sprintf("$2a$%02d$", 10).strtr(base64_encode(mcrypt_create_iv(16)), '+', '.');
$message=$currency . "," . $business . "," . $salt.",".$data;
$obj->digest = hash('md5',$message);
$stmt=$dbh->prepare("insert into orders(salt,digest) values(?,?);"); 
$stmt->execute(array($salt,$obj->digest));
$obj->id = $dbh->lastInsertId();

output();
function output(){
	global $obj, $dbh;
	echo json_encode($obj);
	$dbh = null;
}
?>

