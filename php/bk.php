<?php
// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
// Set this to 0 once you go live or don't require logging.
define("DEBUG", 0);
define("USE_SANDBOX", 1);
define("LOG_FILE", "ipn.log");

$dsn = 'mysql:dbname=ierg4210;host=127.0.0.1';
$user = 'ierg4210_user';
$password = 'helloworld123';
try {
        $dbh = new PDO($dsn,$user,$password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
        printf("DatabaseError: %s\n", $e->getMessage());
}

// Read POST data
// reading posted data directly from $_POST causes serialization
// issues with array data in POST. Reading raw POST data from input stream instead.
//$raw_post_data = file_get_contents('php://input');
//$raw_post_array = explode('&', $raw_post_data);
//$myPost = array();
//foreach ($raw_post_array as $keyval) {
//	$keyval = explode ('=', $keyval);
//	if (count($keyval) == 2)
//		$myPost[$keyval[0]] = urldecode($keyval[1]);
//} 
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
//if(function_exists('get_magic_quotes_gpc')) {
//	$get_magic_quotes_exists = true;
//}
$postdata = '';
foreach ($_POST as $key => $value) {
    $postdata .= PHP_EOL . " $key = $value ";      // SAVE THE COLLECTION
    $$key     = trim(stripslashes($value));        // ASSIGN LOCAL VARIABLES
    $value    = urlencode(stripslashes($value));   // ENCODE FOR BOUNCE-BACK
//	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
//		$value = urlencode(stripslashes($value));
//	} else {
//		$value = urlencode($value);
//	}
	$req .= "&$key=$value";
}
// Post IPN data back to PayPal to validate the IPN data is genuine
// Without this step anyone can fake IPN data
$ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
if(DEBUG == true) {
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
}
// CONFIG: Optional proxy configuration
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
// Set TCP timeout to 30 seconds
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
// of the certificate as shown below. Ensure the file is readable by the webserver.
// This is mandatory for some environments.
//$cert = __DIR__ . "./cacert.pem";
//curl_setopt($ch, CURLOPT_CAINFO, $cert);
$res = curl_exec($ch);
if (curl_errno($ch) != 0) // cURL error
	{
	if(DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
	}
	curl_close($ch);
	exit;
} else {
		// Log the entire HTTP response if debug is switched on.
		if(DEBUG == true) {
			error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
			error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
		}
		curl_close($ch);
}
// Inspect IPN validation result and act accordingly
// Split response headers and payload, a better way for strcmp
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));
if (strcmp ($res, "VERIFIED") == 0) {
	// check whether the payment_status is Completed
	// check that txn_id has not been previously processed
	// check that receiver_email is your PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment and mark item as paid.
	// assign posted variables to local variables
	//$item_name = $_POST['item_name'];
	//$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];
	$orderid = $_POST['invoice'];
	$custom = $_POST['custom'];
	$num_cart_items = $_POST['num_cart_items'];

    // IPN message values depend upon the type of notification sent.
    // To loop through the &_POST array and print the NV pairs to the screen:
    foreach($_POST as $key => $value) {
      echo $key." = ". $value."<br>";
    }
    // Check payment status 
    if ($payment_status != 'Completed') { 
        $errors[] .= "Payment not completed";
        //error_log("Payment not completed", 3, LOG_FILE);
    }

    // Check seller e-mail
    if ($receiver_email != 'yangdian0503-facilitator@gmail.com')  {
        $errors[] = "Incorrect seller e-mail";
        //error_log("Incorrect seller email", 3, LOG_FILE);
    }

    // Check the currency code
    if ($payment_currency != 'HKD')  {
        $errors[] = "Incorrect currency code";
        //error_log("Incorrect currency code", 3, LOG_FILE);
    }

    //check info with database
    //if ($conn->connect_error) {
    //    $errors[] = "Database connection error";
    //    //error_log("Database connection error", 3, LOG_FILE);
    //}
    //else {
        //check if transaction has been processed before
        $stmt1 = $dbh->prepare("SELECT * FROM transactions WHERE txn_id = ?");
        if ($stmt1->execute(array($txn_id))) {
            $count=$stmt1->rowCount();
        }
        if($count > 0) {
            $errors[] = "Transaction already processed";
            //error_log("Transaction already processed", 3, LOG_FILE);
        } else {
            // Transaction not processed
            //check message digest
            $message = $payment_currency.",".$receiver_email;//.$salt;

            $stmt2 = $dbh->prepare("SELECT * FROM orders WHERE orderid = ?;");
            if($stmt2->execute(array($orderid))){
		while($row=$stmt2->fetch()){
	    		$digest_db=$row['digest'];
	    		$salt_db=$row['salt'];
		}
	    }
            $message=$message.",".$salt_db;
	    $product_list = "";
            for($i = 1; $i <= $num_cart_items; $i++)
            {
                $item_name = $_POST['item_name'.$i];//product name
                $item_number = $_POST['item_number'.$i];//pid
                $quantity = $_POST['quantity'.$i];//item quantity
                $item_amount = $_POST['mc_gross_'.$i];//item total price
                $price = $item_amount/$quantity;

                $product_list =$product_list. $item_number.",".$quantity.",".$price.";";
                $message =$message.",".$item_number.",".$quantity.",".$price;
            }
            $message=$message.",".$payment_amount;
            //error_log("message in paypay is: ".$message, 3, LOG_FILE);
            $digest = hash('md5', $message);

            //compare digest from paypal with database
            if(strcmp($digest, $digest_db) == 0){
                //error_log("Message digest is valid", 3, LOG_FILE);
            }
            else {
                $errors[] = "Message digest is not valid";
                //error_log("Message digest is not valid", 3, LOG_FILE);
            }
        } 

    if(DEBUG == true) {
        error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
    }

    if (count($errors) > 0)  {
        // IPN data is incorrect - possible fraud
        // It is a good practice to send the transaction details to your e-mail and investigate manually
        //error_log("IPN failed fraud checks", 3, LOG_FILE);
        $errors[] = "IPN failed fraud checks";
    }else {
        //Paypal payment is valid
        //store transaction into database transaction table
        $stmt = $dbh->prepare("INSERT INTO transactions(txn_id, product_list) VALUES (?, ?)");
        if($stmt->execute(array($txn_id,$product_list))){
            //error_log("Store successful transactions into database", 3, LOG_FILE);
        }else{
            $errors[] = "Store transactions error";
            //error_log("Error in storing transactions into database", 3, LOG_FILE);
        } 
     }

} else if (strcmp ($res, "INVALID") == 0) {
    //echo "<script type='text/javascript'>console.log('The IPN is invalid');</script>";
    // IPN invalid, log for manual investigation
    if(DEBUG == true) {
        error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
    }
}
error_log(print_r($_POST,true));
?>
