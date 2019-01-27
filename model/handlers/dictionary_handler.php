<?php
require_once(__DIR__."/../DB/config.php");

/**
 * A function that selects 5 random topics from the database
 *
 * @return array    List of 5 randomly selected topics
 */
function selectRandomTopics() {
    global $link;
    $topicList = array();
    $sql = "SELECT id, description FROM topics";
    if($stmt = mysqli_prepare($link, $sql)) {
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $id, $description);
            
            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $id,
                    "description" => $description
                );
            }
        }
    }

    //Shuffle the array
    shuffle($topicList);
    $fiveRandomTopics = array_slice($topicList, 0, 5, true);
    return $fiveRandomTopics;
}

/**
 * Function that selects topics from the database in chronological order
 *
 * @return array    The top 5 recently added topics
 */
function selectTopicsInChronologicalOrder() {
    global $link;
    $topicList = array();

    $sql = "SELECT id, description FROM topics ORDER BY createdAt DESC LIMIT 5";
    if($stmt = mysqli_prepare($link, $sql)) {
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $id, $description);
            
            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $id,
                    "description" => $description
                );
            }
        }
    }
    return $topicList;
}

/**
 * Function that selects topics based on their popularity (nr of entries created for this topic during last week)
 *
 * @return array    Top 5 most popular topics.
 */
function selectTopicsByPopularity() {
    global $link;
    $topicList = array();

    $sql = "SELECT te.topic_id, t.description, count(te.entry_id) as nr_entries 
            FROM topic_entries te
            INNER JOIN topics t on te.topic_id = t.id 
            where te.createdAt between date_sub(now(),INTERVAL 1 WEEK) and now()
            GROUP BY te.topic_id
            ORDER BY nr_entries DESC
            LIMIT 5";
    if($stmt = mysqli_prepare($link, $sql)) {
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $id, $description, $count_entries);
            
            while ($data = $stmt->fetch()) {
                $topicList[] = array(
                    "id" => $id,
                    "description" => $description
                );
            }
        }
    }
    return $topicList;
}

/**
 * Simple function that selects a random element from an array of strings
 *
 * @return string   A randomly selected string from the $labelColors array
 */
function getRandomLabelColor() {
    $labelColors = array("default", "success", "info", "warning", "primary", "danger");
    shuffle($labelColors);
    return $labelColors[rand(0, 5)];
}

/**
 * A function that fetches all the entries associated to the given topic
 *
 * @param int $topic_id     The id of the topic to fetch the entries for
 * @return array            The list of entries associated to the topic
 */
function getEntriesForTopic($topic_id) {
    global $link;
    $entryList = array();

    $sql = "SELECT e.word, e.definition, e.example_usage, e.createdAt, u.name, u.lastname 
            FROM entries e 
            INNER JOIN topic_entries te ON e.id = te.entry_id
            INNER JOIN users u on e.user_id = u.id
            WHERE te.topic_id = ?
            ORDER BY e.createdAt DESC";
    if($stmt = (mysqli_prepare($link, $sql))){
        mysqli_stmt_bind_param($stmt, "s", $param_topic);
                
        // Set parameters
        $param_topic = $topic_id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $word ,$definition, $usage, $createdAt, $name, $lastname);
            
            while ($data = $stmt->fetch()) {
                $date = new DateTime($createdAt);
                $entryList[] = array(
                    "word" => $word,
                    "definition" => $definition,
                    "usage" => $usage,
                    "createdAt" => $date->format('M d, Y'),
                    "createdBy" => $name . " " . $lastname
                );
            }
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $entryList;
}

/**
 * A function that fetches all information added in the DB for the given topic
 *
 * @param int $topic_id     The id of the topic to fetch the data for
 * @return array            A json object containing all the data for the topic
 */
function getTopicData($topic_id) {
    global $link;
    $topicData = array();
    $sql = "SELECT id, description FROM topics WHERE id = ?";
    if($stmt = (mysqli_prepare($link, $sql))){
        mysqli_stmt_bind_param($stmt, "s", $param_topic);
                
        // Set parameters
        $param_topic = $topic_id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $id ,$description);
            if(mysqli_stmt_fetch($stmt)){
                $topicData = array(
                    "id" => $id,
                    "description" => $description
                );
            }
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $topicData;
}

/**
 * The urban dictionary search keyword function.
 *
 * @param string $keyword   The keyword to search the database against
 * @return array            List of entries found that contain the given keyword
 */
function searchDictionary($keyword) {
    global $link;
    $entryList = array();
    $sql = "SELECT t.description, e.word, e.definition, e.example_usage, e.createdAt, u.name, u.lastname
    FROM topics t 
    INNER JOIN topic_entries te ON te.topic_id = t.id
    INNER JOIN entries e ON e.id = te.entry_id
    INNER JOIN users u ON u.id = e.user_id
    WHERE e.word LIKE ? OR t.description LIKE ? OR e.example_usage LIKE ?";

    if($stmt = (mysqli_prepare($link, $sql))){
        mysqli_stmt_bind_param($stmt, "sss", $param_word, $param_description, $param_usage);
                
        // Set parameters
        $param_word = $param_description = $param_usage ="%{$keyword}%";
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $description, $word ,$definition, $usage, $createdAt, $name, $lastname);

            while ($data = $stmt->fetch()) {
                $date = new DateTime($createdAt);
                $entryList[] = array(
                    "topic_description" => str_replace($keyword, "<strong class='text-danger'>{$keyword}</strong>", $description) ,
                    "word" => str_replace($keyword, "<strong class='text-danger'>{$keyword}</strong>", $word),
                    "definition" => str_replace($keyword, "<strong class='text-danger'>{$keyword}</strong>", $definition),
                    "usage" => str_replace($keyword, "<strong class='text-danger'>{$keyword}</strong>", $usage),
                    "createdAt" => $date->format('M d, Y'),
                    "createdBy" => $name . " " . $lastname
                );
            }
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    return $entryList;
}
?>