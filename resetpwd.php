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
if(isset($_POST['reset_pwd'])){
	$pwd=test_input($_POST['pwd']);
	$pwd2=test_input($_POST['pwd2']);
	$nonce=$_GET['nonce'];
	$stmt = $dbh->prepare("select * from reset where status='0' and nonce=?");
	$stmt->execute(array($nonce));
	$row = $stmt->fetch(PDO::FETCH_OBJ);
	$email=$row->email;
	if($stmt->rowCount()==1){
		$stmt3=$dbh->prepare("select * from user_login WHERE email=? LIMIT 1;");
		$stmt3->execute(array($email));
		if($stmt3->rowCount()==1){
			if(empty($pwd)){
				echo "Please input your new password!";
			}else if(empty($pwd2)){
				echo "Please input your new password again!";
			}else if(strcmp($pwd,$pwd2)!=0){
				echo "Your new passwords are different! Please input again!";
			}else{

				$salt = sprintf("$2a$%02d$", 10).strtr(base64_encode(mcrypt_create_iv(16)), '+', '.');
				$hash = crypt($pwd, $salt);
				try {
					$stmt = $dbh->prepare("update user_login set password=? WHERE email =? LIMIT 1;update reset set status='1' where nonce=? LIMIT 1");
					$stmt ->execute(array($hash,$email,$nonce));
					unset($_COOKIE['auth']);
                        		setcookie('auth', null, -1);
                        		session_start();
                        		session_unset();
                        		if(session_destroy()){
                                	header("Location: login.php");
					}
				}catch (PDOException $e) {
                	        	printf("DatabaseError: %s", $e->getMessage());
	        		}

			}
		}else{
			echo "We cannot find such user!";
		} 
	}else{
		echo"No such request!";
	}
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge chrome=1">
  <meta charset="utf-8">
  <link href="Stylesheets/style.css" rel="stylesheet" type="text/css" />
  <title>Reset password</title>
</head>
<body>
<fieldset>
<legend>Reset your password!</legend>
<form method="post">
<label>Your new password:</label>
<input type="password" name="pwd" required="true" />
<br>
<label>Retype your new password:</label>
<input type="password" name="pwd2" required="true" />
<div><input type="submit" name="reset_pwd" value="Reset your password" /></div>
</form>
<a href="login.php">Go back to login page...</a>
</fieldset>
</body>
</html>

