<?php
    // get all authors
    // TODO: only list authors with posts > 0?
    require_once("admin/db.php");
    $db = db_connect();
    $sqlusers = 'SELECT * ';
    $sqlusers .= 'FROM user ';
    $sqlusers .= 'ORDER BY created DESC';
    $result = db_select($db, $sqlusers);
    
    foreach ($result as &$user) {
        $username = $user['username'];
        $uid = $user['id'];
        $dispname = $user['dispname'];
        $urltitle = htmlspecialchars($dispname);
        $urltitle = strtolower($urltitle);
        $urltitle = trim($urltitle);
        $urltitle = str_replace(" ", "-", $urltitle);
        $urltitle = urlencode($urltitle);
        echo "<li>";
        echo "<a href='./".$uid."_".$urltitle."'>$dispname</a>";
//        echo "<a href='./?uid=$uid'>$dispname</a>"; 
        echo "</li>";
    }
    db_disconnect($db); 
?>