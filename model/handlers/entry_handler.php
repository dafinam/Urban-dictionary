<?php 
require_once(__DIR__."/../DB/config.php");

/**
 * A function that fetches all entries (and their corresponding data) for a given topic
 *
 * @param int $topicId  The id of the topic to search the entries for
 * @return array        The list of entries associated to the given topic
 */
function entriesByTopic($topicId) {
    global $link;    

    $entryList = array();
    //Fetch all entries for the given topic
    $sql = "SELECT e.id, e.word, e.definition, e.example_usage, e.createdAt 
            FROM entries e 
            INNER JOIN topic_entries te ON e.id = te.entry_id 
            WHERE te.topic_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_topic);
                
        // Set parameters
        $param_topic = $topicId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $entryID, $word ,$definition, $usage, $createdAt);
            
            while ($data = $stmt->fetch()) {
                $entryList[] = array(
                    "id" => $entryID,
                    "word" => $word,
                    "definition" => $definition,
                    "usage" => $usage,
                    "createdAt" => $createdAt
                );
            }
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $entryList;
}

/**
 * A function that creates a new entry in the database
 *
 * @param string $word          The name of the entry
 * @param string $definition    The definition of the entry
 * @param string $usage         An example usage of the entry
 * @param int $user_id          The user who is creating this entry
 * @param int $topic_id         The topic for which this entry is associated with
 * @return json                 A json object that represents the status of the insert statement
 */
function insertEntry($word, $definition, $usage, $user_id, $topic_id) {
    global $link;
    $returnStatus = array();
    // Prepare an insert statement
    $sql = "INSERT INTO entries (word, definition, example_usage, user_id, createdAt) VALUES (?, ?, ?, ?, ?)";
         
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sssss", $param_word, $param_definition, $param_usage, $param_user, $param_createdAt);
        
        // Set parameters
        $param_word = $word;
        $param_definition = $definition;
        $param_usage = $usage;
        $param_user = $user_id;
        $param_createdAt = date('Y-m-d H:i:s');
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Now connect the entry with the topic
            $new_entry_id = mysqli_insert_id($link);
            $sql = "INSERT INTO topic_entries (topic_id, entry_id, createdAt) VALUES (?, ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "sss", $param_topic, $param_entry, $param_createdAt);
                $param_topic = $topic_id;
                $param_entry = $new_entry_id;
                $param_createdAt = date('Y-m-d H:i:s');

                if(mysqli_stmt_execute($stmt)){
                    $returnStatus = array(
                        "status" => 200,
                        "msg" => "Success"
                    );
                } else {
                    $returnStatus = array(
                        "status" => 500,
                        "msg" => "Something went wrong creating the topic <-> entry relation."
                    );
                }
            }
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Something went wrong creating the entry."
            );
        }
    } else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "Something went wrong preparing insert statement."
        );
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $returnStatus;
}

/**
 * A function that checks whether an entry already exists in the db.
 *
 * @param string $word          The name of the entry
 * @return json                 A json object that represents the status of the statement
 */
function check_entry_exists($word) {
    global $link;
    $returnStatus = array();
    // Prepare a select statement
    $sql = "SELECT id FROM entries WHERE word = ?";
        
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_word);
        
        // Set parameters
        $param_word = $word;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1){
                $returnStatus = array(
                    "status" => 500,
                    "msg" => "This word is already added to the dictionary."
                );
            } else{
                $returnStatus = array(
                    "status" => 200,
                    "msg" => "Success"
                );
            }
        } else{
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
 * A function that deletes a given entry from the database
 *
 * @param int $entryID          The id of the entry to be deleted
 * @return json                 A json object that represents the status of the delete statement
 */
function deleteEntry($entryID) {
    global $link;
    $returnStatus = array();
    $sql = "DELETE FROM entries WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_entryid);
                
        // Set parameters
        $param_entryid = $entryID;
        if(mysqli_stmt_execute($stmt)){
            $returnStatus = array(
                "status" => 200,
                "msg" => "Success"
            );
        } else {
            $returnStatus = array(
                "status" => 500,
                "msg" => "Could not delete entry!"
            );
        }
    }  else {
        $returnStatus = array(
            "status" => 500,
            "msg" => "Something went wrong preparing the delete query!"
        );
    }
    mysqli_stmt_close($stmt);
    return $returnStatus;
}
?>