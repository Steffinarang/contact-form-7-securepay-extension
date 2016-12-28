<?php
  /* server timezone */
define('CONST_SERVER_TIMEZONE', 'UTC');
 
/* server dateformat */
define('CONST_SERVER_DATEFORMAT', 'YmdHis'); 
?>

<?php
/**
 * Converts current time for given timezone (considering DST)
 *  to 14-digit UTC timestamp (YYYYMMDDHHMMSS)
 *
 * DateTime requires PHP >= 5.2
 *
 * @param $str_user_timezone
 * @param string $str_server_timezone
 * @param string $str_server_dateformat
 * @return string
 */
function now($str_user_timezone, $str_server_timezone = CONST_SERVER_TIMEZONE, $str_server_dateformat = CONST_SERVER_DATEFORMAT) {
  // set timezone to user timezone
  date_default_timezone_set($str_user_timezone);
 
  $date = new DateTime('now');
  $date->setTimezone(new DateTimeZone($str_server_timezone));
  $str_server_now = $date->format($str_server_dateformat);
 
  // return timezone to server default
  date_default_timezone_set($str_server_timezone);
 
  return $str_server_now;
}

// merchant info 
$EPS_MERCHANT = "ABC0001";
$transactionPassword = "abc123";

// card info
$EPS_CARDNUMBER = "4444333322221111";
$EPS_EXPIRYMONTH = "12";
$EPS_EXPIRYYEAR = "2017";
$EPS_CCV = "123";


$EPS_REFERENCEID = "100";
$EPS_TXNTYPE="0";
$EPS_AMOUNT = "1.00";
$EPS_TIMESTAMP = now("UTC",CONST_SERVER_TIMEZONE,CONST_SERVER_DATEFORMAT);
$EPS_RESULTURL = "https://en.wikipedia.org/";
// $EPS_RESULTURL = "https://api.securepay.com.au";

/*
The Fingerprint is a SHA1 hash of the above fields plus the SecurePay Transaction Password in this order with a pipe separator “|”:
-> EPS_MERCHANTID
-> Transaction Password (supplied by SecurePay Support)
-> EPS_TXNTYPE
-> EPS_REFERENCEID
-> EPS_AMOUNT
-> EPS_TIMESTAMP
*/
$EPS_FINGERPRINT = sha1($EPS_MERCHANT."|".$transactionPassword."|".$EPS_TXNTYPE."|".$EPS_REFERENCEID."|".$EPS_AMOUNT."|".$EPS_TIMESTAMP);
?>
<div id="securepayForm">
  <form action="https://api.securepay.com.au/test/directpost/authorise" method=:"post">
    <input type="hidden" name="EPS_MERCHANT" value="<?php echo $EPS_MERCHANT; ?>">
    <input type="hidden" name="EPS_TXNTYPE" value="<?php echo $EPS_TXNTYPE; ?>">
    <input type="hidden" name="EPS_REFERENCEID" value="<?php echo $EPS_REFERENCEID; ?>">
    <input type="hidden" name="EPS_AMOUNT" value="1.00">
    <input type="hidden" name="EPS_TIMESTAMP" value="<?php echo $EPS_TIMESTAMP;?>">
    <input type="hidden" name="EPS_FINGERPRINT" value="<?php echo $EPS_FINGERPRINT;?>">
    <input type="hidden" name="EPS_RESULTURL" value="<?php echo $EPS_RESULTURL; ?>">

    <!-- Card Info -->
    EPS_CARDNUMBER<input type="text" name="EPS_CARDNUMBER" value="<?php echo $EPS_CARDNUMBER;?>"><br><br>
    EPS_EXPIRYMONTH<input type="text" name="EPS_EXPIRYMONTH" value="<?php echo $EPS_EXPIRYMONTH;?>"><br><br>
    EPS_EXPIRYYEAR<input type="text" name="EPS_EXPIRYYEAR" value="<?php echo $EPS_EXPIRYYEAR;?>"><br><br>
    EPS_CCV<input type="text" name="EPS_CCV" value="<?php echo $EPS_CCV;?>"><br><br>
    <input type="submit" name="submit" value="Submit">

  </form>
</div>
