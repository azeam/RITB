<?php
    require_once("admin/db.php");
    $db = db_connect();

    // get posts by user or all users depending on menu selection or search string if searching
    if((isset($_POST["uid"]) && isset($_POST["offset"]) && isset($_POST["limit"])) || isset($_POST["searchstring"])){
        if(isset($_POST["uid"]) && isset($_POST["offset"]) && isset($_POST["limit"])  && (filter_var($_POST["uid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_POST["uid"], FILTER_VALIDATE_INT) === false)) {
            $sqlposts = 'SELECT * FROM post WHERE post.userId=\'' . db_escape($db, $_POST["uid"]) . '\' AND draft=0 ';
            if($_POST["uid"] == "0") {
                $sqlposts = 'SELECT * FROM post WHERE draft=0 '; // overwrite if 0 = all users selected
            }
            $sqlposts .= 'ORDER BY created DESC ';
            $sqlposts .= 'LIMIT ' . db_escape($db, $_POST["offset"]) . ', ' . db_escape($db, $_POST["limit"]) . '';  
        }
        if(isset($_POST["searchstring"])) {
            $searchstring = $_POST["searchstring"];
            if ($searchstring != '') {
                $sqlposts = 'SELECT * FROM post WHERE draft=0 AND content LIKE \'%' . db_escape($db, $searchstring) . '%\' OR draft=0 AND title LIKE \'%' . db_escape($db, $searchstring) . '%\' '; // overwrite if 0 = all users selected
                $sqlposts .= 'ORDER BY created DESC '; 
            }
            else {
                echo "<script>setUser(0)</script>"; 
                return false; 
            }
        }
        
        $result = db_select($db, $sqlposts);
        if ($result != NULL) {
            foreach ($result as &$post) {
                $posttitle = $post['title'];
                $postid = $post['id'];
                $postuser = $post['username'];
                $postdispname = $post['dispname'];
                $postdate = $post['created'];
                $postcontent = $post['content'];
                $postcontent = str_replace("../uploads", "uploads", $postcontent);
                $postcontent = nl2br($postcontent);
                // htaccess disabled on LTU, no SEO friendly links
                $urltitle = htmlspecialchars($posttitle);
                $urltitle = strtolower($urltitle);
                $urltitle = trim($urltitle);
                $urltitle = str_replace(" ", "-", $urltitle);
                $urltitle = urlencode($urltitle);
                echo "<article class='multiarticle'>";
                echo "<div class='post'>";
                echo "<a href='./$postid-$urltitle'>";
    //            echo "<a href='./?pid=$postid'>";
                echo "<h2>$posttitle</h2>";
                echo "</a>";
                echo "<p class='byuser'>By $postdispname on $postdate</p>";
                echo "<p>$postcontent</p>"; 
                echo "</div>";
                echo "</article>";
                echo "<hr>"; 
            }
        }
    }
    db_disconnect($db);
?>