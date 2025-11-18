<?php
// load all summary rows from v_post_summary view
function loadSummaryRows($pdo)
{
    $sql = "SELECT post_number, location, occupation_type, name, weapons, zombie_types, observation FROM v_post_summary ORDER BY post_number, occupation_type";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
