<?php
// error_reporting(E_ALL);
require_once('session.php');
include('../../model/handlers/entry_handler.php');
include('../../model/handlers/topic_handler.php');
 
// Define variables and initialize with empty values
$word = $definition = $usage = "";
$word_err = $definition_err = $usage_err = "";
 
/* Handle Request for creating new entry */
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate word field
    if(empty(trim($_POST["word"]))){
        $word_err = 'The word filed is required.';
    } else {
        $exists = check_entry_exists(trim($_POST["word"]));
        if($exists['status'] == 200) {
            $word = trim($_POST["word"]);
        } else {
            $word_err = $exists['msg'];
        }
    }
    
    // Check if definition is empty
    if(empty(trim($_POST['definition']))){
        $definition_err = 'The definition field is required.';
    } else{
        $definition = trim($_POST['definition']);
    }

    // Check if usage is empty
    if(empty(trim($_POST['usage']))){
        $usage_err = 'Provide a usage example of the word.';
    } else{
        $usage = trim($_POST['usage']);
    }
    
    // Validate credentials
    if(empty($word_err) && empty($definition_err) && empty($usage_err)){
        $insertEntryStatus = insertEntry($word, $definition, $usage, $_SESSION['user_id'], $_REQUEST['topic']);
        if($insertEntryStatus['status'] == 200) {
            header("location: entries.php?topic_id=" . $_REQUEST['topic']);
        } else {
            $word_err = $insertEntryStatus['msg'];
        }
        
    }
}

/* Get the list of topics created by the user */
$topicList = getTopicsByUser($_SESSION['user_id']);
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
  <style>
    textarea {
        resize: none;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo HOMEPAGE; ?>">Urban Dictionary</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
        <ul class="nav navbar-nav navbar-right">
        <li><a href="index.php" data-toggle="tooltip" title="Log out!"><span class="glyphicon glyphicon-circle-arrow-right"></span></a></li>
        </ul>
    </div>
  </div>
</nav>
  
<div class="container text-center">    
  <div class="row">
    <div class="col-xs-7 col-xs-offset-3 well">
        <h2>Create New Entry</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($word_err)) ? 'has-error' : ''; ?>">
                <label>Word</label>
                <input type="text" name="word" class="form-control" value="<?php echo $word; ?>">
                <span class="help-block"><?php echo $word_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($definition_err)) ? 'has-error' : ''; ?>">
                <label>Definition</label>
                <textarea rows="4" cols="50" maxlength="1000" class="form-control" name="definition" ><?php echo $definition; ?></textarea>
                <span class="help-block"><?php echo $definition_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($usage_err)) ? 'has-error' : ''; ?>">
                <label>Example Usage</label>
                <textarea rows="4" cols="50" maxlength="1000" class="form-control" name="usage" ><?php echo $usage; ?></textarea>
                <span class="help-block"><?php echo $usage_err; ?></span>
            </div>
            <div class="form-group">
                <label>Topic</label>
                <select name="topic" class="form-control">
                <?php
                    for ($x = 0; $x < count($topicList); $x++) {
                        echo "<option value='".$topicList[$x]['id']."'>".$topicList[$x]["description"]."</option>";
                    }
                ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Save Entry">
            </div>
        </form>
    </div>    
    </div>
</div>
</body>
</html>