<?php

/**
 * Fetch top entries based on users' preference.
 */
$entryList = array();
if(isset($_REQUEST['selectedTopic']) && !empty($_REQUEST['selectedTopic'])) {
    $selectedTopic = getTopicData($_REQUEST['selectedTopic']);
    $entryList = getEntriesForTopic($_REQUEST['selectedTopic']);
} else if(count($topFiveTopics) > 0){
    $selectedTopic = $topFiveTopics[rand(0, count($topFiveTopics)-1)];
    $entryList = getEntriesForTopic($selectedTopic['id']);
}
?>


<?php
if(isset($selectedTopic)) {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default text-left">
            <div class="panel-body">
                <h4>Current Topic: <?= $selectedTopic['description'] ?></h4>   
            </div>
        </div>
    </div>
</div>
<?php
} else {
?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default text-left">
            <div class="panel-body">
                <h4> No entries available </h4>   
            </div>
        </div>
    </div>
</div>
<?php
}
?>


<?php for ($x = 0; $x < count($entryList); $x++) { ?>
<div class="row">
    <div class="col-sm-12 border">
        <div class="panel panel-default text-left">
            <div class="panel-body">
                <h3><?= $entryList[$x]['word'] ?></h3>
                <div>
                    <p class="text-justify">
                        <?= $entryList[$x]['definition'] ?>
                    </p>
                </div>
                <div>
                    <p class="text-justify"><em>
                        <?= $entryList[$x]['usage'] ?>
                    </em></p>
                </div>

                <div class="text-left">
                    <!-- <strong>by</strong> <em><?= $entryList[$x]['createdBy'] ?></em> <strong><?= $entryList[$x]['createdAt'] ?></strong> -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
