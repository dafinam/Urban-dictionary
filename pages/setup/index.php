<?php
require_once('../../model/DB/DatabaseHandler.php');
require_once('../../lib/Utilities.php');

$server = $user = $password = "";
$server_err = $user_err = $password_err = $connection_err = "";
$processedData = array();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate servername
    if(empty(trim($_POST["server"]))){
        $server_err = "Please fill in the server name (Ex. localhost).";
    } else {
        $server = trim($_POST["server"]);
    }
    // Validate username
    if(empty(trim($_POST["user"]))){
        $user_err = "Please enter a db username";
    } else {
        $user = trim($_POST["user"]);
    }
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Continue if all fields are validated.
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        // Create connection
        $conn = new mysqli($server, $user, $password);
        // Check connection
        if ($conn->connect_error) {
            $connection_err = "CONNECTION REFUSED";
        } 

        if(empty($connection_err)) {
            $DBHandler = new DatabaseHandler($conn);
            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS urban_dictionary";
            $schemaCreate = $DBHandler->executeSQL($sql);
            
            //Create a mapping where we store the status of each sql entry.
            $tableHead = array(
                "entry" => "DB Entry",
                "status" => "Status"
            );

            array_push($processedData, $tableHead);
            array_push($processedData, array("entry" => "Database", "status" => empty($schemaCreate) ? "Created" : "Failed"));
            if(empty($schemaCreate)) {
                $conn = new mysqli($server, $user, $password, "urban_dictionary");
                $DBHandler->setConnection($conn);

                //Roles Table Setup
                $rolesTbl = "CREATE TABLE IF NOT EXISTS roles (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                description VARCHAR(30) NOT NULL,
                createdAt TIMESTAMP)";

                $rolesQuery = $DBHandler->executeSQL($rolesTbl);
                array_push($processedData, array("entry" => "Roles Table", "status" => empty($rolesQuery) ? "Created" : "Failed"));
                
                //Now we create two role entries for our website: admin and author
                $adminInsertSQL = "INSERT INTO roles (description, createdAt) VALUES ('admin', NOW())";
                $authorInsertSQL = "INSERT INTO roles (description, createdAt) VALUES ('author', NOW())";
                $adminQuery = $DBHandler->executeSQL($adminInsertSQL);
                array_push($processedData, array("entry" => "Admin Role", "status" => empty($adminQuery) ? "Added" : "Failed"));
                $authorQuery = $DBHandler->executeSQL($authorInsertSQL);
                array_push($processedData, array("entry" => "Author Role", "status" => empty($authorQuery) ? "Added" : "Failed"));

                //Users Table Setup
                $usersTbl = "CREATE TABLE IF NOT EXISTS users (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                name VARCHAR(30) NOT NULL,
                lastname VARCHAR(30) NOT NULL,
                username VARCHAR(30) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role_id INT(6) UNSIGNED NOT NULL,
                createdAt TIMESTAMP,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE)";

                $usersQuery = $DBHandler->executeSQL($usersTbl);
                array_push($processedData, array("entry" => "Users Table", "status" => empty($usersQuery) ? "Created" : "Failed". $usersQuery));

                //Topics Table Setup
                $topicsTbl = "CREATE TABLE IF NOT EXISTS topics (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                description TEXT NOT NULL,
                user_id INT(6) UNSIGNED NOT NULL,
                createdAt TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)";

                $topicsQuery = $DBHandler->executeSQL($topicsTbl);
                array_push($processedData, array("entry" => "Topics Table", "status" => empty($topicsQuery) ? "Created" : "Failed ".$topicsQuery));

                //Entries Table Setup
                $entriesTbl = "CREATE TABLE IF NOT EXISTS entries (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                word TEXT NOT NULL,
                definition TEXT(1000) NOT NULL,
                example_usage TEXT(1000),
                user_id INT(6) UNSIGNED NOT NULL,
                createdAt TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)";

                $entriesQuery = $DBHandler->executeSQL($entriesTbl);
                array_push($processedData, array("entry" => "Entries Table", "status" => empty($entriesQuery) ? "Created" : "Failed".$entriesQuery));

                //Topic-Entries Table Setup
                $topicEntriesTbl = "CREATE TABLE IF NOT EXISTS topic_entries (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                topic_id INT(6) UNSIGNED NOT NULL,
                entry_id INT(6) UNSIGNED NOT NULL,
                createdAt TIMESTAMP,
                FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
                FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE)";
            
                $topicEntriesQuery = $DBHandler->executeSQL($topicEntriesTbl);
                array_push($processedData, array("entry" => "Topic-Entries Table", "status" => empty($topicEntriesQuery) ? "Created" : "Failed".$topicEntriesQuery));
                
                //Save database config data into a json file for later usage.
                $encrypted_pass = my_simple_crypt( $password , 'e' );
                
                $dbData = array(
                    "server" => $server,
                    "user" => $user,
                    "password" => $encrypted_pass
                );
                $dbDataJson = json_encode($dbData);
                file_put_contents("../../model/DB/connection.json", $dbDataJson);
            } else {
                $connection_err .= $schemaCreate;
            }
            // Close Connection!
            $DBHandler->closeConnection();
        }
    }
}
?>

<!DOCTYPE html> 
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Basic Bootstrap Template</title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../assets/css/bootstrap-theme.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2> Welcome to Urban Dictionary </h2>
                <h3> Start DB Setup </h3>
            </div>
        </div>
        <div class="row">
                
            <div class="col-md-12 text-center">
                <?php if(count($processedData) == 0 || !empty($connection_err)) {
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group <?php echo (!empty($server_err)) ? 'alert-danger' : ''; ?>">
                        <label>Server Name</label>
                        <input type="text" name="server"class="form-control" value="<?php echo $server; ?>">
                        <span class="help-block"><?php echo $server_err; ?></span>
                    </div>
                    <div class="form-group <?php echo (!empty($user_err)) ? 'alert-danger' : ''; ?>">
                        <label>DB User</label>
                        <input type="text" name="user"class="form-control" value="<?php echo $user; ?>">
                        <span class="help-block"><?php echo $user_err; ?></span>
                    </div>    
                    <div class="form-group <?php echo (!empty($password_err)) ? 'alert-danger' : ''; ?>">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                        <span class="help-block"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <span class="help-block alert-danger"><?php echo $connection_err; ?></span><br>
                        <input type="submit" class="btn btn-primary" value="Submit">
                    </div>
                </form>
                <?php
                } else {
                ?>
                <table id="processed_input" class="display" style="width:100%">
                    <thead>
                        <tr>
                        <?php
                        foreach ($processedData[0] as $key => $value) {
                            echo "<th>".$value."</th>";
                        }
                        ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($x = 1; $x < count($processedData); $x++) {
                            $tableRow = "<tr>";
                            $tableRow .= "<td>" . $processedData[$x]["entry"] . "</td>";
                            $tableRow .= "<td>" . $processedData[$x]["status"] . "</td>";
                            $tableRow .= "</tr>";
                            echo $tableRow;
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <script src="../../assets/js/jquery-1.12.4.js"></script>
    <script src="../../assets/js/jquery.dataTables.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            var dbsetup = $('#processed_input').DataTable();

            /* Sort the students table on the GPA columin in descending order*/
            dbsetup.order( [ 1, 'desc' ] )
            .draw();
        });
    </script>
</body>
</html>