<?php
// Include config file
require_once '../../model/DB/config.php';
include('../../model/handlers/user_handler.php');


// Initialize the session
session_start();
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
  header("location: index.php");
  exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $error_msg = "";
 
/* Handle Login Request */
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = 'Please enter username.';
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST['password']))){
        $password_err = 'Please enter your password.';
    } else{
        $password = trim($_POST['password']);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        $validateCreds = validateUserCredentials($username, $password);
        if($validateCreds['status'] == 200) {
            session_start();
            $_SESSION['user_data'] = array(
                "name" => $validateCreds['data']['name'],
                "lastname" => $validateCreds['data']['lastname'],
                "role" => $validateCreds['data']['role']
            );

            $_SESSION['username'] = $validateCreds['data']['username'];
            $_SESSION['user_id'] = $validateCreds['data']['user_id'];
            header("location: index.php");
        } else {
            $error_msg = $validateCreds['msg'];
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Urban Dictionary</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo HOMEPAGE; ?>">Urban Dictionary</a>
    </div>
  </div>
</nav>
  
<div class="container text-center">    
  <div class="row">
    <div class="col-xs-5 col-xs-offset-4 well">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <?php if (!empty($error_msg)) {?>
            <div class="alert alert-danger">
                <strong>ERROR!</strong> <?php echo $error_msg; ?>!
            </div>
            <?php } ?>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
    </div>
</div>
</body>
</html>