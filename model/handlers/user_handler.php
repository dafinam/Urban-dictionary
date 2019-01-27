<?php
require_once(__DIR__."/../DB/config.php");

/**
 * A function that checks whether a user exists or not in the DB
 *
 * @param string $username      The username of the user to be validated
 * @return json                 A json object that represents the status of the statement
 */
function check_user_exists($username) {
    global $link;
    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE username = ?";
    $returnStatus = array();

    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        
        // Set parameters
        $param_username = $username;
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1){
                $returnStatus = array(
                    "status" => 500,
                    "msg" => "This username is already taken."
                );
            } else {
                $returnStatus = array(
                    "status" => 200,
                    "msg" => "Success"
                );
            }
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Oops! Something went wrong. Please try again later."
            );
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}

/**
 * A function that updates the user profile information
 *
 * @param int $user_id          The id of the user for which the profile info will be updated
 * @param string $name          New user name
 * @param string $lastname      New user lastname
 * @param string $username      New user username
 * @return json                 A json object that represents the status of the statement
 */
function updateUserProfileData($user_id, $name, $lastname, $username) {
    global $link;

    $sql = "UPDATE users SET name = ?, lastname = ?, username = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_lastname, $param_username, $param_id);
        
        // Set parameters
        $param_name = $name;
        $param_lastname = $lastname;
        $param_username = $username;
        $param_id = $user_id;
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $returnStatus = array(
                "status" => 200,
                "msg" => "Success"
            );
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "User profile data update failed."
            );
        }
    } else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "Something went wrong preparing the update statement."
        );
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}

/**
 * A function that updates the users' password
 *
 * @param int $user_id          The id of the user
 * @param string $password      The new password to be updated
 * @return json                 A json object that represents the status of the statement
 */
function updateUserPass($user_id, $password) {
    global $link;
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ss", $param_pass, $param_id);
        
        // Set parameters
        $param_pass = $password;
        $param_id = $user_id;
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $returnStatus = array(
                "status" => 200,
                "msg" => "Success"
            );
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "User password update failed."
            );
        }
    } else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "Something went wrong preparing the update statement."
        );
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}

/**
 * A function that fetches all the system users from DB (besides the logged user itself)
 *
 * @param id $logged_user_id    The logged user id
 * @return array                The list of system users
 */
function getAllSystemUsers($logged_user_id) {
    global $link;
    $user_list = array();
    $sql = "SELECT u.id, u.name, u.lastname, u.username, r.description
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.id != ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_id);
                
        // Set parameters
        $param_id = $logged_user_id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $id, $name, $lastname, $username, $role);
            
            while ($data = $stmt->fetch()) {
                $user_list[] = array(
                    "id" => $id,
                    "name" => $name,
                    "lastname" => $lastname,
                    "username" => $username,
                    "role" => $role
                );
            }
        }
    }
    mysqli_stmt_close($stmt);
    return $user_list;
}

/**
 * A function that deletes a system user
 *
 * @param int $loggedUserID     The logged user id
 * @param int $userToDeleteID   The id of the user to be deleted
 * @return json                 A json object that represents the status of the statement
 */
function deleteUser($loggedUserID, $userToDeleteID) {
    global $link;

    $returnStatus = array();
    $sql = "DELETE FROM users WHERE id = ?";
    if($loggedUserID != $userToDeleteID) {
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_id);
                    
            // Set parameters
            $param_id = $userToDeleteID;
            if(mysqli_stmt_execute($stmt)){
                $returnStatus = array(
                    "status" => 200,
                    "msg" => "Success"
                );
            } else {
                $returnStatus = array(
                    "status" => 500,
                    "msg" => "Could not delete user!"
                );
            }
        }  else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Something went wrong preparing the delete query!"
            );
        }
        mysqli_stmt_close($stmt);
    } else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "You cannot delete your own user!"
        );
    }
    return $returnStatus;
}

/**
 * A function that validates the given credentials for a user
 *
 * @param string $username      User username
 * @param string $password      User password
 * @return json                 A json object that represents the status of the statement
 */
function validateUserCredentials($username, $password) {
    global $link;
    $returnStatus = array();

    // Prepare a select statement
    $sql = "SELECT u.id, u.name, u.lastname, u.username, u.password, r.description 
    FROM users u INNER JOIN roles r ON u.role_id = r.id
    WHERE u.username = ?";

    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_username);

        // Set parameters
        $param_username = $username;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Store result
            mysqli_stmt_store_result($stmt);
            
            // Check if username exists, if yes then verify password
            if(mysqli_stmt_num_rows($stmt) == 1){              
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $user_id, $name, $lastname, $username, $hashed_password, $role);

                if(mysqli_stmt_fetch($stmt)){
                    if(password_verify($password, $hashed_password)){
                        /* Password is correct */
                        $returnStatus = array(
                            "status" => 200,
                            "msg" => "Success.",
                            "data" => array(
                                "user_id" => $user_id,
                                "name" => $name,
                                "lastname" => $lastname,
                                "username" => $username,
                                "role" =>  $role
                            )
                        );
                    } else{
                        $returnStatus = array(
                            "status" => 500,
                            "msg" => "The password you entered was not valid."
                        );
                    }
                }
            } else {
                $returnStatus = array(
                    "status" => 403,
                    "msg" => "No account found with that username."
                );
            }
        } else{
            $returnStatus = array(
                "status" => 500,
                "msg" => "Something went wrong!"
            );
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}

/**
 * A function that creates a new user in the DB
 *
 * @param string $name          The name for the new user
 * @param string $lastname      The lastname for the new user
 * @param string $username      The username for the new user
 * @param string $password      The password for the new user
 * @return json                 A json object that represents the status of the insert statement
 */ 
function createNewUser($name, $lastname, $username, $password) {
    global $link;
    $returnStatus = array();
    // Prepare an insert statement
    $sql = "INSERT INTO users (name, lastname, username, password, role_id, createdAt) VALUES (?, ?, ?, ?, 2, ?)";
         
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sssss", $param_name, $param_lastname, $param_username, $param_password, $param_createdAt);
        
        // Set parameters
        $param_name = $name;
        $param_lastname = $lastname;
        $param_username = $username;
        $param_password = $password;
        $param_createdAt = date('Y-m-d H:i:s');
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Redirect to login page
            $returnStatus = array(
                "status" => 200,
                "msg" => "Success!"
            );
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Something went wrong!"
            );
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}
?>