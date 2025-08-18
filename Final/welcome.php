<?php
include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';
// Initialize the session
session_start();
require_once "config.php";
//basic data query
$sql3 = "SELECT * FROM users WHERE username = '" . $_SESSION['username'] . "' ";
$result3 = mysqli_query($link, $sql3);
$basics = mysqli_fetch_assoc($result3);
//echo $_COOKIE["id"];

// Check if the user is logged in, if not then redirect him to login page
if ( (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) )
{
    header("location: login.php"); 
    exit;
}


if(isset($_POST['del']) && 
   $_POST['del'] == 'Yes') {
	mysqli_query($link, "DELETE FROM users WHERE username = '" . $_SESSION["username"] . "';");
	header("location: logout.php");
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
    <meta charset="UTF-8" http-equiv="refresh" content="300;url=logout.php"/> 
	<title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<style>
		html {
			height:200%;
			width:100%;
			margin: auto;
			overflow: scroll;
		}
		body {
			background-color : #0f7bff;
			background-image: linear-gradient(#0f7bff,#d6d6d6);
			font-size: 14px;
			font-family: "Lucida Console", "Courier New", monospace;
			margin-left: 25%;
			margin-top: 50px;
			}
		.wrapper {
			width: 360px;
			padding: 20px;
			background-image: linear-gradient( #0f7bff,#d6d6d6,#ff9efa);
			position: absolute;
			margin-left: 25%;
			margin-top: 140px;
			border-radius: 25px;
			border: 2px solid #d6d6d6;
			padding: 20px;
			}
		.btn {
			rgb(32,34,33)
			border: none;
			color: white;
			padding: 15px 32px;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			font-size: 16px;
			margin: 4px;
			cursor: pointer;
			
			margin-top: 20px;
		}
	a:link {
  color: black;
  background-color: transparent;
  text-decoration: none;
}
a:hover {
  color: red;
  background-color: transparent;
  text-decoration: underline;
}
.my-5 {
color: black;
}
.my-5:hover {
  
  text-decoration: underline;
}

		</style>
		
	</head>
		<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
  <body>	
  
    <h1 class="my-5">
	
	
	<?php 
	date_default_timezone_set('America/Los_Angeles');
	$date = date('m-d-Y');		 
	$time_in_12_hour_format = date("h:i a", strtotime(date("h:i a")));
		$greeting = "";
		if (date('H') < 12) {
			$greeting = "Good morning";
		}
		else if (date('H') >= 12 && date('H') < 18   ) {
			$greeting = "Good afternoon";
		}
		else if (date('H') >= 18) {
			$greeting = "Good evening";
		}
		echo $greeting."," ;
	
	?>
	
	<?php 
	echo nl2br(htmlspecialchars($basics["first_name"] . " " . $basics["last_name"]. ".\nUsername: " . $basics["username"] . ".\r\n" . " Your Birthday is: " . 
	date("m-d-Y", strtotime($basics["dob"]))
	)); 
	echo nl2br(htmlspecialchars(".\n" ."The server time is: ". $time_in_12_hour_format));
	echo nl2br(htmlspecialchars(".\nThe current date: " . $date));
	?>.
	<br>
	You have logged in 
	<?php print($basics["count"]);print "\r\n";?> times. <br>
	Last Login: <?php print date("m-d-Y", strtotime($basics["created_at"]));print "\r\n";?>...</h1>
	<a download href="/file/company_confidential_file.txt"> Download company_confidential_file.txt</a>
	<br>
	<br>
	
	
  
	<form  method="post"  >
	<input type="checkbox"  name="2fa" value="Yes" ><b>Toggle 2FA</b> </input>
	<input type="submit" name="formSubmit2" value="Update 2FA Status" class="btn-secondary btn-sm" />
	
	<?php
	
	ob_start();
	if($basics["tfaen"] == 0 && isset($_POST['formSubmit2']) &&$_POST["2fa"]!="Yes") {ob_end_clean(); echo "2FA is already Disabled."; }
	else if (isset($_POST['formSubmit2']) && $_POST['2fa']!='Yes' && $basics["tfaen"] == 1 )
{
	ob_end_clean();
	echo "2FA has been Disabled";
    mysqli_query($link, "UPDATE users SET tfaen=0 WHERE username = '" . $basics["username"] . "';");
    mysqli_query($link, "UPDATE users SET tfa='0' WHERE username = '" . $basics["username"] . "';");
}
else if($basics["tfaen"] == 1) {ob_end_clean(); echo "2FA is already Enabled. Your secret: ".$basics["tfa"];}
	?>
	<?php if(isset($_POST['2fa']) && $basics["tfaen"] == 0 && $_POST["2fa"]=="Yes")  : ?>
		
		<div class="form-group" id="myDIV">
		<b><label>2FA has been Enabled. <br> Remember this Google Authenticator Code:</label>
		<?php 
			$g = new \Google\Authenticator\GoogleAuthenticator();
			$secret = str_shuffle('XVQ2UIGO75XRUKJ2');
			echo (htmlspecialchars($secret));
			mysqli_query($link,"UPDATE users SET tfaen=1 WHERE username = '".$basics["username"]."';");
			mysqli_query($link,"UPDATE users SET tfa='".$secret ."' WHERE username = '".$basics["username"]."';");   
			$url =  \Google\Authenticator\GoogleQrUrl::generate(urlencode($basics["username"]), urlencode($secret),urlencode("COMP424"));
		?>	
		</b> 
		<br>
		<img src = "<?php echo $url; ?>" alt = "QR Code" />
		</div>
		<?php endif; ?>
	</form>
	<br>
	<form  method="post">
	<input type="checkbox"   name="del" value="Yes" > <b>Delete Account</b></input>
	<input type="submit" name="formSubmit" value="Are You Sure?" class="btn-secondary btn-sm" />
	</form>
	
	



	<br>
     <p>
	   <a href="logout.php" class="btn btn-secondary" value="Submit"><b>Sign Out</b></a>
      <a href="reset-password.php" class="btn btn-secondary"><b>Reset Your Password</b></a>
    </p>
  </body>
</html>