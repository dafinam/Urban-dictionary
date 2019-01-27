<?php
// Include config file
require_once('session.php');
include('../../model/handlers/entry_handler.php');
include('../../model/handlers/topic_handler.php');

$error_msg = "";

/* Get the list of entries based on the users role */
$entryList = array();
if(isset($_REQUEST['topic_id']) && !empty($_REQUEST['topic_id'])) {
    if($_SESSION['user_data']['role'] == 'admin' || userPermissionOnTopic($_REQUEST['topic_id'], $_SESSION['user_id'])) {
        $entryList = entriesByTopic($_REQUEST['topic_id']);
    }
}

/* Handle delete request */
if(isset($_REQUEST['delete_entry']) && isset($_REQUEST['entry_id'])) {
    //DELETING ENTRY
    $deleted = deleteEntry($_REQUEST['entry_id']);
    if($deleted['status'] == 200) {
        header("location: entries.php?topic_id=" . $_REQUEST['topic_id']);
    } else {
        $error_msg = $deleted['msg'];
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
        <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body>
    
    <nav class="navbar navbar-inverse">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo HOMEPAGE; ?>">Urban Dictionary</a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav navbar-right">
            <li><a href="index.php" data-toggle="tooltip" title="Home!"><span class="glyphicon glyphicon-circle-arrow-left"></span></a></li>
            </ul>
        </div>
      </div>
    </nav>
      
    <div class="container text-center">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12 border">
                    <div class="panel panel-default text-left">
                        <div class="panel-body">
                            <?php if (!empty($error_msg)) {?>
                            <div class="alert alert-danger">
                                <strong>ERROR!</strong> <?php echo $error_msg; ?>
                            </div>
                            <?php } ?>
                            <h3>List of Entries for Topic: TEST</h3>
                            <br>
                            <div>
                                <table id="entries" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th> Word </th>
                                            <th> Definition </th>
                                            <th> Example Usage </th>
                                            <th> Created At </th>
                                            <th> Action </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        for ($x = 0; $x < count($entryList); $x++) {
                                            $tableRow = "<tr>";
                                            $tableRow .= "<td>" . $entryList[$x]["word"] . "</td>";
                                            $tableRow .= "<td>" . $entryList[$x]["definition"] . "</td>";
                                            $tableRow .= "<td>" . $entryList[$x]["usage"] . "</td>";
                                            $tableRow .= "<td>" . $entryList[$x]["createdAt"] . "</td>";
                                            $tableRow .= "<td>
                                            <a
                                            onClick=\"javascript: return confirm('Are you sure you want to delete this entry?');\"
                                            href='entries.php?topic_id=".$_REQUEST['topic_id']."&delete_entry&entry_id=".$entryList[$x]["id"]."' class='btn btn-danger'>
                                            Delete 
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
    <script src="../../assets/js/jquery-1.12.4.js"></script>
    <script src="../../assets/js/jquery.dataTables.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#entries').DataTable();
    });
    </script>
    </body>
</html>