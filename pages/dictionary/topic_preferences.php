<?php
$cookie_name = "topic_list_preferences";

$chronological_order = $_COOKIE[$cookie_name] == "chronological" ? "checked" : "";
$popularity_order = $_COOKIE[$cookie_name] == "popularity" ? "checked" : "";

?>

<p>Topic Display Settings</p>
<div class="radio">
    <label><input type="radio" name="optradio" onClick="radioClicked('chronological')" <?= $chronological_order ?> >Chronological</label>
</div>
<div class="radio">
    <label><input type="radio" name="optradio" onClick="radioClicked('popularity')" <?= $popularity_order ?> >Popularity</label>
</div>
<script src="assets/js/jquery-1.12.4.js"></script>
<script src="assets/js/jquery.cookie.js"></script>
<script>
function radioClicked(topic_order){
    $.cookie('topic_list_preferences', topic_order , { expires: 365 });
    location.reload();
}
</script>
