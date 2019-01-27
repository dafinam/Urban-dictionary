<?php
require_once('session.php');
require_once('../../model/DB/config.php');
include('../../model/handlers/topic_handler.php');
 
// Define variables and initialize with empty values
$topic_name = "";
$topic_name_err = "";

/* Handle Request for creating new topic */
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $topic_name = trim($_POST["topic_name"]);

    // Check if username is empty
    if(empty(trim($_POST["topic_name"]))){
        $topic_name_err = 'Please enter name for the topic.';
    } else {
        $exists = isTopicDescriptionValid($topic_name);
        if($exists['status'] == 500) {
            $topic_name_err = $exists['msg'];
        }
    }

    // Check input errors before inserting in database
    if(empty($topic_name_err)){
        $created = createNewTopic($topic_name, $_SESSION['user_id']);
        if($created['status'] == 200) {
            header("location: index.php");
        } else {
            $topic_name_err = $created['msg'];
        }
    }
}

/* Handle Request for deleting topic*/
if(isset($_REQUEST['deleteTopic']) && !empty($_REQUEST['deleteTopic'])) {
    $topicToDelete = $_REQUEST['deleteTopic'];
    $deleted = deleteTopic($topicToDelete);
    if($deleted['status'] == 200) {
        header("location: index.php");
    } else {
        $topic_name_err = $deleted['msg'];
    }
}

/* Get list of topics based on the user role */
if($_SESSION['user_data']['role'] == 'admin') {
    $topicList = getAllTopicEntryMappings();
} else {
    $topicList = getTopicEntryMappingsByUser($_SESSION['user_id']);
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
        <p><a href="#" data-toggle="modal" data-target="#newTopicModal">Create New Topic</a></p>
        <p><a href="newentry.php" >Create New Entry</a></p>
        <?php if($_SESSION['user_data']['role'] == 'admin') { ?>
        <p><a href="../admin/users.php" >User List</a></p>
        <?php } ?>
      </div>
    </div>
    <div class="col-sm-9">
      <div class="row">
        <div class="col-sm-12 border">
            <div class="panel panel-default text-left">
                <div class="panel-body">
                    <!-- Post Alert Messages here when creating new topics -->
                    <?php if (!empty($topic_name_err)) {?>
                    <div class="alert alert-danger">
                        <strong>ERROR!</strong> <?php echo $topic_name_err; ?>!
                    </div>
                    <?php } ?>

                    <h3>Your Topics</h3>
                    <br>
                    <div>
                        <table id="topics" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th> Topic Name </th>
                                    <th> Nr of entries</th>
                                    <th> CreatedAt </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                for ($x = 0; $x < count($topicList); $x++) {
                                    $tableRow = "<tr>";
                                    $tableRow .= "<td>" . $topicList[$x]["description"] . "</td>";
                                    $tableRow .= "<td>" . $topicList[$x]["entry_count"] . "</td>";
                                    $tableRow .= "<td>" . $topicList[$x]["createdAt"] . "</td>";
                                    $tableRow .= "<td>
                                    <a
                                    onClick=\"javascript: return confirm('Deleting topic will delete all its corresponding entries. Are you sure you want to delete the topic?');\"
                                    href='index.php?deleteTopic=".$topicList[$x]["id"]."' class='btn btn-danger'> Delete 
                                    </a>
                                    <a href='entries.php?topic_id=".$topicList[$x]["id"]."' class='btn btn-info'>Entry List </a>
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

<!-- Modal -->
<div class="modal fade" id="newTopicModal" tabindex="-1" role="dialog" aria-labelledby="newTopicModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Create New Topic</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($topic_name_err)) ? 'has-error' : ''; ?>">
                    <label>Topic Name</label>
                    <input type="text" name="topic_name" class="form-control" value="<?php echo $topic_name; ?>">
                    <span class="help-block"><?php echo $topic_name_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>


<script src="../../assets/js/jquery-1.12.4.js"></script>
<script src="../../assets/js/jquery.dataTables.min.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#topics').DataTable();
});
</script>
</body>
</html>
