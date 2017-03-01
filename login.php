<?php

$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$pwd = 'helloworld123';
try {
	$dbh = new PDO($dsn,$user,$pwd);
} catch (PDOException $e) {
	printf("DatabaseError: %s", $e->getMessage());
}
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
if(isset($_POST['login'])) {
	$email = test_input($_POST['email']);
	$password = test_input($_POST['password']);
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	if(empty($email))
	{
		echo "Please input email!";
	}else if(empty($password))
	{
		echo "Please input password!";
	}
	else {
		try {
		$stmt = $dbh->prepare("select * from user_login WHERE email=? LIMIT 1;");

		$stmt->execute(array($email));
		$user = $stmt->fetch(PDO::FETCH_OBJ);
		}catch (PDOException $e) {
			printf("DatabaseError: %s", $e->getMessage());
			}
	}
	if ( hash_equals($user->password, crypt($password, $user->password)) ) {

		$token= md5(uniqid());
		session_name('auth');
		session_id($token);
		$_COOKIE['auth'] = $token;
		session_set_cookie_params(3*24*60*60,NULL,NULL,isset($_SERVER["HTTPS"]),TRUE);
		session_start();
		$_SESSION['userid']=$user->userid;
		$_SESSION['form_token'] = $token;
		header("Location: admin.php");
	}
	else{
		echo "Email or password is wrong!";
	}
}
if(isset($_POST['logoff'])) {
	unset($_COOKIE['auth']); 
	setcookie('auth', null, -1); 
	session_start();
	session_unset(); 
	if(session_destroy()){
		header("Location: login.php");
	}
}

$dbh = null;
?>


<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge chrome=1">
  <meta charset="utf-8">
  <link href="Stylesheets/style.css" rel="stylesheet" type="text/css" />
  <title>login</title>
</head>
<body>
<fieldset>
<legend>login</legend>
<form method="POST">
<label>E-Mail:</label><input size="35" name="email" type="email"> 
<br>
<label>Password:</label><input name="password" size="35" type="password"> 
<br>
<input type="submit" name="login" value="Login">
<div><a href="forgetpwd.php">Forget password</a></div>
</form>
</fieldset>
</body>
</html>

