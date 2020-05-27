<?php session_start(); // start session at the very beginning to avoid session problems
if(empty($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if (isset($_POST["logout"])){
    $_SESSION['username'] = "";
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>The Blog - Admin page</title>
	<meta name="description" content="Admin page">
	<meta name="author" content="Dennis Hägg">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.0.9/purify.min.js"></script>
</head>

<body>
<header>
    <a href="admin.php"><img src="../images/logo.png" alt="logo" /></a> 
    <div id="logoutHolder">
        <form method="post">
            <input type="submit" class="darkBtn" name="profile" id="profileBtn" value="Edit profile">
            <input type="submit" class="darkBtn" name="logout" value="Log out">
        </form>
    </div>
    <div id="profileEditHolder">
            <?php 
                if(!empty($_SESSION['username'])) {
                    echo "<h2>".$_SESSION['username']."</h2>"; 
                }
            ?>
            <br>
        <form method="post" id="dispnameForm" style="clear:both"> 
            <label for="dispname">Display name</label>
            <br>
            <input type="text" id="dispname" name="dispname" maxlength="50">
            <br>
            <input type="submit" id="saveDispname" name="saveDispname" value="Update display name">
        </form>
        <br>
            <img src="../images/placeholder.png" alt="Placeholder" class="profileImg">
        <form method="post" id="profile">
            <label for="udesc">Presentation</label>
            <br>
            <textarea id="udesc" name="udesc"></textarea>
            <br>
            <input type="submit" id="save" name="save" value="Update presentation">
        </form>
        <br>
        <div id="profileSpinner"></div>
        <form method="post" enctype="multipart/form-data" id="uUpload">
            <label class="filelabel">
            <input type="file" name="fileToUpload" id="uFileToUpload">
                select image
            </label>
            <span id="uFileSelected">No file selected</span>
            <input type="submit" id="uUploadbtn" name="upload" value="Update image">
        </form>
    </div>
</header>
<div id="outer">
    <div id="content"> 
        <form method="post" >
        <div id="dialog" class="dialog">
            <div id="dialogContent">
            </div>
        </div>
            <?php 
                if(!empty($_SESSION['username'])) {
                    echo "<h2>Welcome ".$_SESSION['username'].", let's blog!</h2>"; 
                }
            ?>
        </form>
        <br><br>
        <form method="post" id="blogpost">
            <label for="title">Title</label>
            <br>
            <input type="text" id="title" name="title" maxlength="80">
            <input type="text" id="pid" name="pid" class="hidden">
            <input type="text" id="draftbool" name="draftbool" class="hidden">
            <br><br>
            <div class="floatRight">
                <input type="button" id="headline" value="Headline">
                <input type="submit" id="link" value="Link">
                <input type="submit" id="listBtn" value="List (&#9632;)">
                <input type="submit" id="nmbrListBtn" value="List (1)">
                <input type="submit" id="boldBtn" value="Bold">
                <input type="submit" id="italicBtn" value="Italic">
            </div>
            <label for="text" class="floatLeft">Text</label>
            <br>
            <textarea id="text" name="text"></textarea>
            <br>
            <div class="floatRight">
                <input type="submit" id="clear" name="clear" value="Clear">
                <input type="submit" id="draft" name="draft" value="Save draft">
                <input type="submit" id="update" name="update" value="Update post" class="hidden">
                <input type="submit" id="publish" name="publish" value="Publish post">
            </div>
        </form>
       
        <br><br>
        <form method="post" enctype="multipart/form-data" id="upload">
            <div id="spinner"></div>
            <label class="filelabel">
            <input type="file" name="fileToUpload" id="fileToUpload">
                select image
            </label>
            <span id="fileSelected">No file selected</span>
            <input type="submit" id="uploadbtn" name="upload" value="Upload image">
        </form>
        <br><br>
        <h2>Preview</h2>
        <div id="preview"></div>
    </div>

    <div id="imgAltEdit">
        <div id="imgAltEditInner">
            <form method="post" id="newImgAlt">New image description: 
                <input type="text" name="imgDescription" id="imgDescription">&nbsp;&nbsp;
                <input type="submit" name="newImgDesc" value="Set description">
            </form>
        </div>
    </div>

    <div id="imgHolder">
        <h2>Uploaded images</h2>
        <div class='searchHolder'>
            <input type='search' id='searchImgs' placeholder='Search images'>
        </div>
        <div id="imgHolderInner">

        </div>
    </div>

    <div id="postHolder">
        <h2>Previous posts</h2>
        <div class='searchHolder'>
            <input type='search' id='searchPosts' placeholder='Search posts'>
        </div>
        <ul class="postList">
        
        </ul>

    </div>
    
</div>
<?php include "../footer.php"; ?>
<script src="../js/adminjs.js"></script>
</body>
</html>