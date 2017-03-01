<?php
$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$password = 'helloworld123';
try {
        $dbh = new PDO($dsn,$user,$password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
        printf("DatabaseError: %s\n", $e->getMessage());
}
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
if(isset($_POST['send_email'])){
	$email=test_input($_POST['email']);
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	$stmt=$dbh->prepare("select * from user_login where email=? LIMIT 1");
	$stmt->execute(array($email));
	echo $email;
	echo $stmt->rowCount();
	if($stmt->rowCount()==0){
		echo "No such user!";
	}else if($stmt->rowCount()==1){
		$_nonce = mt_rand();
		$message = "Please use the following link to reset your password: https://s26.ierg4210.ie.cuhk.edu.hk/resetpwd.php?nonce=".$_nonce;
		$q = $dbh->prepare("INSERT INTO reset (nonce, email, status) VALUES (?,?,0);");
		$q->execute(array($_nonce, $_POST['email']));
		mail($_POST['email'], 'Reset Your Password', $message);	
		header('Location: login.php');
		exit();
	}else{
		echo "Something wrong!";
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge chrome=1">
  <meta charset="utf-8">
  <link href="Stylesheets/style.css" rel="stylesheet" type="text/css" />
  <title>Forget your password</title>
</head>
<body>
<fieldset>
<legend>Use your registered email to reset your password!</legend>
<form method="post">
<label>Email:</label>
<input type="text" name="email" required="true" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$" />
<div><input type="submit" name="send_email" value="Submit" /></div>
</form>
<a href="login.php">Go back to login page...</a>
</fieldset>
</body>
</html>
