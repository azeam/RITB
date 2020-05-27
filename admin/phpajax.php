<?php
    // start session and get basic user info
    session_start();
    require_once("db.php");
    $db = db_connect();
    if (isset($_SESSION['username'])) {
        $_SESSION['username'] = $_SESSION['username']; // keepalive, not sure if necessary or if doing anything (like below) is enough, probably the latter
        $user = $_SESSION['username'];
        $sql = 'SELECT id FROM user WHERE username=\'' . db_escape($db, $user) . '\'';
        $result = db_select($db, $sql);
        if ($result != NULL) {
            $uid = $result[0]["id"];
        }

        // return formatted title for url
        function getUrlTitle($title) {
            $urltitle = htmlspecialchars($title);
            $urltitle = strtolower($urltitle);
            $urltitle = trim($urltitle);
            $urltitle = str_replace(" ", "-", $urltitle);
            $urltitle = urlencode($urltitle);
            return $urltitle;
        }
    
        // load profile data
        if(isset($_POST["getprofile"])) {
            $sql = 'SELECT * ';
            $sql .= 'FROM user ';
            $sql .= 'WHERE user.username=\'' . db_escape($db, $user) . '\' ';
            $result = db_select($db, $sql);
            foreach ($result as &$profile) {
                $userpres = $profile['presentation'];
                $userdispname = $profile['dispname'];
                $userimg = htmlspecialchars($profile['image']);
                $userimgdesc = htmlspecialchars($profile['imgdesc']);
                echo "pres: ".$userpres.": presend";
                echo "img: ".$userimg.": imgend";
                echo "imgdesc: ".$userimgdesc.": imgdescend";
                echo "dispname: ".$userdispname.": dispname";
            }
        }

        // update user presentation
        if(isset($_POST["pres"])) { 
            $presentation = $_POST["pres"];
            $sql = 'UPDATE user SET presentation=\'' . db_escape($db, $presentation) . '\' WHERE username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sql);
            echo "Your profile presentation has been updated";
        }

        // update user display name
        if(isset($_POST["dispname"]) && isset($_POST["setdispname"])) { 
            $dispname = $_POST["dispname"];
            $sql = 'UPDATE user SET dispname=\'' . db_escape($db, $dispname) . '\' WHERE username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sql);
            // update posts by user as well, or they will display old display name
            $sql = 'UPDATE post SET dispname=\'' . db_escape($db, $dispname) . '\' WHERE username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sql);
            echo "Your display name has been updated";
        }

        // update image description (alt text)
        // TODO: custom html attr with desc and separate alt text?
        if(isset($_POST["imgfile"]) && isset($_POST["imgdesc"])){
            $imgfile = $_POST["imgfile"];
            $imgfile2 = substr($imgfile, strrpos($imgfile, '/') + 1); // remove "../uploads" to display onle filename
            $imgdesc = $_POST["imgdesc"];
            $sql = 'UPDATE image SET image.description=\'' . db_escape($db, $imgdesc) . '\' WHERE filename=\'' . db_escape($db, $imgfile) . '\' AND username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sql);
            echo "The description of file <b>$imgfile2</b> has been changed";
        }

        // delete image
         if(isset($_POST["imgfile"]) && isset($_POST["imgdelete"])){
            $imgfile = $_POST["imgfile"];
            $imgfile2 = substr($imgfile, 3); // remove "../" as it's not in content
            $imgfile3 = substr($imgfile, strrpos($imgfile, '/') + 1); // remove "../uploads" to display onle filename
            $sql = 'SELECT * FROM post WHERE content LIKE \'%' . db_escape($db, $imgfile2) . '%\' AND username=\'' . db_escape($db, $user) . '\' ';
            $sql .= 'ORDER BY created DESC ';
            $result = db_query($db, $sql);
            if (mysqli_num_rows($result)!=0) {
                echo "Warning: The image <b>$imgfile3</b> is used in the following posts, edit or delete them before deleting the image:<br><br>"; // avoid img 404
                echo "<ul class='postListDialog'>";
                foreach ($result as $post) {
                    $posttitle = htmlentities($post['title']);
                    $postid = $post['id'];
                    $draft = $post['draft'];
                    $postcontent = str_replace("\n","\\n",$post['content']); // needed to not break option menu on newline
                    $postcontent = htmlspecialchars($postcontent);
                    echo "<li>";
                    if ($draft == 0) {
                        echo "<a href='#/' class='postRow' title='$posttitle' content='$postcontent' pid='$postid' draft='$draft'>$posttitle</a>";
                    }
                    else {
                        echo "<a href='#/' class='postRowDraft' title='$posttitle' content='$postcontent' pid='$postid' draft='$draft'>$posttitle</a>";
                    }
                    echo "</li>";
                }
                echo "</ul>";
            }
            else {
                $sql = 'DELETE FROM image WHERE filename=\'' . db_escape($db, $imgfile) . '\' AND username=\'' . db_escape($db, $user) . '\'';
                $result = db_query($db, $sql);
                unlink($imgfile);
                echo "The image <b>$imgfile3</b> has been deleted";
            }
        } 

        // delete post
        if(isset($_POST["postid"]) && isset($_POST["posttitle"]) && isset($_POST["postdelete"])){
            $postid = $_POST["postid"];
            $posttitle = $_POST["posttitle"];
            $sql = 'DELETE FROM post WHERE id=\'' . db_escape($db, $postid) . '\' AND username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sql);
            echo "The post <b>$posttitle</b> has been deleted";
        }

        // load images
        if(isset($_POST["loadimages"]) && isset($_POST["offset"]) && isset($_POST["limit"])) {
            $sql = 'SELECT image.filename, image.description ';
            $sql .= 'FROM image ';
            $sql .= 'WHERE image.username=\'' . db_escape($db, $user) . '\' ';
            $sql .= 'ORDER BY created DESC ';
            $sql .= 'LIMIT ' . db_escape($db, $_POST["offset"]) . ', ' . db_escape($db, $_POST["limit"]) . '';
            $imgresult = db_select($db, $sql);
            foreach ($imgresult as &$filename) {
                $imgfile = $filename['filename'];
                $imgdesc = $filename['description'];
                echo "<div class='singleImg'>";
                echo "<img src='$imgfile' alt='$imgdesc'>";
                echo "<div class='options'><ul><li><a href='#/' onclick=\"editImg('$imgfile', '$imgdesc')\">Edit image description</a></li><li><a href='#' onclick=\"appendImgLink('$imgfile', '$imgdesc')\">Add to blog text</a></li><li><a href='#' onclick=\"deleteImg('$imgfile', '$imgdesc')\">Delete image</a></li></div>";
                echo "</div>";
            }
        }

        // search images
        if(isset($_POST["searchimgstring"])) {
            $searchstring = $_POST["searchimgstring"];
                if ($searchstring != '') {
                    $sql = 'SELECT * FROM image WHERE image.username=\'' . db_escape($db, $user) . '\' AND filename LIKE \'%' . db_escape($db, $searchstring) . '%\' OR image.username=\'' . db_escape($db, $user) . '\' AND description LIKE \'%' . db_escape($db, $searchstring) . '%\' ';
                    $sql .= 'ORDER BY created DESC '; 
                }
                else {
                    return false; 
                }

                $result = db_select($db, $sql);   
                foreach ($result as &$filename) {
                    $imgfile = $filename['filename'];
                    $imgdesc = $filename['description'];
                    echo "<div class='singleImg'>";
                    echo "<img src='$imgfile' alt='$imgdesc'>";
                    echo "<div class='options'><ul><li><a href='#/' onclick=\"editImg('$imgfile', '$imgdesc')\">Edit image description</a></li><li><a href='#' onclick=\"appendImgLink('$imgfile', '$imgdesc')\">Add to blog text</a></li><li><a href='#' onclick=\"deleteImg('$imgfile', '$imgdesc')\">Delete image</a></li></div>";
                    echo "</div>";
                }
        }

        // load posts
        if(isset($_POST["loadposts"]) && isset($_POST["offset"]) && isset($_POST["limit"])) {
            $sql = 'SELECT * ';
            $sql .= 'FROM post ';
            $sql .= 'WHERE post.userId=\'' . db_escape($db, $uid) . '\' ';
            $sql .= 'ORDER BY created DESC ';
            $sql .= 'LIMIT ' . db_escape($db, $_POST["offset"]) . ', ' . db_escape($db, $_POST["limit"]) . '';
            
            $result = db_select($db, $sql);
            foreach ($result as &$post) {
                $posttitle = htmlentities($post['title']);
                $postid = $post['id'];
                $draft = $post['draft'];
                $urltitle = getUrlTitle($posttitle);
                echo "<li>";
                if ($draft == 0) {
                    echo "<a href='#/' class='postRow' pid='$postid'>$posttitle</a>";
                    echo "<div class='options'><ul><li><a href='../$postid-$urltitle' target='_blank'>Go to post (new tab)</a></li><li><a href='#' onclick=\"editPostLink('$postid')\">Edit blog post</a></li><li><a href='#' onclick=\"deletePost('$posttitle', '$postid')\">Delete blog post</a></li></div>";
                }
                else {
                    echo "<a href='#/' class='postRowDraft' pid='$postid'>$posttitle</a>";
                    echo "<div class='options'><ul><li><a href='#' onclick=\"editPostLink('$postid')\">Edit draft</a></li><li><a href='#' onclick=\"deletePost('$posttitle', '$postid')\">Delete draft</a></li></div>";
                }
                 echo "</li>";
            }
        }

        // search posts
        if(isset($_POST["searchstring"])) {
            $searchstring = $_POST["searchstring"];
                if ($searchstring != '') {
                    $sql = 'SELECT * FROM post WHERE post.userId=\'' . db_escape($db, $uid) . '\' AND content LIKE \'%' . db_escape($db, $searchstring) . '%\' OR post.userId=\'' . db_escape($db, $uid) . '\' AND title LIKE \'%' . db_escape($db, $searchstring) . '%\' ';
                    $sql .= 'ORDER BY created DESC '; 
                }
                else {
                    return false; 
                }

                $result = db_select($db, $sql);   
                foreach ($result as &$post) {
                    $posttitle = htmlentities($post['title']);
                    $postid = $post['id'];
                    $draft = $post['draft'];
                    $postcontent = str_replace("\n","\\n",$post['content']); // needed to not break option menu on newline
                    $postcontent = htmlspecialchars($postcontent);
                    $urltitle = getUrlTitle($posttitle);
                    echo "<li>";
                    if ($draft == 0) {
                        echo "<a href='#/' class='postRow' pid='$postid'>$posttitle</a>";
                        echo "<div class='options'><ul><li><a href='../$postid-$urltitle' target='_blank'>Go to post (new tab)</a></li><li><a href='#' onclick=\"editPostLink('$postid')\">Edit blog post</a></li><li><a href='#' onclick=\"deletePost('$posttitle', '$postid')\">Delete blog post</a></li></div>";
                    }
                    else {
                        echo "<a href='#/' class='postRowDraft' pid='$postid'>$posttitle</a>";
                        echo "<div class='options'><ul><li><a href='#' onclick=\"editPostLink('$postid')\">Edit draft</a></li><li><a href='#' onclick=\"deletePost('$posttitle', '$postid')\">Delete draft</a></li></div>";
                    }
                    echo "</li>";
                }
        }
        
        // publish blog post
        if(isset($_POST["publish"]) && $_POST["title"] != '' && $_POST["content"] != '' && $_POST["dispname"] != '') { 
            if (isset($_POST["draft"]) && $_POST["draft"] == -1) {
                $draft = -1;
            }
            else {
                $draft = 0;
            }
            $title = $_POST["title"]; 
            $content = $_POST["content"];
            $dispname = $_POST["dispname"];
            $urltitle = getUrlTitle($title);
            $sqlpublish = 'INSERT INTO post (userId, username, title, content, dispname, draft) VALUES (\'' . db_escape($db, $uid) . '\', \'' . db_escape($db, $user) . '\', \'' . db_escape($db, $title) . '\', \'' . db_escape($db, $content) . '\', \'' . db_escape($db, $dispname) . '\', \'' . db_escape($db, $draft) . '\')';
            $result = db_query($db, $sqlpublish);
            $lastid = mysqli_insert_id($db);
            if ($draft == 0) {
                echo "The post <b>$title</b> has been published, <a href='../$lastid-$urltitle' target='_blank'>go to post</a> (new tab)";
            }
            else {
                echo "The post <b>$title</b> has been saved as draft";
            }
        }  

        // edit blog post
        if(isset($_POST["postedit"]) && isset($_POST["pid"])) { 
            $pid = $_POST["pid"];
            $sqlpublish = 'SELECT * FROM post WHERE id=\'' . db_escape($db, $pid) . '\' AND username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sqlpublish);
            foreach ($result as $post) {
                $title = $post["title"];
                $content = $post["content"];
                $draft = $post["draft"];
                echo $title.":::".$content.":::".$pid.":::".$draft;
            }
        } 

        

        // update blog post
        if(isset($_POST["update"]) && $_POST["title"] != '' && $_POST["content"] != ''  && $_POST["pid"] != '' && $_POST["dispname"] != '' && isset($_POST["draft"])) { 
            $title = $_POST["title"]; 
            $draft = $_POST["draft"];
            $urltitle = getUrlTitle($title);
            $content = $_POST["content"];
            $pid = $_POST["pid"];
            $dispname = $_POST["dispname"];
            $sqlpublish = 'UPDATE post SET userId=\'' . db_escape($db, $uid) . '\', username=\'' . db_escape($db, $user) . '\', title=\'' . db_escape($db, $title) . '\', content=\'' . db_escape($db, $content) . '\', dispname=\'' . db_escape($db, $dispname) . '\' WHERE id=\'' . db_escape($db, $pid) . '\' AND username=\'' . db_escape($db, $user) . '\'';
            $result = db_query($db, $sqlpublish);
            if ($draft == 0) {
                echo "The post <b>$title</b> has been updated, <a href='../$pid-$urltitle' target='_blank'>go to post</a> (new tab)";
            }
            else {
                echo "The draft <b>$title</b> has been updated";
            }
             
        }
        // resize image
        function resize($file, $maxsize, $imageFileType) {
            list($width, $height) = getimagesize($file);
            $ratio = $width / $height;
            
            if ($width/$height < 1) { // vertical
                $newwidth = $maxsize*$ratio;
                $newheight = $maxsize;
            } else { // horizontal or square
                $newheight = $maxsize/$ratio;
                $newwidth = $maxsize;
            }
            
            switch($imageFileType){
                case "png":
                    $src = imagecreatefrompng($file);
                break;
                case "jpeg":
                case "jpg":
                    $src = imagecreatefromjpeg($file);
                break;
                case "gif":
                    $src = imagecreatefromgif($file);
                break;
                default:
                    $src = imagecreatefromjpeg($file);
                break;
            }
            
            $dest = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            return $dest;
        }

        // upload image, based on https://www.w3schools.com/php/php_file_upload.asp
        if (isset($_FILES["fileToUpload"]) && $_FILES['fileToUpload']['name'] != ""){ 
            if (isset($_POST["profile"])) {
                $target_dir = "../uploads/$uid/profileimg/";    
            }
            else {
                $target_dir = "../uploads/$uid/"; 
            }
            $targetFile = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $targetFile = $targetFile = str_replace(" ", "_", $targetFile); 
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
            // Check if image file is an actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = 1;
                } else {
                    echo "Error: File is not an image.";
                    $uploadOk = 0;
                }
            }
            // make folder if it doesn't exist
            if (!is_dir($target_dir)) {
                if(!mkdir($target_dir, 0755, true)) {
                    echo "Error: No permission to create folder.";
                    $uploadOk = 0;
                }
            }
            // Check if file already exists, profile images can be overwritten
            if (!isset($_POST["profile"])) {
                if (file_exists($targetFile)) {
                    echo "Error: File already exists, choose a different file name.";
                    $uploadOk = 0;
                }
            }
            
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                echo "Error: Only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk != 0) {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
                    $desc = basename( $_FILES["fileToUpload"]["name"]);
                    $desc = substr($desc, 0, strrpos( $desc, '.') ); // remove file extension and use filename as default description
                    $filename = $targetFile;
                    $resizedFilename = $targetFile;
                    if (isset($_POST["profile"])) {
                        $desc = "Profile image";
                        $sqlpublish = 'UPDATE user SET image=\'' . db_escape($db, $targetFile) . '\', imgdesc=\'' . db_escape($db, $desc) . '\' WHERE username=\'' . db_escape($db, $user) . '\'';
                        $maxsize = 200;
                    }
                    else {
                        $sqlpublish = 'INSERT INTO image (filename, username, description) VALUES (\'' . db_escape($db, $targetFile) . '\', \'' . db_escape($db, $user) . '\', \'' . db_escape($db, $desc) . '\')';
                        $maxsize = 1000;
                    }
                    
                    // resize if image is larger than maxsize 
                    list($width, $height) = getimagesize($targetFile);
                    if ($width > $maxsize || $height > $maxsize) {
                        $imgData = resize($filename, $maxsize, $imageFileType);
                        $quality = 85;
                        if($imageFileType == "jpg" || $imageFileType == "jpeg") {
                            imagejpeg($imgData, $resizedFilename, $quality);
                        }
                        if($imageFileType == "png") {
                            $pngQuality = ($quality - 100) / 11.111111; // png quality 1-9 in reverse and not 0-100 as jpg, or just hard-code 1
                            $pngQuality = round(abs($pngQuality));
                            imagepng($imgData, $resizedFilename, $pngQuality);
                        }
                        if($imageFileType == "gif") {
                            imagegif($imgData, $resizedFilename); 
                        }
                    } 
                    
                    $result = db_query($db, $sqlpublish);
                    echo ($targetFile.":::".$desc);
                } else {
                    echo "Error: unable to upload your file. Try with a smaller image file (less than 2 MB).";
                }
            }
        }
    }
    // disconnect when finished
    db_disconnect($db);
?>