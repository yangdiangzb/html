<?php
error_reporting(0);
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
  <title> Home </title>
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
<section>
  <a class="title" href="index.php">Home</a>
  <div >
 
<p>&nbsp;</p>
<p><strong>About BLANC &amp; ECLARE<br></strong></p>
<br>
<p>Jessica Jung brings her flair for design and love of the classic aesthetic to her fashion line, BLANC &amp; ECLARE.&nbsp;BLANC &amp; ECLARE’s unique point of view is best described as the modern classic. This sensibility is revealed in each item, starting first with the beloved eyewear collection. BLANC &amp; ECLARE sunglasses are timeless yet current, understated yet unique. Meant for the fashion savvy, these versatile designs have just the right amount of the unexpected element to make it relevant, relatable and wholly BLANC &amp; ECLARE. Derived&nbsp;from the Latin root Clara, ECLARE expresses clarity and brightness, virtues highly held by Founder and Creative Director, Jessica Jung.</p>
<br>
<p>At the heart of the BLANC &amp; ECLARE design perspective is that less is more. Yet we recognize the need for that special something to make a piece exciting and modern. Our logo is the expression of this philosophy. A division sign nestled within the classic lettering of the brand name illustrates how BLANC &amp; ECLARE bridges the&nbsp;divide between the clean enduring classic and the fashion forward.</p>
<br>
<p>Defined by clean silhouettes and interesting detail, all items in the evolving line will reflect Jessica’s personal taste and inspirations. She incorporates into her own wardrobe classic pieces of clothing and&nbsp;accessories that she makes her own- a white t-shirt, crisp blouse, well-tailored jacket, the perfect little black dress, or a pair of vintage oversized tortoise sunglasses. Drawn to the enduring and all things minimalist, the BLANC &amp; ECLARE team works to ensure the collections create a cohesive line defined by the same spirit of understated chic.&nbsp;</p>
<p>BLANC &amp; ECLARE is available online and in specialty shops and exclusive retail locations across Asia.<strong></strong></p>
<p>&nbsp;</p>
<br>
<p><strong>About Jessica</strong></p>
<br>
<p>  Jessica is a multi-faceted artist who enjoys a vibrant career as a singer, actress, stage performer, model and designer. With multiple albums, singles and duets in her discography, Jessica has expanded her reach in the entertainment industry to include stage performance and acting roles. She has appeared in&nbsp;popular Korean TV shows including, most recently, her own reality show.</p>
<br>
<p>  Jessica’s personal style has long been adored by industry leaders and fans alike. As a highly pursued fashion model and style icon, her wardrobe and every look are documented, including her red carpet choices, airport fashion and day-to-day casual wear. Having spent much of her adulthood in the spotlight surrounded by renowned designers and brands, Jessica decided to join the fashion conversation with her own collections that reflect her love of the modern classic.</p>  
  </div>
<br>
<p><strong>Follow us on FACEBOOK</strong></p>
<br>
<iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FBlancAndEclareGroup%2F%3Ffref%3Dts&tabs=timeline&width=340&height=70&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=true&appId" width="340" height="70" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
</section>

<footer>
  2015-2016 CUHK IERG 4210 &copy; Copyrights</footer>
</html>
<script src="js/cart.js"></script>
<?php
$dbh=null;
?>

