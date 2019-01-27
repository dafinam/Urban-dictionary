<?php
require_once('../user/session.php');
include('../../model/handlers/user_handler.php');

/* Only admin users are able to see this page */
if(!isset($_SESSION['user_data']['role']) || empty($_SESSION['user_data']['role']) || $_SESSION['user_data']['role'] != 'admin') {
    header("location: ../user/index.php");
    exit;
}

$error_msg = "";

/* Handle Request for deleting a user */
if(isset($_REQUEST['deleteUser']) && !empty($_REQUEST['deleteUser'])) {
    //DELETING User
    $deleted = deleteUser($_SESSION['user_id'], $_REQUEST['deleteUser']);
    if($deleted['status'] == 200) {
        header("location: users.php");
    } else {
        $error_msg = $deleted['msg'];
    }
}

/* Fetch all system users */
$user_list = getAllSystemUsers($_SESSION['user_id']);
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
      <a class="navbar-brand" href="#">Urban Dictionary</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
     
        <ul class="nav navbar-nav navbar-right">
            <li><a href="../user/index.php" data-toggle="tooltip" title="Home!"><span class="glyphicon glyphicon-home"></span></a></li>
        </ul>
    </div>
  </div>
</nav>
  
<div class="container text-center">    
  <div class="row">
    <div class="col-sm-3 well">
      <div class="well text-left">
        <p>Quick Links</p>
        <p><a href="../user/profile.php">Edit Profile</a></p>
        <p><a href="../user/index.php" >Topic List</a></p>
        <p><a href="../user/newentry.php" >Create New Entry</a></p>
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
                        <strong>ERROR!</strong> <?php echo $error_msg; ?>!
                    </div>
                    <?php } ?>

                    <h3>System Users</h3>
                    <br>
                    <div>
                        <table id="users" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th> Name </th>
                                    <th> Lastname </th>
                                    <th> Username </th>
                                    <th> Role </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                for ($x = 0; $x < count($user_list); $x++) {
                                    $tableRow = "<tr>";
                                    $tableRow .= "<td>" . $user_list[$x]["name"] . "</td>";
                                    $tableRow .= "<td>" . $user_list[$x]["lastname"] . "</td>";
                                    $tableRow .= "<td>" . $user_list[$x]["username"] . "</td>";
                                    $tableRow .= "<td>" . $user_list[$x]["role"] . "</td>";
                                    $tableRow .= "<td>
                                    <a
                                    onClick=\"javascript: return confirm('Are you sure you want to delete this user?');\"
                                    href='users.php?deleteUser=".$user_list[$x]["id"]."' class='btn btn-danger'> Delete 
                                    </a>
                                    </td>";
                                    $tableRow .= "</tr>";
                                    echo $tableRow;
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../../assets/js/jquery-1.12.4.js"></script>
<script src="../../assets/js/jquery.dataTables.min.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#users').DataTable();
});
</script>
</body>
</html>
