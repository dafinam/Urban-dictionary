<?php

/**
 * Show list of topics based on users preference.
 */
if(isset($_COOKIE['topic_list_preferences'])) {
    if($_COOKIE['topic_list_preferences'] == "chronological") {
        $topFiveTopics = selectTopicsInChronologicalOrder();
    } else {
        $topFiveTopics = selectTopicsByPopularity();
    }
} else {
    $topFiveTopics = selectRandomTopics();
}

?>

<p>Popular Topics</p>
<p>
    <?php
        for ($x = 0; $x < count($topFiveTopics); $x++) {
            $topic = "<span class='label label-".getRandomLabelColor()."'>";
            $topic .= "<a href='".htmlspecialchars($_SERVER["PHP_SELF"])."?selectedTopic=".$topFiveTopics[$x]['id']."' class='link-unstyled'>";
            $topic .= $topFiveTopics[$x]['description'];
            $topic .= "</a>";
            $topic .= "</span><br/>";
            echo $topic;
        }
    ?>
</p>