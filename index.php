<?php
include('model/handlers/dictionary_handler.php');
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
    /* Set black background color, white text and some padding */
    footer {
      background-color: #555;
      color: white;
      padding: 15px;
    }
    .link-unstyled, .link-unstyled:hover {
        font-style: inherit;
        color: inherit;
        background-color: transparent;
        font-size: inherit;
        text-decoration: none;
        font-variant: inherit;
        font-weight: inherit;
        line-height: inherit;
        font-family: inherit;
        border-radius: inherit;
        border: inherit;
        outline: inherit;
        box-shadow: inherit;
        padding: inherit;
        vertical-align: inherit;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>                        
      </button>
      <a class="navbar-brand" href="<?php echo HOMEPAGE; ?>">Urban Dictionary</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
     
      <ul class="nav navbar-nav navbar-right">
        <li><a href="pages/user/newentry.php" data-toggle="tooltip" title="Add new entry!"><span class="glyphicon glyphicon-plus"></span></a></li>
        <li><a href="pages/user/login.php" data-toggle="tooltip" title="Profile!"><span class="glyphicon glyphicon-user"></span></a></li>
      </ul>
      <form class="navbar-form" role="search">
        <div class="form-group input-group col-md-9">
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="search_key" class="form-control" placeholder="Type any word..">
            <span class="input-group-btn">
              <button class="btn btn-default" type="submit">
                <span class="glyphicon glyphicon-search"></span>
              </button>
            </span>
          </form>
        </div>
      </form>
    </div>
  </div>
</nav>
  
<div class="container text-center">    
  <div class="row">
    <div class="col-sm-3 well">
      <div class="well text-left">
        <?php include('pages/dictionary/topic_preferences.php'); ?>
      </div>
      <div class="well">
        <?php include('pages/dictionary/topic_list.php'); ?>
      </div>
    </div>
    <div class="col-sm-9">
      <?php
        if(isset($_REQUEST['search_key']) && !empty($_REQUEST['search_key'])) {
          include('pages/dictionary/dictionary_search.php');
        } else {
          include('pages/dictionary/entry_list.php');
        }
      ?>
    </div>
  </div>
</div>
</body>
</html>
