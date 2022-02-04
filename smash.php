#!/usr/bin/php
<?php

// WELCOME TO SMASH
// Selkowitz Multifactor Authentication Snitch and Hound

// this script emails admin UI and Portal Users (chosen scopes) to remind them to set up MFA, as well as emailing the list of users to an admin

// fill out the info below 
define("SERVER", "localhost");
define("MYSQLUSER", "user");
define("MYSQLPASS", "password");
define("PORTALSCOPES","Super User,Reseller"); //comma separated list of portal scopes to check for MFA
define("EMAILTO","user@domain.tld");  // will receive list of users with MFA not enabled
define("EMAILFROM","user@domain.tld");


$headers = "From: " . EMAILFROM . "\r\n";
$headers .= "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

///////////////////////////////
// check Admin UI Users

$localDb['server'] = SERVER;
$localDb['user'] = MYSQLUSER;
$localDb['password'] = MYSQLPASS;
$localDb['db'] = 'SiPbxDomain';

$sql = "SELECT firstname, lastname, email FROM level1_access WHERE two_step!='google'";

# Get the data
$data = get_data($localDb, $sql);

// email body to admin
$body_details = "";

foreach ( $data as $key => $login ) {
// $current_logins_arr[$login['login']] = $login;
 $body_details .= "The Admin UI User " .$login['firstname'] ." " . $login['lastname'] . "does not have MFA enabled.<br>\n";

 // email user
    If ($login['email'])
    {
         mail($login['email'],'MFA is not enabled','You do not have MFA enabled on your SNAPsolution Admin UI login. Please add MFA by:<br>logging into your Admin UI <br>going to System>User Accounts and Editing your User <br>Select Google as the Multi Factor Authentication type <br>scan the 2D barcode using your authenticator app (e.g. Google Authenticator) <br>Then enter the 6 digit code and click Modify', $headers);
    }
}

//mail admin the list
if ($body_details!="")
{
    mail(EMAILTO,"Admin UI Users without MFA enabled!", $body_details,$headers);
}


///////////////////////////////
// check Portal Users 

$scopearray=explode(',', PORTALSCOPES);
$scopequery="(";

foreach ($scopearray as $scope)
{
    $scopequery .= "Scope='" . $scope . "' OR ";
}
$scopequery=substr($scopequery,0,-4) . ')'; //strip last OR and add close paren

$sql = "SELECT CONCAT(aor_user,'@',aor_host) as subscriber_id, firstname,lastname, email_address FROM SiPbxDomain.subscriber_config LEFT JOIN NsApi.multifactor_auth ON CONCAT(aor_user,'@',aor_host) = ns_id WHERE ns_id IS NULL AND " . $scopequery;

# Get the data
$data = get_data($localDb, $sql);

// email body to admin
$body_details = "";

foreach ( $data as $key => $login ) {

 $body_details .= "The Portal User " . $login['firstname'] . " " . $login['lastname'] . " " . $login['subscriber_id'] . " does not have MFA enabled.<br>\n";

    // email user
    If ($login['email_address'])
    {
         mail($login['email_address'],'MFA is not enabled','You do not have MFA enabled on your SNAPsolution Portal login. Please add MFA by:<br>logging into the Portal <br>Edit your Profile <br>Click Set Up Google Authenticator<br>scan the 2D barcode using your authenticator app (e.g. Google Authenticator) <br>Then enter the 6 digit code and password and click Save',$headers);
    }
}

//mail admin the list
if ($body_details!="")
{
    mail(EMAILTO,"Portal Users without MFA enabled!", $body_details, $headers);
}



function get_data($db_data, $sql) {

 $returner = [];

 $link = mysqli_connect($db_data['server'], $db_data['user'], $db_data['password'], $db_data['db']);

 /* check connection */
 if (!$link) {
     printf("Connect failed: %s\n", mysqli_connect_error());
     exit();
 }

 $result = mysqli_query($link, $sql) or die(mysqli_error($link)." Q=".$sql);

 if ( $result ) {

  /* fetch associative array */
  while ($row = mysqli_fetch_assoc($result)) {
   $returner[] = $row;
  }

  /* free result set */
  mysqli_free_result($result);
 }


 /* close connection */
 mysqli_close($link);

 return $returner;

}


?>
