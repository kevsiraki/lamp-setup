<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (
(isset($_COOKIE["remember"]) && $_COOKIE["remember"]==1))
{
	$_POST["remember"] = "Yes";
	$_SESSION["loggedin"] = true;
	$_SESSION["username"] = $_COOKIE["username"];//$_COOKIE["username"];
	$_SESSION["id"] = $_COOKIE["id"];//$_COOKIE["username"];
    header("location: welcome.php");
    exit();
}


// Include config file
require_once "config.php";
// Define variables and initialize with empty values
$username = $password = $usernameO = "";
$username_err = $password_err = $login_err = $tfa_err = $captcha_err = "";
$in = $lock = 0;
// Change the line below to your timezone!
include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    //safely stores all login attempts. tested against SQL injection. dont allow spaces in the log, prevent buffer overlow aswell.
    if (isset($_POST["Submit"]))
    {
        date_default_timezone_set("America/Los_Angeles");
        $date = date("Y-m-d H:i:s"); //trim user inputs,remove spaces to avoid sql injections
        mysqli_query($link, " INSERT INTO login_attempts(username, password, attempt_date, ip) 
		VALUES ('" . trim(str_replace(' ', '', $_POST["username"])) . "' , '" . password_hash(trim($_POST["password"]) , PASSWORD_DEFAULT) . 
		"', '" . $date . "', '" . $_SERVER['REMOTE_ADDR'] . "' );");
		mysqli_query($link, " INSERT INTO all_login_attempts(username, password, attempt_date, ip) 
		VALUES ('" . trim(str_replace(' ', '', $_POST["username"])) . "' , '" . password_hash(trim($_POST["password"]) , PASSWORD_DEFAULT) . 
		"', '" . $date . "', '" . $_SERVER['REMOTE_ADDR'] . "' );");
		$attemptsIP = mysqli_query($link, " SELECT * FROM login_attempts where ip = '".$_SERVER['REMOTE_ADDR'] ."' "); //ip
		$attemptsUS = mysqli_query($link, " SELECT * FROM login_attempts where ip = '".$_POST["username"] ."' "); //username
		$rowUS = mysqli_num_rows($attemptsUS);
		$rowIP = mysqli_num_rows($attemptsIP);
		if($rowUS>5 || $rowIP>5) { //over 5 unsuccessful attempts from a specific ip or user/email, resets when successfully logged in.
			$lock = 1;
		}			
	}
    $sql3 = "SELECT * FROM users WHERE username = '" . $_POST['username'] . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);

    // Check if username is empty
    if (empty(trim($_POST["username"])) && isset($_POST["Submit"]))
    {
        $username_err = "Please enter username.";
    }
	
    else
    {
        $usernameO =$username = trim($_POST["username"]);
    }
    // Check if password is empty
    if (empty(trim($_POST["password"])) && isset($_POST["Submit"]))
    {
        $password_err = "Please enter your password.";
    }
    else
    {
        $password = trim($_POST["password"]);
    }
	$sql4 = "SELECT * FROM users WHERE email = '" . $username . "' ";
	$result4 = mysqli_query($link, $sql4);
	$basics4 = mysqli_fetch_assoc($result4);
	
	if(!empty($basics4['username'])) {
		$usernameO = $username;
		$username = trim($basics4['username']);
	}
	$sql3 = "SELECT * FROM users WHERE username = '" . $username . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);
	   if ($basics["tfaen"] == 1 || $basics4["tfaen"] == 1)
    {

        $g = new \Google\Authenticator\GoogleAuthenticator();
        $secret = $basics["tfa"];
        $code = trim($_POST["2fa"]);
        if ($g->checkCode($secret, $code) && isset($_POST["Submit"]))
        {
        }
        else if (!($g->checkCode($secret, $code)) && isset($_POST["Submit"]))
        {
            if (empty($code) && isset($_POST["Submit"]))
            {
                $tfa_err = " ";
            }
            else
            {
                $tfa_err = "Incorrect/Exipired.";
            }
        }
    }else{}
    // Validate credentials
    if (empty($username_err) && empty($password_err) && empty($tfa_err) && isset($_POST["Submit"]))
    {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ? ";
        $check = mysqli_query($link, "SELECT * FROM users WHERE username =  '$username'; ");
        $name = mysqli_fetch_assoc($check);
        if ($stmt = mysqli_prepare($link, $sql))
        {
			//echo $basics4['username'];
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            // Set parameters
			$param_username = $username;
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                // Store result
                mysqli_stmt_store_result($stmt);
                //check for 2FA
                if (($basics["tfaen"] == 0 ||  $basics4["tfaen"] == 0 || empty($tfa_err)))
                {$in = 0;
                    // Check if username exists, if yes then verify password
                    if (mysqli_stmt_num_rows($stmt) == 1 )
                    {
                        $in = 0;
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                        if (mysqli_stmt_fetch($stmt) && isset($_POST["Submit"]))
                        {
                            if (password_verify($password, $hashed_password) && !empty($name["email_verified_at"]))
                            {
                               $in = 1;
							   if (isset($_POST['remember']) && $_POST['remember'] == 'Yes' )
										{
											//session_start();
										setcookie("remember", 1, time() + (86400 * 30), "/"); // 86400 = 1 day
										setcookie("id", $id, time() + (86400 * 30), "/"); // 86400 = 1 day
										setcookie("username", $username, time() + (86400 * 30), "/"); // 86400 = 1 day
										}
								//else
									{ 
                                if ($basics["tfaen"] == 0 || $basics4["tfaen"] == 0)
                                {
                                    if ($lock == 0||!(empty($_SESSION["captcha_code"]) || strcasecmp($_SESSION["captcha_code"], $_POST["captcha_code"]) != 0))
                                    {
										$lock = 0;
										mysqli_query($link, " DELETE FROM login_attempts where ip = '".$_SERVER['REMOTE_ADDR'] ."' ");
										mysqli_query($link, " DELETE FROM login_attempts where username = '".$username ."' ");
                                        // Password is correct and they are verified, so start a new session
                                        session_start();
										mysqli_query($link, "UPDATE users SET count=count+1 WHERE username = '$username';");
                                        date_default_timezone_set("America/Los_Angeles");
                                        $date = date("Y-m-d H:i:s");
                                        mysqli_query($link, "UPDATE users SET created_at='$date' WHERE username = '$username';");
                                        // Store data in session variables
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["id"] = $id;
                                        $_SESSION["username"] = $username;
                                        // Redirect user
                                        header("location: welcome.php");
										
                                    }
                                    else
                                    {
                                        $captcha_err = "2FA Disabled, Login Verification: ";
                                    }
                                }
								else if ($basics["tfaen"] == 1 || $basics4["tfaen"] == 1)
                                {
									if ($lock == 0||!(empty($_SESSION["captcha_code"]) || strcasecmp($_SESSION["captcha_code"], $_POST["captcha_code"]) != 0))
                                    {
										$lock = 0;
										mysqli_query($link, " DELETE FROM login_attempts where ip = '".$_SERVER['REMOTE_ADDR'] ."' ");
										mysqli_query($link, " DELETE FROM login_attempts where username = '".$username ."' ");
										// Password is correct and they are verified, so start a new session
										session_start();
										if(isset($_POST['remember']) && 
										$_POST['remember'] == 'Yes') {
										$_SESSION["remember"] = true;
										}
										mysqli_query($link, "UPDATE users SET count=count+1 WHERE username = '$username';");
										date_default_timezone_set("America/Los_Angeles");
										$date = date("Y-m-d H:i:s");
										mysqli_query($link, "UPDATE users SET created_at='$date' WHERE username = '$username';");
										// Store data in session variables
										$_SESSION["loggedin"] = true;
										$_SESSION["id"] = $id;
										$_SESSION["username"] = $username;
										// Redirect user
										header("location: welcome.php");
									}
                                    else
                                    {
                                        $captcha_err = "Login Verification: ";
                                    }
								}
                                }
                            }
							
                            else
                            {
                                // Password is not valid, display a generic error message
                                $login_err = "Invalid credentials, or email not verified.";
                            }
                        }
                    }
                    else
                    {
                        // Username doesn't exist, display a generic error message
                        $login_err = "Invalid credentials, or email not verified.";
						
                    }
                }
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
			
        }
    }
    // Close connection
    mysqli_close($link);
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
 html {
     height:200%;
     width:120%;
     overflow: scroll;
}
 body {
     background-color : #0f7bff;
     background-image: linear-gradient(#0f7bff,#d6d6d6);
     font-size: 14px;
     font-family: "Lucida Console", "Courier New", monospace;
	  display: block;
}
 .btn {
     margin-left: -14px;
}
 .wrapper {
     width: 500px;
     padding: 20px;
     background-image: linear-gradient( #0f7bff,#d6d6d6,#9EC8FF);
     position: absolute;
     display: block;
     margin-left: 37%;
     margin-top: 40px;
     border-radius: 25px;
     border: 2px solid #d6d6d6;
     padding: 20px;
	  display: block;
}

.divider{
    width:5px;
    display:inline-block;
}

.center {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 50%;
}
     </style>
  </head>
  <body>
    <div class="wrapper" >
      <h2>Login</h2>
      <p>Please fill in your credentials to login.</p> <?php 
        if(!empty($login_err)){
            echo '
					<div class="alert alert-danger">' . $login_err . '</div>';
        }?> 
		
	
	
		<form action="
						<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
          <label>Username/Email</label>
		  
          <input type="text" name="username" class="form-control 
	
			<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $usernameO; ?>">
          <span class="invalid-feedback"> <?php echo $username_err; ?> </span>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control 
	  <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"value="<?php echo $password; ?>">
          <span class="invalid-feedback"> <?php echo $password_err; ?> </span>
        </div>
		<?php if($basics["tfaen"]==1 || $basics4["tfaen"] == 1)  : ?>
			<div class="form-group">
          <label>2FA Google Authenticator Code</label>
          <input type="2fa" name="2fa" id="2fa" class="form-control
		  <?php echo (!empty($tfa_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $code; ?>">
        <span class="invalid-feedback"> <?php echo $tfa_err; ?> </span>
		<small id="2faHelp" class="form-text text-muted">2FA is Enabled.</small>
        </div>
		<?php endif; ?>
		
		<?php 
			if ($in==1&&$lock==1): ?>
		<div class="form-group">
            <table width="400" border="0" align="left" cellpadding="5" cellspacing="1" class="table"> <?php if(isset($msg)){?><tr>
                <td colspan="2" align="left" valign="top"> <?php echo $msg;?></td>
              </tr> <?php } ?><tr>
                <td align="left" valign="top"> ReCAPTCHA:</td>
               <tr> <td><img src="captcha.php?rand=
			<?php echo rand();?>" id='captchaimg'><br><label for='message'> <small> <?php echo $captcha_err ?></small> </label>
			
			<br><input id="captcha_code" name="captcha_code" type="text"><br><small> Can't read the image? click 
			<a href='javascript: refreshCaptcha();'>here</a> to refresh.</small>
			
			</td>
			  </tr>
                <td></td>
				</tr>
				</table>
		</div>
		<?php endif ?>
		<div class="form-group">
		<input type="checkbox"  name="remember" value="Yes" 
		<?php if($_POST["remember"]=='Yes'):?> checked <?php endif; ?> > Remember me</input>
		</div>
		
		<td><input name="Submit" type="submit" onclick="return validate();"  value="Login" class="center btn btn-primary"> </td>
		<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<td><input name="verify" type="submit"  value="Sign-Up" class="btn btn-primary"></td>
		&nbsp;
		<td><input name="resetU" type="submit" value="Forgot User" class="btn btn-primary"></td>
		&nbsp;
		<td><input name="resetP" type="submit"  value="Forgot Pass" class="btn btn-primary" ></td>
		&nbsp;
		<br>
		<br>
        <p>
			<?php if (isset($_POST["verify"])  ){header("location: register.php");}?>	
			<?php if (isset($_POST["resetP"])) {header("location: fp.php");}?>
			<?php if (isset($_POST["resetU"]) ){header("location: fu.php");}?>		
		</p>
        </div>
      </form>
	  
    </div>
    <head>
      <meta charset="utf-8">
      <script type='text/javascript'>
        function refreshCaptcha() {
          var img = document.images['captchaimg'];
          img.src = img.src.substring(0, img.src.lastIndexOf("?")) + "?rand=" + Math.random() * 1000;
        }
      </script>
    </head>
  </body>
</html>
