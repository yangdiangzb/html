<?php
session_name('auth');
session_start(); //if you are copying this code, this line makes it work.
//session_regenerate_id();

if(!isset($_COOKIE['auth']))
{
	header("Location: login.php");
}
if(!isset($_SESSION['userid']))
{
	header("Location: login.php");
}
if($_COOKIE['auth'] !=$_SESSION['form_token'])
		{			
			unset($_COOKIE['auth']); 
			setcookie('auth', null, -1); session_start();
			session_unset(); session_destroy();
			header("Location: login.php");
		}
if(isset($_SESSION['userid'])) 
{
	$uid = $_SESSION['userid'];
}
function store_in_session($key,$value)
{
	if (isset($_SESSION))
	{
		$_SESSION[$key]=$value;
	}
}
function unset_session($key)
{
	$_SESSION[$key]=' ';
	unset($_SESSION[$key]);
}
function get_from_session($key)
{
	if (isset($_SESSION[$key]))
	{
		return $_SESSION[$key];
	}
	else {  return false; }
}
function csrfguard_generate_token($unique_form_name)
{
	if (function_exists("hash_algos") and in_array("sha512",hash_algos()))
	{
		$token=hash("sha512",mt_rand(0,mt_getrandmax()));
	}
	else
	{
		$token=' ';
		for ($i=0;$i<128;++$i)
		{
			$r=mt_rand(0,35);
			if ($r<26)
			{
				$c=chr(ord('a')+$r);
			}
			else
			{ 
				$c=chr(ord('0')+$r-26);
			} 
			$token.=$c;
		}
	}
	store_in_session($unique_form_name,$token);
	return $token;
}
function csrfguard_validate_token($unique_form_name,$token_value)
{
	$token=get_from_session($unique_form_name);
	if ($token===false)
	{
		return false;
	}
	elseif ($token===$token_value)
	{
		$result=true;
	}
	else
	{ 
		$result=false;
	} 
	unset_session($unique_form_name);
	return $result;
}
function csrfguard_replace_forms($form_data_html)
{
	$count=preg_match_all("/<form(.*?)>(.*?)<\\/form>/is",$form_data_html,$matches,PREG_SET_ORDER);
	if (is_array($matches))
	{
		foreach ($matches as $m)
		{
			// if (strpos($m[1],"nocsrf")!==false) { continue; }
			$name="CSRFGuard_".mt_rand(0,mt_getrandmax());
			$token=csrfguard_generate_token($name);
			$form_data_html=str_replace($m[0],
				"<form{$m[1]}>
<input type='hidden' name='CSRFName' value='{$name}' />
<input type='hidden' name='CSRFToken' value='{$token}' />{$m[2]}</form>",$form_data_html);
		}
	}
	return $form_data_html;
}
function csrfguard_inject()
{
	$data=ob_get_clean();
	$data=csrfguard_replace_forms($data);
	echo $data;
}
function csrfguard_start()
{
	if (count($_POST))
	{
		if ( !isset($_POST['CSRFName']) or !isset($_POST['CSRFToken']) )
		{
			trigger_error("No CSRFName found, probable invalid request.",E_USER_ERROR);		
		} 
		$name =$_POST['CSRFName'];
		$token=$_POST['CSRFToken'];
		if (!csrfguard_validate_token($name, $token))
		{ 
			trigger_error("Invalid CSRF token.",E_USER_ERROR);
		}
	}
	ob_start();
	/* adding double quotes for "csrfguard_inject" to prevent: 
          Notice: Use of undefined constant csrfguard_inject - assumed 'csrfguard_inject' */
	register_shutdown_function("csrfguard_inject");	
}
csrfguard_start();



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
if(isset($_POST['changepwd'])){
        $email = test_input($_POST['email']);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $oldpwd = test_input($_POST['oldpwd']);
        $newpwd = test_input($_POST['newpwd']);
        $newpwd2 = test_input($_POST['newpwd2']);
        try {
                $stmt1 = $dbh->prepare("select * from user_login WHERE userid=? LIMIT 1;");
                $stmt1->execute(array($uid));
                $test = $stmt1->fetch(PDO::FETCH_OBJ);
        }catch (PDOException $e) {
                                printf("DatabaseError: %s", $e->getMessage());
        }
        if(empty($oldpwd)){
                echo "Please input your old password!";
        }else if(empty($newpwd)){
                echo "Please input your new password!";
        }else if(empty($newpwd2)){
                echo "Please input your new password again!";
        }else if(strcmp($newpwd,$newpwd2)!=0){
                echo "Your new passwords are different! Please input again!";
        }else if(strcmp($email,$test->email)!=0){
                echo "Your email is not correct!";
        }else{
                try {
                        $stmt = $dbh->prepare("select password from user_login WHERE userid=? LIMIT 1;");
                        $stmt->execute(array($uid));
                        $user = $stmt->fetch(PDO::FETCH_OBJ);
                }catch (PDOException $e) {
                        printf("DatabaseError: %s", $e->getMessage());
                }
                if(hash_equals($user->password, crypt($oldpwd, $user->password))){
                        $salt = sprintf("$2a$%02d$", 10).strtr(base64_encode(mcrypt_create_iv(16)), '+', '.');
                        $hash = crypt($newpwd, $salt);
                        try {
                                $stmt = $dbh->prepare("update user_login set password=? WHERE email =? LIMIT 1;");
                                $stmt ->execute(array($hash,$email));
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
                }else{
                        echo "Your old password is not correct!";
                }
        }
}
if(isset($_POST['new_category'])){
	try{
		$name = test_input($_POST['name']);
		if(empty($name)){
			echo "Please input category name!";
		}
		else if(!preg_match("/([A-Za-z()])+/",$name))
		{
			echo "Please input valid category name!";
		}
		else
		{
			$stmt=$dbh->prepare("INSERT INTO categories VALUES(null,?)");
			$stmt->execute(array($name));
			echo "New category ".$name." is added.\n";
		}
	} catch (PDOException $e) {
		printf("Error: %s\n", $e->getMessage());
	}
}
if(isset($_POST['edit_category'])) {
		try {
				$name = test_input($_POST['name']);
				$catid = $_POST['catid'];
		        
				if(empty($name))
				{
					echo "Please input category name!";
				}
				else if(!preg_match("/([A-Za-z()])+/",$name))
				{
					echo "Please input valid category name!";
				}
				else
				{
					$stmt=$dbh->prepare("UPDATE categories set name = ? WHERE catid=?;"); 
					$stmt->execute(array($name,$catid)); 
					echo "Category name is changed to ".$name.".\n";
				}
			}catch(PDOException $e) {
			printf("Error: %s\n", $e->getMessage());
		}	
}
if(isset($_POST['remove_category'])){
	try{
		$stmt=$dbh->prepare("DELETE FROM categories WHERE catid=?;");
		$stmt->execute(array($_POST['catid']));
		echo "Category is deleted.\n";
	} catch (PDOException $e) {
		printf("Error: %s\n", $e->getMessage());
	}
}
if(isset($_POST['new_product']) &&isset($_FILES['image'])) {
		$name=test_input($_POST['name']);
		$price=test_input($_POST['price']);
		$description=test_input($_POST['description']);
		$catid=$_POST['catid'];
	
		$stmt=$dbh->prepare("INSERT INTO products VALUES(null,?,?,?,?);"); 

		$target_dir = "Images/";
		$target_file = $target_dir . basename($_FILES['image']['name']);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(empty($name))
		{
			echo "Please input product name!";
		}
		else if(empty($price))
		{
			echo "Please input product price!";
		}
		else if(empty($_FILES['image']))
		{
			echo "Please attach product image!";
		}
		else if(!preg_match("/([A-Za-z0-9()])+/",$name))
		{
			echo "Please input valid product name!";
		}
		else if(!is_numeric($price))
		{
			echo "Please input valid product price!";
		}
		else if(!empty($description)&&!preg_match("/([A-Za-z0-9()])+/",$description))
		{
			echo "Please input valid product description!";
		}
		else{
    		$check = getimagesize($_FILES['image']['tmp_name']);
    		if($check !== false) {
        		$uploadOk = 1;
    		} else {
        		echo "File is not an image.\n".$target_file.$tmp_file;
        		$uploadOk = 0;
    		}
		}
		// Check if file already exists
		if (file_exists($target_file)) {
    		echo "Sorry, file already exists.\n";
    		$uploadOk = 0;
		}
		// Check file size
		if ($_FILES['image']['size'] > 10485760) {
    		echo "Sorry, your file is too large.\n";
    		$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "gif" ) {
    		echo "Sorry, only JPG, PNG & GIF files are allowed.\n";
    		$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error

		if ($uploadOk == 0) {
    		echo "Sorry, your file was not uploaded.\n";
		// if everything is ok, try to upload file
		} else {
			try{
				$stmt->execute(array($catid,$name,$price,$description));
				echo "New product ".$name." is added!\n";
			}catch (PDOException $e) {
				echo "New product ".$name." add fail.\n";
				printf("DatabaseError: %s\n", $e->getMessage());
    		}
			$last_id = $dbh->lastInsertId();
			$target_name= $target_dir . $last_id;
			if ($_FILES["image"]["error"] > 0) {
                                echo "Error: " . $_FILES["image"]["error"] . "<br>";
                        } else {
                                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_name)){
                                        echo "Uploaded File :" . $target_name;
                                        //echo "<pre>";
                                        //print_r($_POST);
                                        //print_r($_FILES);
                                }
                        else { echo "Fail"; } }
			//if (move_uploaded_file($_FILES['image']['tmp_name'], $target_name)) {
			//	echo "The file ". basename( $_FILES['image']['name']). " has been uploaded.\n";
    		//} else {
		//		echo "Sorry, there was an error uploading your file". $target_file .".\n"  ;
    		//}
		}
}
if(isset($_POST['edit_product']) && isset($_FILES['image'])) {
		$name=test_input($_POST['name']);
		$pid=$_POST['pid'];
		$catid=$_POST['catid'];
		$price=test_input($_POST['price']);
		$description=test_input($_POST['description']);
	
		$stmt = $dbh->prepare("Update products set name=?,price=?,description=? where pid =?and catid=?");  		    
			
		$target_dir = "Images/";
		$target_file = $target_dir . basename($_FILES['image']['name']);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$target_name = $target_dir . $pid;
		if(empty($name))
		{
			echo "Please input product name!";
		}
		else if(empty($price))
		{
			echo "Please input product price!";
		}
		else if(empty($_FILES['image']))
		{
			echo "Please attach product image!";
		}
		else if(!preg_match("/([A-Za-z0-9()])+/",$name))
		{
			echo "Please input valid product name!";
		}
		else if(!is_numeric($price))
		{
			echo "Please input valid product price!";
		}
		else if(!empty($description)&&!preg_match("/([A-Za-z0-9()])+/",$name))
		{
    			$check = getimagesize($_FILES['image']['tmp_name']);
    			if($check !== false) {
        			echo "File is an image - " . $check['mime'] . ".\n";
        			$uploadOk = 1;
    			} else {
        			echo "File is not an image.\n";
        			$uploadOk = 0;
    			}
		}
		// Check if file already exists
		if (file_exists($target_name)) {
    			if(unlink($target_name)){
    				$uploadOk = 1;
			}
		}
		// Check file size
		if ($_FILES['image']['size'] > 10485760) {
    			echo "Sorry, your file is too large.\n";
    			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "gif" ) {
    			echo "Sorry, only JPG, PNG & GIF files are allowed.\n";
    			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			//print_r($_FILES);
    			echo "Sorry, your file was not uploaded.\n";
		// if everything is ok, try to upload file
		} else {
			try{
				$stmt->execute(array($name,$price,$description,$pid,$catid));
				echo "Product ".$name." is updated!\n";
			} catch (PDOException $e) {
				echo "Product ".$name." update fail.\n";
				printf("DatabaseError: %s\n", $e->getMessage());
    			}
			if ($_FILES["image"]["error"] > 0) {
				echo "Error: " . $_FILES["image"]["error"] . "<br>";
			} else {
				if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_name)){
					echo "Uploaded File :" . $target_name;
					//echo "<pre>";
					//print_r($_POST);
					//print_r($_FILES);
				}
			else { echo "Fail"; } }
    			//if (move_uploaded_file($_FILES['image']['tmp_name'], $target_name)) {
			//	echo "The file ". basename( $_FILES['image']['name']). " has been uploaded.\n";
    			//} else {
			//	echo "Sorry, there was an error uploading your file.\n"  ;
    			//}
		}
}
if(isset($_POST['remove_product'])){
	try{
		$stmt=$dbh->prepare("DELETE FROM products WHERE pid=?;");
		$stmt->execute(array($_POST['pid']));
		echo "Product is deleted.\n";
	} catch (PDOException $e) {
		printf("Error: %s\n", $e->getMessage());
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge chrome=1">
  <meta charset="utf-8">
  <link href="Stylesheets/style.css" rel="stylesheet" type="text/css" />
  <title>Admin panel</title>
</head>
<body>
<fieldset>
<legend>Log off</legend>
<form method="POST" action="login.php">
<div><input type = "submit" name = "logoff" Value = "Log off" /></div>
</form>
</fieldset>
<fieldset>
<legend>Change password</legend>
<form method="POST">
<ul>
<li>
<label>Email *</label>
<input type = "email" name = "email" required/>
</li>
<li>
<label>Enter old password *</label>
<input type = "password" name = "oldpwd"  required/>
</li>
<li>
<label>Enter your new password * </label>
<input type = "password" name = "newpwd"  required/>
</li>
<li>
<label>Retype your new password * </label>
<input type = "password" name = "newpwd2" required/>
</li>
<div><input type = "submit" name = "changepwd" Value = "Change Password" required/></div>
</ul>
</form>
</fieldset>
<br>
<br>
<fieldset>
<legend>Category Admin Panel</legend>

<fieldset>
  <legend>New category</legend>
  <form method="POST">
  <ul>
    <label>Name * </label>
    <input type="text" pattern="[A-Za-z0-9()]+" name="name" placeholder="Only letters/numbers/round brackets" maxlength="255" size="35" required />
    <div><input type="submit" name="new_category" Value="Add" /></div>
    </ul>
  </form>
</fieldset>

<fieldset>
<legend>Edit a category</legend>
<form method="POST" >
  <ul>
    <li>
      <label>Category * </label>
      <?php 
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   echo "<select required name='catid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['catid'] . "'>" . $row['name'] . "</option>";
       		}
			echo "</select>";
	   }
      ?>
    </li>
    <li><label>New Name * </label>
      <input type="text" pattern="[A-Za-z0-9()]+" name="name" placeholder="Only letters/numbers/round brackets" maxlength="255" size="35" required />
    </li>
    <div><input type="submit" name="edit_category" Value="Update" /></div>
  </ul>
</form>
</fieldset>


<fieldset>
<legend>Remove a category</legend>
<form method="POST" >
<ul>
<label>Category * </label>
      <?php 
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   echo "<select required name='catid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['catid'] . "'>" . $row['name'] . "</option>";
       		}
			echo "</select>";
	   }
      ?>
<div><input type="submit" name="remove_category" Value="Remove" /></div>
</ul>
</form>
</fieldset>
</fieldset>
<br>
<br>
<fieldset>
<legend>Product Admin Panel</legend>
<fieldset>
<legend>New product</legend>
<form method="post" enctype="multipart/form-data">
<ul>
<li>
<label>Category * </label>
      <?php 
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   echo "<select required name='catid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['catid'] . "'>" . $row['name'] . "</option>";
       		}
			echo "</select>";
	   }
      ?>
</li>
<li>
<label>Name * </label>
<input type="text" pattern="[A-Za-z0-9()]+" name="name" placeholder="Only letters/numbers/round brackets" maxlength="255" size="35" required />
</li>
<li>
<label>Price * </label>
<input type="number" name="price" required />
</li>
<li>
<label>Description </label>
<div><textarea name="description" pattern="[A-Za-z0-9()]+" placeholder="Only letters/numbers/round brackets" maxlength="500" cols="35"></textarea></div>
</li>
<li>
<label>Image * (format: jpg/gif/png, size: <=10MB)</label>
<input type="file" name="image" accept="image/jpg, image/gif, image/png" required />
</li>
<div><input type="submit" name="new_product" value="Add" /></div>
</ul?
<div id="status"></div>

</form>
</fieldset>
<fieldset>
<legend>Edit a product</legend>
<form method="post"  enctype="multipart/form-data">
<ul>
<li>
<label>Product * </label>
<?php 
	   $stmt = $dbh->prepare("select * from products;");
	   if($stmt->execute()){
		   echo "<select required name='pid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['pid'] . "'>" . $row['name'] . "</option>";
		   }
		   echo "</select>";
       }
      ?>
</li>
<li>
<label>Category * </label>
      <?php 
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   echo "<select required name='catid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['catid'] . "'>" . $row['name'] . "</option>";
       		}
			echo "</select>";
	   }
      ?>
</li>
<li>
<label>Name * </label>
<input type="text" pattern="[A-Za-z0-9()]+" name="name" placeholder="Only letters/numbers/round brackets" maxlength="255" size="35"required />
</li>
<li>
<label>Price *</label>
<input type="number" name="price" required />
</li>
<li>
<label>Description</label>
<div><textarea name="description" pattern="[A-Za-z0-9()]+" placeholder="Only letters/numbers/round brackets"maxlength="500" cols="35"></textarea></div>
</li>
<li>
<label>Image * (format: jpg/gif/png, size: <=10MB)</label>
<input required type="file" name="image" accept="image/jpg,image/gif,image/png"  />
</li>
<div><input type="submit" name="edit_product" value="Update" /></div>
</ul>
<div id="status"></div>

</form>
</fieldset>
<fieldset>
<legend>Remove product</legend>
<form method="post" >
<ul>
<label>Product * </label>
<?php 
	   $stmt = $dbh->prepare("select * from products;");
	   if($stmt->execute()){
		   echo "<select required name='pid'>";
		   echo "<option />";
		   while ($row = $stmt->fetch()) {
			   echo "<option value='" . $row['pid'] . "'>" . $row['name'] . "</option>";
		   }
		   echo "</select>";
       }
      ?>
<div><input type="submit" name="remove_product" value="Remove" /></div>
</ul>
</form>
</fieldset>
</fieldset>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>
<script src="http://malsup.github.com/jquery.form.js"></script>
<script>
$(document).ready(function() { $('form').ajaxForm({
complete: function(xhr) { $('#status').html(xhr.responseText);
} });
}); </script>
</body>
</html>
<?php
$dbh=null;
?>



