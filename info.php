<?php 
    require_once("admin/db.php");
    $db = db_connect();
    $postuser = '';
    // get profile info
    if(isset($_GET["pid"]) || isset($_GET["uid"]) && $_GET["uid"] != 0) {
        if (isset($_GET["pid"]) && !filter_var($_GET["pid"], FILTER_VALIDATE_INT) === false) {
            $sqlposts = 'SELECT username ';
            $sqlposts .= 'FROM post WHERE post.id=\'' . db_escape($db, $_GET["pid"]) . '\'';
            $result = db_select($db, $sqlposts);
            if ($result != NULL) {
                $postuser = $result[0]['username'];
            }    
            $sqlid = 'SELECT * FROM user WHERE username=\'' . db_escape($db, $postuser) . '\'';
        }
        else if(isset($_GET["uid"]) && (filter_var($_GET["uid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_GET["uid"], FILTER_VALIDATE_INT) === false)) {
            $postuid = $_GET["uid"];
            $sqlid = 'SELECT * FROM user WHERE id=\'' . db_escape($db, $postuid) . '\'';
        }
        else {
            return false;
        }
        $result = db_select($db, $sqlid);
        foreach ($result as &$user) {
            $name = $user['username'];
            $dispname = $user['dispname'];
            $image = $user['image'];
            $imgdesc = $user['imgdesc'];
            $imgfile = substr($image, 3);
            $presentation = nl2br($user['presentation']); // show newlines
            echo "<h2>Author</h2>";
            echo "<img src='".$imgfile."' alt='".$imgdesc."' class='profileImg'>";
            echo "<p><h3>$dispname</h3><br>".$presentation."</p>";
        }
    }
    else {
        echo "<h2>Blog statistics</h2>";
        echo "<p>";
        $sqlusers = 'SELECT COUNT(*) as users from user';
        $result = db_select($db, $sqlusers);
        echo "There are ".$result[0]['users']." registered authors who have written ";

        $sqlusers = 'SELECT COUNT(*) as posts from post';
        $result = db_select($db, $sqlusers);
        echo $result[0]['posts']." blog posts and uploaded ";

        $sqlusers = 'SELECT COUNT(*) as images from image';
        $result = db_select($db, $sqlusers);
        echo $result[0]['images']." images.<br>";
        echo "</p>";
    }
    db_disconnect($db);
?>