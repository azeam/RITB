    // globals for scroll load
    var iloading = false;
    var ploading = false;
    var ilimit = 10; // images to load each time
    var ioffset = 0;
    var plimit = 25; // posts to load each time
    var poffset = 0;
    // set position in textarea globally, this way uploaded images can be uploaded to last position in text
    var text = document.forms['blogpost']['text'];
    var start = 0;
    var end = 0;

    // keep session alive while page is active
    function keepAlive() {
        var time = 600000; // 10 minutes
        setTimeout(function() {
            $.ajax({
                url: 'phpajax.php',
                cache: false,
                complete: function() {
                    keepAlive();
                }
            });
        }, time 
        );
    };

    // hide when clicking elsewhere
    $(document).mouseup(function(e) {
        container = $('#imgAltEdit');
        if (!container.is(e.target) && container.has(e.target).length === 0) 
        {
            container.hide();
        }
        container = $('#dialog');
        if (!container.is(e.target) && container.has(e.target).length === 0 && !$('#profileBtn').is(e.target)) 
        {
            container.hide();
        }
        container = $('#profileEditHolder');
        if (!container.is(e.target) && container.has(e.target).length === 0 && !$('#profileBtn').is(e.target)) 
        {
            container.hide();
            $('#profileBtn').removeClass("darkBtn_jsset").addClass("darkBtn");
        }
    });

     // load profile
     function loadProfile() {
        $.ajax({ 
            url: "../admin/phpajax.php",
            data: { getprofile: true },
            type: "POST"
            }).done(function(response) {
                var pres = response.split("pres: ")[1];
                pres = pres.split(": presend")[0];
                var imgfile = response.split("img: ")[1];
                imgfile = imgfile.split(": imgend")[0];
                var imgdesc = response.split("imgdesc: ")[1];
                imgdesc = imgdesc.split(": imgdescend")[0];
                var dispname = response.split("dispname: ")[1];
                dispname = dispname.split(": dispname")[0];
                $('.profileImg').attr('src', imgfile);
                $('.profileImg').attr('alt', imgdesc);
                document.forms['profile']['udesc'].value = pres;
                document.forms['dispnameForm']['dispname'].value = dispname;
            });
    }

    // update profile presentation
    $('#save').click(function( event ) {
        event.preventDefault(); // don't send form, causes refresh
        var pres = document.forms['profile']['udesc'].value; // get set value
        pres = DOMPurify.sanitize(pres, {SAFE_FOR_JQUERY: true});
        if (pres.length > 0) {
            $.ajax({
                url: "../admin/phpajax.php",
                data: { pres: pres },
                type: "POST"
                }).done(function(response) {
                    loadProfile();
                    showDialog(response);
                });
        }
        else {
            showDialog("Warning: Fill in the presentation before updating");
        }
    });
    
    // update display name
    $('#saveDispname').click(function( event ) {
        event.preventDefault();
        var dispname = document.forms['dispnameForm']['dispname'].value;
        dispname = DOMPurify.sanitize(dispname, {SAFE_FOR_JQUERY: true});
        if (dispname.length > 0) {
            $.ajax({
                url: "../admin/phpajax.php",
                data: { dispname: dispname, setdispname: true },
                type: "POST"
                }).done(function(response) {
                    loadProfile();
                    showDialog(response);
                });
        }
        else {
            showDialog("Warning: Fill in the display name before updating");
        }
    });

    // show edit profile menu
    $('#profileBtn').click(function( event ) {
        event.preventDefault();
        if ($('#profileEditHolder').is(':visible')) {
            $('#profileEditHolder').slideUp(100);
            $('#profileBtn').removeClass("darkBtn_jsset").addClass("darkBtn");
        }
        else {
            $('#profileEditHolder').slideDown(100);
            $('#profileBtn').removeClass("darkBtn").addClass("darkBtn_jsset");
        }    
    });

    function escapeRegExp(string) {
        return string.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // escape special chars https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions#Escaping
    }

    // delete image
    function deleteImg(imgfile, imgdesc) {
        var n = imgfile.lastIndexOf('/'); // needs \ on windows server?
        var imgfiledisp = imgfile.substring(n + 1); // hide path
        if (confirm("Do you really want to delete the image "+imgfiledisp+"?")) { 
            $.ajax({ 
                url: "../admin/phpajax.php",
                data: { imgfile: imgfile, imgdelete: true },
                type: "POST"
                }).done(function(response) {
                    // remove img from text if there
                    curText = text.value;
                    if (curText.includes('<img src=\"'+imgfile+'\" alt=\"'+imgdesc+'\"><div class=\"textImgMain\"><i>'+imgdesc+'</i></div>')) {
                        var toescape = '<img src=\"'+imgfile+'\" alt=\"'+imgdesc+'\"><div class=\"textImgMain\"><i>'+imgdesc+'</i></div>';
                        var regexp = escapeRegExp(toescape);
                        var newText = curText.replace(new RegExp(regexp, 'g'), '');
                        text.value = newText;
                        manualPreview();
                    }
                    $(window).scrollTop(0);
                    ioffset = 0;
                    loadImages();
                    showDialog(response);
                    startPostHoverMenuDialog();
                });
            return false;
        }
    }

    // show menu on img hover
    function startImgHoverMenu() {
        $(".singleImg").mouseenter(function(){ 
            // TODO: slidedown/up is prettier but possible to fail if quickly leaving and entering before completely hidden
            // using mouseenter instead of hover and check if hidden, prevents some jumping issues with slidedown
            if ($(".options", this).is(":hidden")) {
                $(".options", this).show();
            }
        });
        $(".singleImg").mouseleave(function(){ 
            if ($(".options", this).is(":visible")) {
                $(".options", this).hide();
            }
        });
    }

    // load and display images uploaded by user, called after various image alterations for updates
    function loadImages() {
        $.ajax({ 
             url: "../admin/phpajax.php",
             data: { loadimages: true, offset: ioffset, limit: ilimit },
             type: "POST"
             }).done(function(response) {
                if (response.includes('<img')) { // don't try to add if there are no more posts and don't add if session has expired
                    if (ioffset == 0) {
                        $("#imgHolderInner").html(""); // clear imgholder to reload instead of append                                                
                    }
                    $("#imgHolderInner").append(response);
                    startImgHoverMenu();
                }
                iloading = false;    
             });
        return false;
    } 
    
    // change img alt text
    function editImg(imgfile, imgdesc) {
        document.forms['newImgAlt']['imgDescription'].value = imgdesc;
        $('#imgAltEdit').css({'top':event.pageY,'left':event.pageX});  
        $('#imgAltEdit').show();   
        $('#newImgAlt').submit(function( event ) {
            var newimgdesc = $('#imgDescription').val();
            newimgdesc = DOMPurify.sanitize(newimgdesc, {SAFE_FOR_JQUERY: true});
            event.preventDefault();
            $.ajax({ 
            url: "../admin/phpajax.php",
            data: { imgfile: imgfile, imgdesc: newimgdesc },
            type: "POST"
            }).done(function(response) {
                $('#imgAltEdit').hide();
                var curText = document.forms['blogpost']['text'].value;
                // replace active text desc
                if (curText.includes(' alt="'+imgdesc+'"><div class="textImgMain"><i>'+imgdesc+'</i></div>')) {
                    // escape all occurrences of string, probably not very necessary but annyoing if only one img updates and not the other, 
                    // in case of multiple instances of the same image
                    var toescape = ' alt="'+imgdesc+'"><div class="textImgMain"><i>'+imgdesc+'</i></div>';
                    var regexp = escapeRegExp(toescape);
                    var newText = curText.replace(new RegExp(regexp, 'g'), ' alt="'+newimgdesc+'"><div class="textImgMain"><i>'+newimgdesc+'</i></div>');
                    document.forms['blogpost']['text'].value = newText;
                    manualPreview();
                }
                $(window).scrollTop(0);
                ioffset = 0;
                loadImages();
                showDialog(response);
            });
        });
    } 

    // update global position on mouse click
    $('#text').click(function( event ) {
        start = text.selectionStart;
        end = text.selectionEnd;    
    });

    // add link html
    $('#link').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            // if selection has http - presume link and set as href, otherwise set selection as title
            if (selected.includes("http")) {
                text.value = textBefore+'<a href="' + selected + '">linktext</a>'+textAfter;
            }
            else {
                text.value = textBefore+'<a href="URL">' + selected + '</a>'+textAfter;
            }
        }
        else {
            text.value = textBefore+'<a href="URL">linktext</a>'+textAfter;   
        }
        manualPreview();
    });

    // add headline html
    $('#headline').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            text.value = textBefore+"<h3>"+selected+"</h3>"+textAfter;
        }
        else {
            text.value = textBefore+"<h3>headline</h3>"+textAfter;
        }
        manualPreview();
    });

    // add ul html
    $('#listBtn').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            selarray = selected.split("\n");
            text.value = textBefore+"<ul class='bulletUl'>";
            $.each(selarray, function(i){
                text.value = text.value+"<li>"+selarray[i]+"</li>";
            });
            text.value = text.value+"</ul>"+textAfter;
        }
        else {
            text.value = textBefore+"<ul class='bulletUl'><li>item 1</li><li>item 2</li><li>item 3</li></ul>"+textAfter;       
        }
        manualPreview();
    });

    // add ol html
    $('#nmbrListBtn').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            selarray = selected.split("\n");
            text.value = textBefore+"<ol>";
            $.each(selarray, function(i){
                text.value = text.value+"<li>"+selarray[i]+"</li>";
            });
            text.value = text.value+"</ol>"+textAfter;
        }
        else {
            text.value = textBefore+"<ol><li>item 1</li><li>item 2</li><li>item 3</li></ol>"+textAfter;       
        }       
        manualPreview();
    });

    // add bold html
    $('#boldBtn').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            text.value = textBefore+"<b>"+selected+"</b>"+textAfter;
        }
        else {
            text.value = textBefore+"<b>bold</b>"+textAfter;
        }
        manualPreview();
    });

    // add italic html
    $('#italicBtn').click(function( event ) {
        event.preventDefault();
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        if (start!=end) {
            selected = window.getSelection().toString();
            text.value = textBefore+"<i>"+selected+"</i>"+textAfter;
        }
        else {
            text.value = textBefore+"<i>italic</i>"+textAfter;
        }
        manualPreview();
    });

    // add img link to blog post from options menu
    function appendImgLink(imgfile, imgdesc) {
        textBefore = text.value.substring(0, start);
        textAfter  = text.value.substring(end, text.length);
        text.value = textBefore+'<img src=\"'+imgfile+'\" alt=\"'+imgdesc+'\"><div class=\"textImgMain\"><i>'+imgdesc+'</i></div>'+textAfter;
        manualPreview(); 
    }
 
    // upload image
    // TODO: sanitize filename?
    $('#upload').submit(function( event ) {
        event.preventDefault();
        if ($('#fileSelected').html() == 'No file selected') {
            showDialog("Error: No file selected, first select an image to upload.")
        }
        else {
            var formData = new FormData(this);
            $.ajax({ 
                url: "../admin/phpajax.php",
                data: formData,
                contentType: false, 
                cache: false,
                processData:false,
                beforeSend: function(){
                    $("#spinner").show();
                },
                type: "POST"
                }).done(function(response) {
                    if (response.includes("Error: ")) {
                        $(window).scrollTop(0);
                        showDialog(response);
                    }
                    else if (response.includes(":::")) {
                        // append uploaded image to blog text by default
                        var imgfile = response.split(":::")[0];
                        var imgdesc = response.split(":::")[1];
                        textBefore = text.value.substring(0, start);
                        textAfter  = text.value.substring(end, text.length);
                        text.value = textBefore+'<img src=\"'+imgfile+'\" alt=\"'+imgdesc+'\"><div class=\"textImgMain\"><i>'+imgdesc+'</i></div>'+textAfter;
                        document.forms['upload'].reset();
                        $('#fileSelected').html('No file selected');
                        manualPreview();
                        $(window).scrollTop(0);
                        ioffset = 0;
                        loadImages();
                    }
                    $("#spinner").hide();
                });
            }
    });

    // upload profile image
    // TODO: sanitize filename?
    $('#uUpload').submit(function( event ) {
        event.preventDefault();
        if ($('#uFileSelected').html() == 'No file selected') {
            showDialog("Error: No file selected, first select an image to upload.")
        }
        else {
            var formData = new FormData(this);
            formData.append('profile', true);
            $.ajax({ 
                url: "../admin/phpajax.php",
                data: formData,
                contentType: false, 
                cache: false,
                processData:false,
                beforeSend: function(){
                    $("#profileSpinner").show();
                },
                type: "POST"
                }).done(function(response) {
                    if (response.includes("Error: ")) {
                        $(window).scrollTop(0);
                        showDialog(response);
                    }
                    else if (response.includes(":::")) {
                        // append uploaded image to profile img
                        var imgfile = response.split(":::")[0];
                        var imgdesc = response.split(":::")[1];
                        $('.profileImg').attr('src', imgfile);
                        $('.profileImg').attr('alt', imgdesc); 
                        $('#uFileSelected').html('No file selected');
                        showDialog("Your profile image has been updated")
                    } 
                    $("#profileSpinner").hide();
                });
            }
    });
 
    // show notifications
    function showDialog(text) {
        if (text.includes("Warning") || text.includes("Notice")) { // TODO: if php error the ": " is not recognized for some reason, just check word for now, will cause more false negatives
            $("#dialog").addClass("warningDialog"); // addclass to more easily handle hover color
            $("#dialog").css("background-color", "#ffffa3");
        }
        else if (text.includes("Error")) {
            $("#dialog").removeClass("warningDialog");
            $("#dialog").css("background-color", "#ff8282");
        }
        else {
            $("#dialog").removeClass("warningDialog");
            $("#dialog").css("background-color", "#87db8e");
        }
        $("#dialogContent").html(text);  
        $("#dialog").show(); 
    }

    // load more images on scroll down
    function startImgScroll() {
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() > $("#imgHolder").height() && !iloading) {
                iloading = true; 
                ioffset = ioffset + ilimit;
                loadImages(ilimit, ioffset);
            }
        });
    }

    // load more posts on scroll down
    function startPostScroll() {
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() > $("#postHolder").height() && !ploading) {
                ploading = true; 
                poffset = poffset + plimit;
                loadPosts(plimit, poffset);
            }
        });
    }

    // monitor images dropped to text area and append html
    function startDragDrop() {
        $("#text")
            .bind("dragover", false)
            .bind("dragenter", false)
            .bind("drop", function(e) {
                var droppedHTML = e.originalEvent.dataTransfer.getData("text/html");
                droppedHTML = decodeURIComponent(droppedHTML); // fix entity display with åäö (etc.) in filename
                if (droppedHTML.includes("<img src") && droppedHTML.includes("/uploads/") && !droppedHTML.includes("postRow")) {
                    var removepath = droppedHTML.split('/uploads/')[1];
                    droppedHTML = '<img src="../uploads/' + removepath;
                    var alttext = droppedHTML.split('alt="')[1];
                    alttext = alttext.split('"')[0];
                    textBefore = text.value.substring(0, start);
                    textAfter  = text.value.substring(end, text.length);
                    text.value = textBefore + droppedHTML + '<div class=\"textImgMain\"><i>'+alttext+'</i></div>'+textAfter;
                    manualPreview();
                }
            return false;
        });
    }

    // reduce width if img is vertical
    // TODO: does not work 100% sometimes, seems to happen in chrome when multiple instances of the page are open at the same time
    // tested with load/ready but doesn't make any difference = doesn't seem to be dom rendering caused (even though it should be)
    // works better in firefox
    function setVertImg() {
        $("#preview img").each(function(){
            var $this = $(this);
            if ($this.width() < $this.height()) {
                $this.addClass("vertical");
            }
        });
    }

    // update preview when not updated with keystrokes
    function manualPreview() {
        var text = document.getElementById('text');
        var title = document.getElementById('title');
        document.getElementById('preview').innerHTML = "<h1>" + title.value + "</h1>" + text.value;
        setVertImg();
        setExtLinks();
    }

    // listen for keystrokes and update preview automatically
    function startPreview() {
        var text = document.getElementById('text');
        var title = document.getElementById('title');
        text.onkeyup = text.onkeypress = function(){
            document.getElementById('preview').innerHTML = "<h1>" + title.value + "</h1>" + text.value;
            setVertImg();
            setExtLinks();
            // update global position on typing
            start = text.selectionStart;
            end = text.selectionEnd;
        } 
        title.onkeyup = title.onkeypress = function(){
            document.getElementById('preview').innerHTML = "<h1>" + title.value + "</h1>" + text.value;
        }
    }

    // monitor file selection and display filename
    $('#fileToUpload').bind('change', function() { 
        var filename = ''; 
        filename = $(this).val(); 
        var n = filename.lastIndexOf('\\');
        var filedisp = filename.substring(n + 1);
        $('#fileSelected').html(filedisp); 
    })
    $('#uFileToUpload').bind('change', function() { 
        var filename = ''; 
        filename = $(this).val();
        var n = filename.lastIndexOf('\\');
        var filedisp = filename.substring(n + 1);
        $('#uFileSelected').html(filedisp);
    })

    // publish blog post or save as draft
    $('#publish, #draft').click(function( event ) { 
        var content = document.forms['blogpost']['text'].value;
        var title = document.forms['blogpost']['title'].value;
        var dispname = document.forms['dispnameForm']['dispname'].value; // get dispname from profile edit form
        var draft = 0;
        dispname = DOMPurify.sanitize(dispname, {SAFE_FOR_JQUERY: true});
        content = DOMPurify.sanitize(content, {SAFE_FOR_JQUERY: true});
        title = DOMPurify.sanitize(title, {SAFE_FOR_JQUERY: true});
        if (event.target.id === 'draft') {
            draft = -1;
        }
        event.preventDefault(); // prevent page reload
        if (title.length > 0 && content.length > 0) { 
            $.ajax({ 
            url: "../admin/phpajax.php",
            data: { content: content, title: title, dispname: dispname, publish: true, draft: draft },
            type: "POST"
            }).done(function(response) {
                $("#update").hide();
                $("#draft").show();
                $("#publish").attr("value", "Publish"); // reset if clicked from edit post
                document.forms['blogpost'].reset();
                manualPreview();
                $(window).scrollTop(0);
                poffset = 0;
                loadPosts(plimit, poffset);
                showDialog(response); 
            });
        }
        else {
            if (draft == 0) {
                showDialog("Warning: Fill in title and text before publishing");
            }
            else {
                showDialog("Warning: Fill in title and text before saving");
            }
        }
    }); 

    // update old blog post
    $('#update').click(function( event ) {
        var content = document.forms['blogpost']['text'].value;
        var title = document.forms['blogpost']['title'].value;
        var pid = document.forms['blogpost']['pid'].value;
        var draft = document.forms['blogpost']['draftbool'].value;
        var dispname = document.forms['dispnameForm']['dispname'].value;
        dispname = DOMPurify.sanitize(dispname, {SAFE_FOR_JQUERY: true});
        content = DOMPurify.sanitize(content, {SAFE_FOR_JQUERY: true});
        title = DOMPurify.sanitize(title, {SAFE_FOR_JQUERY: true});

        event.preventDefault();
        if (title.length > 0 && content.length > 0) { 
            $.ajax({ 
            url: "../admin/phpajax.php",
            data: { content: content, title: title, pid: pid, dispname: dispname, update: true, draft: draft },
            type: "POST"
            }).done(function(response) {
                $("#update").hide();
                $("#draft").show();
                $("#publish").attr("value", "Publish"); 
                document.forms['blogpost'].reset();
                manualPreview();
                $(window).scrollTop(0);
                poffset = 0;
                loadPosts(plimit, poffset);
                showDialog(response);
            });
        }
        else {
            if (draft == 0) {
                showDialog("Warning: Fill in title and text before publishing");
            }
            else {
                showDialog("Warning: Fill in title and text before saving");
            }
        }
    });

    // start search functions
    function startSearch() {
        document.getElementById("searchPosts").onkeyup = function() { // search db instead of js search because not all posts are loaded
            loading = true;
            var input = document.getElementById('searchPosts').value;
            input = DOMPurify.sanitize(input, {SAFE_FOR_JQUERY: true});
            if (input.length > 0) {
                $.ajax({ 
                    url: "../admin/phpajax.php",
                    data: { searchstring: input },
                    type: "POST"
                    }).done(function(response) {
                        $('#postHolder .postList').html(response);
                        startPostHoverMenu();
                    });
            }
            else {
                poffset = 0;
                loadPosts(plimit, poffset);
            }
            return false;
        }

        document.getElementById("searchImgs").onkeyup = function() { // search db instead of js search because not all imgs are loaded
            loading = true;
            var input = document.getElementById('searchImgs').value;
            input = DOMPurify.sanitize(input, {SAFE_FOR_JQUERY: true});
            if (input.length > 0) {
                $.ajax({ 
                    url: "../admin/phpajax.php",
                    data: { searchimgstring: input },
                    type: "POST"
                    }).done(function(response) {
                        $('#imgHolderInner').html(response);
                        startImgHoverMenu();
                    });
            }
            else {
                ioffset = 0;
                loadImages(ilimit, ioffset);
            }
            return false;
        }
    }

    // load and display posts by user, called after various post alterations for updates
    function loadPosts(plimit, poffset) {
        $.ajax({ 
            url: "../admin/phpajax.php",
            data: { loadposts: true, offset: poffset, limit: plimit },
            type: "POST"
            }).done(function(response) {
                if (response.includes("<li>")) { // don't try to add if there are no more posts
                    if (poffset == 0) {
                        $("#postHolder .postList").html("");     
                    }
                    $("#postHolder .postList").append(response);
                    startPostHoverMenu();                   
                }
                ploading = false;
                });
        return false; 
    } 

    // show menu for post links when trying to delete images that are in some published post on hover
    function startPostHoverMenuDialog() {
        $(".postListDialog li").each(function(){
            var postid = $("a", this).attr('pid');
            var posttitle = $("a", this).attr('title');
            var draft = $("a", this).attr('draft');
            // TODO: fix urltitle and set link to that
            var urltitle = posttitle.toLowerCase();
            urltitle = urltitle.trim();
            urltitle = urltitle.replace(/ /g, '-');
            urltitle = encodeURIComponent(urltitle);
            // harder to do with dynamic jquery than onclick because of DOM rendering issues
            // safe to append with jquery instead of php since we load all items 
            if (draft == 0) {
                $(this).append('<div class="options"><ul><li><a href="../' + postid + '-' + urltitle + '" target="_blank">Go to post (new tab)</a></li><li><a href="#/" onclick="editPostLink(\'' + postid + '\');">Edit blog post</a></li><li><a href="#/" onclick="deletePost(\'' + posttitle + '\', \'' + postid + '\');">Delete post</a></li></div>' ); 
            }
            else {
                $(this).append('<div class="options"><ul><li><a href="#/" onclick="editPostLink(\'' + postid + '\');">Edit draft</a></li><li><a href="#/" onclick="deletePost(\'' + posttitle + '\', \'' + postid + '\');">Delete draft</a></li></div>' ); 
            }
        }); 
        $(".postListDialog li").mouseenter(function(){ 
            if ($(".options", this).is(":hidden")) {
                if ($(this).parents('.warningDialog').length) {
                    // messier than it should be, but works as intended with different hover colors for options menu depending on dialog type
                    // and sticky color on selected item
                    $("a", this).css("background-color", "#a4a43c");
                    $(".options a").css("background-color", "#38497E"); 
                }
                $(".options", this).show();
            }
        });
        $(".postListDialog li").mouseleave(function(){ 
            if ($(".options", this).is(":visible")) {
                if ($(this).parents('.warningDialog').length) {
                    $("a", this).css("background-color", "#ffffa3");
                }
                $(".options", this).hide();
            }
        });
    }

    // show menu for post items on hover
    function startPostHoverMenu() {
        $(".postList li").mouseenter(function(){  
            if ($(".options", this).is(":hidden")) {
                $(".postRow", this).css("background-color", "#b5c3e4");
                $(".postRowDraft", this).css("background-color", "#a4a43c");
                $(".options a", this).css("background-color", "#38497E");
                $(".options", this).show();
            }
        });
        $(".postList li").mouseleave(function(){ 
            if ($(".options", this).is(":visible")) {
                $(".postRow", this).css("background-color", "white");
                $(".postRowDraft", this).css("background-color", "#ffffa3");
                $(".options", this).hide();
            }
        });
    }

    // delete post
    function deletePost(posttitle, postid) { 
        posttitle = decodeURIComponent(posttitle);
        if (confirm("Do you really want to delete the post "+posttitle+"?")) { 
            $.ajax({ 
                url: "../admin/phpajax.php",
                data: { postid: postid, posttitle: posttitle, postdelete: true },
                type: "POST"
                }).done(function(response) {
                    poffset = 0;
                    loadPosts(plimit, poffset);
                    $(window).scrollTop(0);
                    showDialog(response);
                });
            return false;
        } 
    }

    // load old blog post to form for editing
    function editPostLink(postid) {
        $.ajax({ 
            url: "../admin/phpajax.php",
            data: { pid: postid, postedit: true },
            type: "POST"
            }).done(function(response) {
                posttitle = response.split(":::")[0];
                postcontent = response.split(":::")[1];
                postid = response.split(":::")[2];
                draft = response.split(":::")[3];   
                document.forms['blogpost']['text'].value = postcontent;
                document.forms['blogpost']['title'].value = posttitle;
                document.forms['blogpost']['pid'].value = postid; // add post id to hidden field, a bit hacky... TODO: set as custom attribute
                document.forms['blogpost']['draftbool'].value = draft;
                $(window).scrollTop(0);
                if (draft == 0) {
                    $("#update").attr("value", "Update post");
                    $("#update").show();
                    $("#publish").attr("value", "Publish as new");
                    $("#draft").show();
                }
                else {
                    $("#update").attr("value", "Update draft");
                    $("#update").show();
                    $("#publish").attr("value", "Publish");
                    $("#draft").hide();
                }
                manualPreview();        
            });        
        return false;
    }

    // set icon and target on links to external sites (assume external if they contain http)
    function setExtLinks() {
        $('#preview a[href^=http]').addClass('external');
        $('#preview a[href^=http]').attr('target', '_blank');
    }

    // clear form
    $('#clear').click(function( event ) {
        event.preventDefault(); 
        if (confirm("Do you really want to clear all written text?")) {
            document.forms['blogpost'].reset();
            $("#update").hide();
            $("#publish").attr("value", "Publish");
            manualPreview();
        }
    }); 
    
    $(document).ready(function() {
        // keep session alive while page is active
        keepAlive();
        // get profile data
        loadProfile();
        // load images and posts by user on document load
        startPostScroll();
        startImgScroll();
        loadImages(ilimit, ioffset);
        loadPosts(plimit, poffset);
        // start preview listener
        startPreview();
        // enable drag and drop to text area
        startDragDrop();
        // enable search function
        startSearch();
        // TODO: fix with css
        $("#update").hide();
    });