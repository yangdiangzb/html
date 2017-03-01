<?php
//error_reporting(0);
$rec_limit = 4;
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
  <title>Category</title>
</head>
<body>
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
  	   $stmt1 = $dbh->prepare("select count(*) from products where catid=?;");
	   if($stmt1->execute(array($_GET['catid']))){
		   while($row1 = $stmt1->fetch()){
			   $rec_count = $row1[0];
		   }
	   }
	   if(isset($_GET{'page'} ) ) {
		   $page = $_GET{'page'}; 
		   $offset = $rec_limit * $page;
	   }else { 
		   $page = 0; 
		   $offset = 0;
	   }
	   $stmt = $dbh->prepare("select * from categories;");
	   if($stmt->execute()){
		   while ($row = $stmt->fetch()) {
			   echo "<br><li><a href=category.php?catid=".$row['catid']. ">" . $row['name'] . "</li>";
       		}
	   }
   ?>
</nav>
<section class="products">
    <a class="title" href="index.php">Home</a>&gt;<a class="title" href="category.php?catid=<?php echo $_GET['catid'];?>">
	<?php  
	   $stmt = $dbh->prepare("select * from categories where catid=?;");
	   if($stmt->execute(array($_GET['catid']))) {
		   while ($row = $stmt->fetch()) {
			   echo $row['name'];
		   }
       }
	 ?>
    </a>
    <ul>
    
    <?php 
	   $left_rec = $rec_count - (($page+1) * $rec_limit);		      
	   $stmt =$dbh->prepare("SELECT * FROM products where catid = ? LIMIT $offset, $rec_limit");
	   if($stmt->execute(array($_GET['catid']))) {
		   while ($row = $stmt->fetch()) {
			   echo "<li id='product'> <a href=product.php?catid=".$row['catid']."&pid=".$row['pid']."><img src=Images/".$row['pid']." alt='Smiley face'></a>";
			   echo "<div class='product-info'>";
			   echo "<a href=product.php?pid=".$row['pid']."><h3 id='product-name'>".$row['name']."</h3></a>";
			   echo "<div class='product-desc'>";
			   echo "<strong class='price' id='product-price'>$".$row['price']."&nbsp;&nbsp;<button type='button' onclick='addToCart(\"".$row['pid']."\")'> addToCart </button></strong> </div></div></li> ";
       }
	   }
	   echo "</ul>";
	
	   if ($page > 0)
		{ echo "<a class='page' href =category.php?catid=".$_GET['catid'] . "&page=" . ($page-1) . ">Last ". $rec_limit . " Records</a> "; }
		if ($left_rec > 0)
		{ echo "<a class='page' href =category.php?catid=".$_GET['catid']  . "&page=" . ($page+1) . ">Next ". $rec_limit . " Records</a>"; }

	  ?>
	
</section>
<script type="text/javascript" src="js/cart.js"></script>
</body>
<footer>
  2015-2016 CUHK IERG 4210 &copy; Copyrights</footer>
</html>

<?php
$dbh=null;
?>

