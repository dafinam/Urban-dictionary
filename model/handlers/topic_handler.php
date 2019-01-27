<?php 
require_once(__DIR__."/../DB/config.php");

/**
 * A function that fetches all the topics created by the given user
 *
 * @param int $user_id      The user id to get the topics for
 * @return array            A list of topics created by the given user
 */
function getTopicsByUser($user_id) {
    global $link;
    $topicList = array();
    //Fetch all topics for current user
    $sql = "SELECT id, description FROM topics WHERE user_id = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_user);
                
        // Set parameters
        $param_user = $user_id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $topic_id ,$topic_dsc);
            
            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $topic_id,
                    "description" => $topic_dsc
                );
            }
        }
    }
    mysqli_stmt_close($stmt);
    return $topicList;
}

/**
 * A function that fetches all the topic-entry mappings
 *
 * @return array    The list of all topic-entry mappings from the DB
 */
function getAllTopicEntryMappings() {
    global $link;
    //Fetch all topics for current user
    $topicList = array();
    $sql = "SELECT t.id, t.description, t.createdAt, COUNT(te.topic_id) entry_count
            FROM topics t
            LEFT JOIN topic_entries te ON t.id = te.topic_id
            GROUP BY t.id";
    if($stmt = mysqli_prepare($link, $sql)){
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $topic_id ,$topic_dsc, $createdAt, $entry_count);

            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $topic_id,
                    "description" => $topic_dsc,
                    "entry_count" => $entry_count,
                    "createdAt" => $createdAt
                );
            }
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $topicList;
}

/**
 * A function that fetches all the topic-entry mappings for a given user
 *
 * @param int $user_id      The user for which the topic-entry mappings will be fetched for
 * @return array            The list of all topic-entry mappings registered from the given user
 */
function getTopicEntryMappingsByUser($user_id) {
    global $link;
    //Fetch all topics for current user
    $topicList = array();
    //Fetch all topics for current user
    $sql = "SELECT t.id, t.description, t.createdAt, COUNT(te.topic_id) entry_count
            FROM topics t 
            LEFT JOIN topic_entries te ON t.id = te.topic_id
            WHERE t.user_id = ?
            GROUP BY t.id";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_user);
            
        // Set parameters
        $param_user = $user_id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $topic_id ,$topic_dsc, $createdAt, $entry_count);

            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $topic_id,
                    "description" => $topic_dsc,
                    "entry_count" => $entry_count,
                    "createdAt" => $createdAt
                );
            }
        }
    }

    // Close statement
    mysqli_stmt_close($stmt);
    return $topicList;
}

/**
 * A function that checks whether a user has permission to see a given topic
 *
 * @param int $topicId          The topic for which the permission will be checked
 * @param int $userId           The user for which the permission will be checked
 * @return json                 A json object that represents the status of the statement
 */
function userPermissionOnTopic($topicId, $userId) {
    global $link;
    $hasPermission = false;
    //Check if user is entiteled to access the given topic  id
    $sql = "SELECT user_id FROM topics WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_topic);
                
        // Set parameters
        $param_topic = $topicId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $topic_user_id);
            if(mysqli_stmt_fetch($stmt) && $userId == $topic_user_id){
                $hasPermission = true;
            }
        }
    }
    mysqli_stmt_close($stmt);
    return $hasPermission;
}

/**
 * A function that checks whether a topic description is valid (duplicate entries in the db)
 *
 * @param string $description   The topic name/description to be validated
 * @return json                 A json object that represents the status of the insert statement
 */
function isTopicDescriptionValid($description) {
    global $link;
    $returnStatus = array();
    // Prepare a select statement
    $sql = "SELECT id FROM topics WHERE description = ?";
 
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_description);
        
        // Set parameters
        $param_description = $description;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1){
                $returnStatus = array(
                    "status" => 500,
                    "msg" => "Topic " . $description . " already exists"
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
 * A function that creates a new topic in the DB
 *
 * @param string $description   The name/description of the topic
 * @param int $userID           The user id of the user who is creating the topic
 * @return json                 A json object that represents the status of the insert statement
 */
function createNewTopic($description, $userID) {
    global $link;
    $returnStatus = array();

    // Prepare an insert statement
    $sql = "INSERT INTO topics (description, user_id, createdAt) VALUES (?, ?, ?)";
        
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sss", $param_desc, $param_user, $param_createdAt);
        
        // Set parameters
        $param_desc = $description;
        $param_user = $userID;
        $param_createdAt = date('Y-m-d H:i:s');
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $returnStatus = array(
                "status" => 200,
                "msg" => "Success"
            );
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
 * A function that deletes a given topic from the database and all its corresponding relations (like entries, and topic-entries)
 *
 * @param int $topicToDelete    The id of the topic to be deleted
 * @return json                 A json object that represents the status of the delete statement
 */
function deleteTopic($topicToDelete) {
    global $link;
    $returnStatus = array();

    //First Delete Entries
    $sql_delete_entries = "DELETE FROM entries WHERE id in (SELECT entry_id FROM topic_entries WHERE topic_id = ?)";
    
    //Then delete topic
    $sql_delete_topics = "DELETE FROM topics WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql_delete_entries)) {
        mysqli_stmt_bind_param($stmt, "s", $param_topicID);
            
        // Set parameters
        $param_topicID = $topicToDelete;
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            //Entries have been delete, proceed with deleting topic
            if($stmt = mysqli_prepare($link, $sql_delete_topics)){
                mysqli_stmt_bind_param($stmt, "s", $param_topicID);
                $param_topicID = $topicToDelete;
                if(mysqli_stmt_execute($stmt)){
                    $returnStatus = array(
                        "status" => 200,
                        "msg" => "Success"
                    );
                } else {
                    $returnStatus = array(
                        "status" => 500,
                        "msg" => "Oops! Topic could not be deleted."
                    );
                }
            }
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Oops! Topic Entries could not be deleted."
            );
        }
    } else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "Oops! Something went wrong while preparing to delete topic."
        );
    }

    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}
?>