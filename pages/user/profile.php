<?php
require_once('session.php');
include('../../model/handlers/user_handler.php');

//Define variables
$name = $_SESSION['user_data']['name']; 
$lastname = $_SESSION['user_data']['lastname'];
$username = $_SESSION['username']; 
$new_pass = $confirm_pass = "";
$name_err = $lastname_err = $username_err = $new_pass_err = $confirm_pass_err = $error_msg ="";

/* Processing profile update form data */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['profile_update'])){
 
    //Validate name and lastname
    if(empty(trim($_POST["name"]))) {
        $name_err = "Name field cannot be left empty.";
    } else {
        $name = $_POST["name"];
    }
    if(empty(trim($_POST["lastname"]))) {
        $lastname_err = "Lastname field cannot be left empty.";
    } else {
        $lastname = $_POST["lastname"];
    }

    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Username field cannot be left empty..";
    } else if(trim($_POST["username"]) != $_SESSION['username']) {
        $exists = check_user_exists(trim($_POST["username"]));
        if($exists['status'] == 500) {
            $username_err = $exists['msg'];
        } else {
            $username = trim($_POST["username"]);
        }
    }
    
    // Check input errors before inserting in database
    if(empty($name_err) && empty($lastname_err) && empty($username_err)){
        $updated = updateUserProfileData($_SESSION['user_id'], $name, $lastname, $username);
        if($updated['status'] == 200) {
            $_SESSION['user_data']['name'] = $name;
            $_SESSION['user_data']['lastname'] = $lastname;
            $_SESSION['username'] = $username;
            header("location: profile.php");
        } else {
            $error_msg = $updated['msg'];
        }
    }
}

/* Process Request for updating the password */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password_update'])){

    //Validate current 
    // Validate password
    if(empty(trim($_POST['new_pass']))){
        $error_msg = $new_pass_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST['new_pass'])) < 6){
        $error_msg = $new_pass_err = "Password must have atleast 6 characters.";
    } else{
        $new_pass = trim($_POST['new_pass']);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_pass"]))){
        $error_msg = $confirm_pass_err = 'Please confirm password.';     
    } else{
        $confirm_pass = trim($_POST['confirm_pass']);
        if($new_pass != $confirm_pass){
            $error_msg = $confirm_pass_err = 'Password did not match.';
        }
    }

    // Check input errors before inserting in database
    if(empty($new_pass_err) && empty($confirm_pass_err)){
        $password = password_hash($new_pass, PASSWORD_DEFAULT);
        $updatedPass = updateUserPass($_SESSION['user_id'], $password);
        if($updatedPass['status'] == 200) {
            header("location: profile.php");
        } else {
            $error_msg = $updatedPass['msg'];
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
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"/>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
        <a class="navbar-brand" href="<?php echo HOMEPAGE; ?>">Urban Dictionary</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
     
      <ul class="nav navbar-nav navbar-right">
        <li><a href="logout.php" data-toggle="tooltip" title="Profile!"><span class="glyphicon glyphicon-log-out"></span></a></li>
      </ul>
    </div>
  </div>
</nav>
  
<div class="container text-center">    
  <div class="row">
    <div class="col-sm-3 well">
      <div class="well text-left">
        <p>Quick Links</p>
        <p><a href="profile.php">Edit Profile</a></p>
        <p><a href="index.php" >Topic List</a></p>
        <p><a href="newentry.php" >Create New Entry</a></p>
      </div>
    </div>
    <div class="col-sm-9">
      <div class="row">
        <div class="col-sm-12 border">
            <div class="panel panel-default text-left">
                <div class="panel-body">
                    <!-- Post Alert Messages here -->
                    <?php if (!empty($error_msg)) {?>
                    <div class="alert alert-danger">
                        <strong>ERROR!</strong> <?php echo $error_msg; ?>
                    </div>
                    <?php } ?>

                    <h3>User Profile</h3>
                    <br>
                    <div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err; ?></span>
                        </div> 
                        <div class="form-group <?php echo (!empty($lastname_err)) ? 'has-error' : ''; ?>">
                            <label>Lastname</label>
                            <input type="text" name="lastname" class="form-control" value="<?php echo $lastname; ?>">
                            <span class="help-block"><?php echo $lastname_err; ?></span>
                        </div> 
                        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                            <span class="help-block"><?php echo $username_err; ?></span>
                        </div>
                        <div class="form-group">
                            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#changePassModal" >Update Password </a>
                        </div>
                        <input type="hidden" name="profile_update" />
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Update">
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="changePassModal" tabindex="-1" role="dialog" aria-labelledby="changePassModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" >Update your password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($new_pass_err)) ? 'has-error' : ''; ?>">
                    <label>New Password</label>
                    <input type="password" name="new_pass" class="form-control" value="<?php echo $new_pass; ?>">
                    <span class="help-block"><?php echo $new_pass_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($confirm_pass_err)) ? 'has-error' : ''; ?>">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_pass" class="form-control" value="<?php echo $confirm_pass; ?>">
                    <span class="help-block"><?php echo $confirm_pass_err; ?></span>
                </div>
                <input type="hidden" name="password_update" />
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
</body>
</html>