<!doctype html>
<html lang="en">
<style>
        html {
	height:100%;
	
	}
	 body {
		 
		  background-color : #0f7bff;
		  background-image: linear-gradient(#0f7bff,#d6d6d6);
        font-size: 14px; 
		font-family: "Lucida Console", "Courier New", monospace;
		
      }
	 
	  .center {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 50%;
}
     
    </style>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Account Activation by Email Verification</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  </head>
  <body> <?php
if($_GET['key'] && $_GET['token'])
{
include "config.php";
$email = $_GET['key'];
$token = $_GET['token'];
$query = mysqli_query($link,
"SELECT * FROM users WHERE email_verification_link='".$token."' and email='".$email."';"
);
$d = date('Y-m-d H:i:s');
if (mysqli_num_rows($query) > 0) 
{
$row= mysqli_fetch_array($query);
if($row['email_verified_at'] == NULL)
{
mysqli_query($link,"UPDATE users set email_verified_at ='" . $d . "' WHERE email='" . $email . "'");
$msg = "Congratulations! Your email has been verified.";
}
else
{
$msg = "You have already verified your account with us.";
}
} 
else 
{
	$msg = "This email has been not registered with us.";
}
}
else
{
$msg = "UNKNOWN ERROR.";
}
?> <div class="container mt-3">
      <div class="card">
        <div class="card-header text-center"> User Account Activation by Email Verification </div>
        <div class="card-body">
          <p> <?php echo $msg; ?> </p>
		  <a href="login.php" class="btn-primary btn-sm">Return to Login</a>
        </div>
      </div>
    </div>
  </body>
</html>
