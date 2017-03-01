<?php
//error_reporting(0);
$req = json_decode($_POST);

$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$password = 'helloworld123';

try {
	$dbh = new PDO($dsn,$user,$password);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	printf("DatabaseError: %s\n", $e->getMessage());
}
$ans->Error = "0";
//$conn = new mysqli($servername, $username, $password,$dbname);

if(count($req->query) > 0){
	foreach ($req->query as $pos => $value) {
		$stmt = $dbh->prepare("SELECT * FROM products WHERE pid=?;");
		if ($stmt->execute(array($value))) {		
		while($row = $stmt->fetch()) {
		$ans->name->$row['pid']=$row['name'];
		$ans->price->$row['pid']=$row['price'];
		//$ans->name= $row['name'];
		 //$ans->price= $row['price'];
		//$ans->ans->$row['name']=$row['pid'];
		//$ans->ans->$row['name']=$row['name'];
		//$ans->ans->$row['price']=$row['price'];
    		}
		} else{

		}
	}		
}else if(count($req->query) == 0){
	$stmt=$dbh->prepare("SELECT * FROM products");
	if ($stmt->execute()) {		
		while($row = $stmt->fetch()) {
$ans->name->$row['pid']=$row['name'];
                $ans->price->$row['pid']=$row['price'];
//$ans->ans->$row['pid']= $row['price'];
		 //$ans->ans->price= $row['price']; 
//$ans->ans->$row['name']=$row['pid'];
                //$ans->ans->$row['name']=$row['name'];
                //$ans->ans->$row['price']=$row['price'];

    	}
	} else{

	}
			
}
output();


function output(){
	global $ans, $dbh;
	echo json_encode($ans);
	$dbh = null;
}
?>
