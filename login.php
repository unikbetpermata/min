<?php
session_start();


$submit = "";

$status = "OK";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $email = $_POST['email'];
  $password = $_POST['password'];


  if (empty($email)) {
    $msg .= "<center><font  size='4px' face='Verdana' size='1' color='red'>Please Enter Your email. </font></center>";


    $status = "NOTOK";

  }
  if (preg_match('/[\'^£$%&*()}{@#~?><>,;|=_+¬-]/', $email)){
    $msg .= "<center><font  size='4px' face='Verdana' size='1' color='red'>Please Enter Your email. </font></center>";
    $status = "NOTOK";
  }


  if (empty($password)) {
    $msg .= "<center><font  size='4px' face='Verdana' size='1' color='red'>Please Enter Your password.</font></center>";

    $status = "NOTOK";
  }

  if ($status == "OK") {

    include('db_connect.php');


//   include('db_connect.php');
//echo "SELECT * FROM users WHERE email='$email' and password='$password'";

    $result = mysqli_query($con, "SELECT * FROM usersweb WHERE email='$email' and password='$password'");

    $count = mysqli_num_rows($result);

    if ($count == 1) {

      $row = mysqli_fetch_array($result);

      $_SESSION['email'] = $row['email'];
      $_SESSION['key']=mt_rand(1000,9999);
      $_SESSION['user_type'] = $row['user_type'];
      $_SESSION['name'] = $row['name'];
      $_SESSION['password']=$row['password'];
      $_SESSION['nid']=0;
      $_SESSION['word_id']=0;
      
     header("location:index.php");      
     

    } else {


      $msg = "<center><font  size='4px' face='Verdana' size='1' color='red'>Wrong Email or Password !!!.</font></center>";

    }
  }

}


?>

<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from preschool.dreamguystech.com/template/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 01 Jun 2023 05:05:23 GMT -->
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>www.dalam.mis-maf.gov.la</title>

<link rel="shortcut icon" href="assets/img/favicon.png">

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&amp;display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">

<link rel="stylesheet" href="assets/plugins/feather/feather.css">

<link rel="stylesheet" href="assets/plugins/icons/flags/flags.css">

<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

<link rel="stylesheet" href="assets/css/style.css">

<style type="text/css">
    @import url("LAOS/stylesheet.css");
		body,td,th ,h3{
			font-family: LAOS;
		}

		 @import url("LAOS/stylesheet.css");
		.save{
			font-family: LAOS;
		}
</style>
<style type="text/css">
		.auto-style1 {
			font-family: LAOS;
		}
	</style>
    
</head>
<body>

<div class="main-wrapper login-body">
<div class="login-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="assets/img/login.png" alt="Logo">
<br><br><br><br><br><br>
</div>
<div class="login-right">
<div class="login-right-wrap">
<h1 class="uto-style1"><label><span class="login-primary auto-style1">ຍິນດີຕອນຮັບເຂົ້າລະບົບ</span></label> </h1>
<p class="account-subtitle auto-style1">ທ່ານໄດ້ລົງທະບຽນຫຼືຍັງ? <a href="register.php">ລົງທະບຽນນຳໃຊ້</a></p>
<h2><label><span class="login-primary auto-style1">ເຂົ້າລະບົບ</span></label></h2>

<form action="login.php" method="post">
<div class="form-group">
<label>ຊື່ນຳໃຊ້ <span class="login-danger">*</span></label>
<input class="form-control" type="text" name="email">
<span class="profile-views"><i class="fas fa-user-circle"></i></span>
</div>
<div class="form-group">
<label>ລະຫັດຜ່ານ <span class="login-danger">*</span></label>
<input class="form-control pass-input" type="text"   name="password">
<span class="profile-views feather-eye toggle-password"></span>
</div>
<div class="forgotpass">
<div class="remember-me">
<label class="custom_check mr-2 mb-0 d-inline-flex remember-me"> ຈື່ລະຫັດຜ່ານ
<input type="checkbox" name="radio">
<span class="checkmark"></span>
</label>
</div>
<a href="forgot-password.php">ລືມລະຫັດຜ່ານ?</a>
</div>
<div class="form-group">
    <?php
          if ($_SERVER["REQUEST_METHOD"] == "POST") {
            echo "<div  align='center'>" . $msg . "</div";
          }
          ?>
</div>
</form>

<div class="login-or">
<span class="or-line"></span>
</div>

</div>
</div>
</div>
</div>
</div>
</div>


<script src="assets/js/jquery-3.6.0.min.js"></script>

<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/feather.min.js"></script>

<script src="assets/js/script.js"></script>
</body>

<!-- Mirrored from preschool.dreamguystech.com/template/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 01 Jun 2023 05:05:24 GMT -->
</html>
