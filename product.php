<?php
//error_reporting(0);
$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$password = 'helloworld123';
try {
	$dbh = new PDO($dsn,$user,$password);
} catch (PDOException $e) {
	printf("DatabaseError: %s", $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge chrome=1">
  <meta charset="utf-8">
  <link href="Stylesheets/style.css" rel="stylesheet" type="text/css" />
  <title>Product</title>
</head>
<header>
    <li> <a href="index.php">BLANC & ECLARE</a></li>
    <li> <div id="cart" class="cart-link">Shopping Cart 
	<form id="cartform" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_cart">
	<input type="hidden" name="upload" value="1">
	<input type="hidden" name="business" value="yangdian0503-facilitator@gmail.com"> 
	<input type="hidden" name="charset" value="big5">
	<input type="hidden" name="currency_code" value="HKD">
	<input type="hidden" name="invoice" value="">
	<input type="hidden" name="custom" value="">
    	<div class="cartinfo" id="cart-content"> </div> 
	</form>
    </li>
</header>
<nav>
  <p>Categories </p>
  <?php 
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   while ($row = $stmt->fetch()) {
			   echo "<br><li><a href=category.php?catid=".$row['catid']. ">" . $row['name'] . "</li>";
       		}
	   }
   ?>
</nav>
<section class="products">
       <a class="title" href="index.php">Home</a>&gt;<a class="title" href="category.php?catid=<?php echo $_GET['catid']; ?>">
       <?php      			       
	   $stmt = $dbh->prepare("select * from categories where catid=?;");
	   if($stmt->execute(array($_GET['catid']))) {
		   while ($row = $stmt->fetch()) {
			   echo $row['name'];
		   }
       }
	   ?>
	   </a>&gt;
       <?php      			       
	   
	   $pid= $_GET['pid'];
	   $catid = $_GET['catid'];
	   $stmt = $dbh->prepare("select * from products where pid=?;");
	   if($stmt->execute(array($pid))) {
		   while ($row = $stmt->fetch()) {
			    $ans->ans->$row['name'] = $row['price'];
	   			echo "<a href=product.php?catid=".$catid."&pid=".$pid.">".$row['name']."</a>";
	   			echo "<div calss='product-pic'>";
	   			echo "<img src=Images/".$pid." alt='Smiley face'></div>";
	   			echo "<div id='product' class='product-detail'>";
       			echo "<h3>".$row['name']."<br><br></h3>";
	   			echo "<div class='description'>";
	   			echo "<p><span>".$row['description']."</span> <br><br></p>";
	   			echo "<strong class='price'>Unit Price: $".$row['price']."<br><br>";
				echo "<div id='id01'></div>";
	   			echo "<button type='button' onclick='addToCart(\"".$row['pid']."\")'> addToCart </button>";
	   			echo "</strong></div></div>";
		   }
       }
	   ?>
</section>
<footer>
  2015-2016 CUHK IERG 4210 &copy; Copyrights</footer>
</html>
<script src="js/cart.js"></script>
<?php
$dbh=null;
?>
