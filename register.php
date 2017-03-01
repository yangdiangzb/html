<?php
$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$pwd = 'helloworld123';
try {
	$dbh = new PDO($dsn,$user,$pwd);
	echo "success!";
} catch (PDOException $e) {
	printf("DatabaseError: %s", $e->getMessage());
}

$email = 'test@test.com';
$password = 'Bb569019785';
$cost = 10;
//echo $cost;

$salt = strtr(base64_encode(mcrypt_create_iv(16)),'+','.');
//echo $salt;
$salt = sprintf("$2a$%02d$", $cost) . $salt;
//echo $salt;
$hash = crypt($password, $salt);
//echo $hash;

	try{
		$sth = $dbh->prepare("INSERT INTO user_login VALUES(null,?,?);");
	    $sth->execute(array($hash,$email));
		echo "New user is added!\n";
	} catch (PDOException $e) {
		echo "New user add fail.\n";
		printf("DatabaseError: %s\n", $e->getMessage());
    }


$dbh = null;

?>




