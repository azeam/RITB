<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>The Blog<?php include "header.php"; ?></title>
    <meta name="description" content="The blog">
    <meta name="author" content="Dennis Hägg">
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.0.9/purify.min.js"></script>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>

<body>
<header>
    <a href="./"><img src="images/logo.png" alt="logo" /></a> 
    <div id='searchPostHolder'> 
        <input type='search' id='searchPosts' placeholder='Search posts'>
    </div>
    <div id="loginHolder"> 
        <button class="darkBtn" name="login" onclick="document.location = 'admin/admin.php'">Log in</button>
    </div>
</header>
<div id="outer"> 
    <div id="menu">
        <h2>Blog authors</h2>
        <div id='searchAuthorHolder'><input type='search' id='searchAuthors' placeholder='Search author'></div>
            <ul>
                <li>
                    <a href='./0_all-authors'>All authors</a>
                </li>
                <?php include "menu.php"; ?> <!-- fill rest of authors -->  
            </ul>
    </div>
    <div id="content">
        <?php
            require_once("admin/db.php");
            $db = db_connect();
            // get single post
            if(isset($_GET["pid"]) && (filter_var($_GET["pid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_GET["pid"], FILTER_VALIDATE_INT) === false)) {
                $sqlposts = 'SELECT * ';
                $sqlposts .= 'FROM post WHERE post.id=\'' . db_escape($db, $_GET["pid"]) . '\'';
                $result = db_select($db, $sqlposts);
                if ($result != NULL) {
                    foreach ($result as &$post) {
                        $posttitle = $post['title'];
                        $postid = $post['id'];
                        $draft = $post['draft'];
                        $postdispname = $post['dispname'];
                        $postdate = $post['created'];
                        $postcontent = $post['content'];
                        $postcontent = str_replace("../uploads", "uploads", $postcontent); // change img path
                        $postcontent = nl2br($postcontent); // show newlines as html
                        echo "<article>";
                        echo "<div class='post'>";
                        if ($draft == 0) {
                            echo "<h1>$posttitle</h1>";
                            echo "<p class='byuser'>By $postdispname on $postdate</p>";
                            echo "<p>$postcontent</p>";
                        } 
                        else {
                            echo "<h1>This post has not been published yet</h1>";
                        }
                        echo "</div>";
                        echo "</article>";
                    }
                }
                else {
                    echo "<h1>Post not found</h1>";
                }
            }
            db_disconnect($db);
        ?>
    </div>
    <div id="info"> 
        <?php include "info.php"; ?>
    </div>
</div>
<?php include "footer.php"; ?>
<script src="js/mainjs.js"></script>
<?php 
if (!isset($_GET["pid"]) && isset($_GET["uid"]) && (filter_var($_GET["uid"], FILTER_VALIDATE_INT) === 0 || !filter_var($_GET["uid"], FILTER_VALIDATE_INT) === false)) {
    $postuser = $_GET["uid"];
    echo "<script>setUser($postuser)</script>"; 
} 
else if (!isset($_GET["pid"]) && !isset($_GET["uid"])){
    echo "<script>setUser(0)</script>"; // set user to all and start script 
}
?>
</body>
</html>