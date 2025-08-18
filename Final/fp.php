<?php
   if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location: login.php');
    exit;
}
// Initialize the session
// Include config file
require_once "config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Define variables and initialize with empty values
$username = $email = $new_password = $confirm_password = $ans = "";
$new_password_err = $confirm_password_err = $email_err = $username_err = $ans_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
 
    // Check if email is valid
    if (empty(trim($_POST["email"])))
    {
        $email_err = "Please enter email.";
    }
    else
    {
        $email = trim($_POST["email"]);
        $query = mysqli_query($link, "SELECT * FROM users WHERE email='" . $email . "'");
        if (!$query)
        {
            die('Error: ' . mysqli_error($link));
        }
        if (mysqli_num_rows($query) == 0)
        {
            $email_err = "Email not found.";
        }
        else{
			
			$email = trim($_POST["email"]);
		}
    }

    // Check if email is validated
    $sql3 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result3 = mysqli_query($link, $sql3);
	$basics = mysqli_fetch_assoc($result3);
	
	
	$expFormat = mktime(date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y"));
	$expDate = date("Y-m-d H:i:s",$expFormat);
	$key = md5(2418*2+$email);
	$addKey = substr(md5(uniqid(rand(),1)),3,10);
	$key = $key . $addKey;
	
	

   
	if(empty($email_err)) {
	            //sends email
            $link2 = "<a href='comp424.technologists.cloud/forgot-password.php?key=" . $_POST["email"] . "&token=" . $key . "'>Reset Password</a>";
            require "phpmail/src/Exception.php";
            require "phpmail/src/PHPMailer.php";
            require "phpmail/src/SMTP.php";
            $mail = new PHPMailer(true);
            try
            {
                $mail->CharSet = "utf-8";
                $mail->IsSMTP();
                // enable SMTP authentication
                $mail->SMTPAuth = true;
                // GMAIL username
                $mail->Username = "compsciemail123@gmail.com";
                // GMAIL password
                $mail->Password = "comp424smtp123*";
                $mail->SMTPSecure = "ssl";
                // sets GMAIL as the SMTP server
                $mail->Host = "smtp.gmail.com";
                // set the SMTP port for the GMAIL server
                $mail->Port = "465";
                $mail->From = "compsciemail123@gmail.com";
                $mail->FromName = "WebMaster";
                //$mail->AddAddress('reciever_email_id', 'reciever_name');
                //var_dump($email);
                $mail->addAddress($_POST["email"], "kevin");
                $mail->Subject = "Reset your Password";
                $mail->IsHTML(true);
                date_default_timezone_set("America/Los_Angeles");
                $date = date("Y-m-d H:i:s");
                $greeting = "";
                if (date('H') < 12)
                {
                    $greeting = "Good morning";
                }
                else if (date('H') >= 12 && date('H') < 18)
                {
                    $greeting = "Good afternoon";
                }
                else if (date('H') >= 18)
                {
                    $greeting = "Good evening";
                }
                $mail->Body = " " . $greeting . " " . $basics["first_name"] . ". Click On This Link to Reset Password: " . $link2 . ". 
				(Note: If this isn't you, ignore this email). This link auto-expires in 24 hours.";
            }
            catch(phpmailerException $e)
            {
                echo $e->errorMessage();
            }
            catch(Exception $e)
            {
                header("location: login.php"); //Boring error messages from anything else!
                
            }
            if ($mail->Send())
            {
				mysqli_query($link, "DELETE FROM password_reset_temp WHERE email = '".$email."' " );
				$sql = "INSERT INTO password_reset_temp (email, keyTo, expD) VALUES (?,?,?)";
        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_key, $param_expDate);
            // Set parameters
            $param_email = $email;
            $param_key = $key;
			$param_expDate = $expDate;
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
				
            }
            else
            {
                echo "Oops! Something went wrong. Please try again later.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
	mysqli_close($link);
				
                header("location: login.php");
            }
            else
            {
                echo "Mail Error - >" . $mail->ErrorInfo;
            }
	}
}
	?>
	<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        html {
	height:200%;
	width:120%;
	margin: auto;
	overflow: scroll;
     
	}
	 body {
		 
		background-color : #0f7bff;
		background-image: linear-gradient(#0f7bff,#d6d6d6);
        font-size: 14px; 
		font-family: "Lucida Console", "Courier New", monospace;
      }
      .wrapper {
        width: 350px;
        padding: 20px;
		background-image: linear-gradient( #0f7bff,#d6d6d6,#ff9efa);
		position: absolute; /* or absolute */
		left: 40%;
		margin-top: 120px;
		border-radius: 25px;
		border: 2px solid #d6d6d6;
		padding: 20px;
      }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Reset Password</h2>
        <p>Reset Password Link will be sent to your email.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
		<div class="form-group">
          <label for="exampleInputEmail1">Email address</label>
          <input type="email" name="email"  id="email" required="" aria-describedby="emailHelp" class="form-control
		  <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
          <span class="invalid-feedback"> <?php echo $email_err; ?> </span> 
		  <small id="emailHelp" class="form-text text-muted">This serves as a re-verification token.</small>
        </div>
		<b> 
		<div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="login.php">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>
