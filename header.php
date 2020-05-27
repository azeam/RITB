<?php
require_once("admin/db.php");
$db = db_connect();
    // set page title to post title or user display name
    if(isset($_GET["pid"]) && (filter_var($_GET["pid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_GET["pid"], FILTER_VALIDATE_INT) === false)) {    
        $sqlposts = 'SELECT title ';
        $sqlposts .= 'FROM post WHERE post.id=\'' . db_escape($db, $_GET["pid"]) . '\'';
        $result = db_select($db, $sqlposts);
        if ($result != NULL) {
            $posttitle = $result[0]['title'];
            echo " - $posttitle";
        }
    }
    else if(isset($_GET["uid"]) && (filter_var($_GET["uid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_GET["uid"], FILTER_VALIDATE_INT) === false)) {    
        require_once("admin/db.php");
        $db = db_connect();
        $sqlposts = 'SELECT dispname ';
        $sqlposts .= 'FROM user WHERE user.id=\'' . db_escape($db, $_GET["uid"]) . '\'';
        $result = db_select($db, $sqlposts);
        if ($result != NULL) {
            $posttitle = $result[0]['dispname'];
            echo " - Posts by $posttitle";
        }
    }
db_disconnect($db); 
?>