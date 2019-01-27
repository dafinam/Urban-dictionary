<?php
$entryList = array();

if(isset($_REQUEST['search_key']) && !empty($_REQUEST['search_key'])) {
    $search_key = $_REQUEST['search_key'];

    $entryList = searchDictionary($search_key);
}

if(count($entryList) == 0) {
?>
<div class="row">
    <div class="col-sm-12 border">
        <div class="panel panel-default text-left">
            <div class="panel-body">
                <h3> Nothing found with search key <em><?= $_REQUEST['search_key']?></em></h3>
            </div>
        </div>
    </div>
</div>
<?php
} else {
    for ($x = 0; $x < count($entryList); $x++) {
?>
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
                    <strong>by</strong> <em><?= $entryList[$x]['createdBy'] ?></em> <strong><?= $entryList[$x]['createdAt'] ?></strong>
                </div>
                <div class="text-right">
                    Topic: <strong><em><?= $entryList[$x]['topic_description']?></em></strong>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }
} ?>