    var loading = false;
    var limit = 10;
    var offset = 0; 
    var uid = 0;

    // set user and start autofetch on scroll
    // TODO: this can probably be done a bit differently after changing user selection to GET
    function setUser(phpuid) {
        uid = phpuid;
        loading = false; // start autoload on scroll (for when selecting a user after single page view)
        offset = 0; // reset offset when changing user
        $("#content").html(''); // clear content
        getPosts(uid, limit, offset); // use global uid and username since second scroll load will use global var
        // script to load more content on end of page scroll
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() > $("#content").height() && !loading) {
                loading = true; 
                getPosts(uid, limit, offset) 
                offset = limit + offset; // next 10 posts
            }
        });
        $('#menu a[href^="./'+uid+'_"]').css('background-color', '#dbe2f3'); // css a:active doesn't work after get req, set with jquery
    }   

    // start search functions
    function startSearch() { // https://www.w3schools.com/howto/howto_js_filter_lists.asp
        document.getElementById("searchAuthors").onkeyup = function() { 
            // Declare variables
            var a, i, txtValue;
            var input = document.getElementById('searchAuthors');
            var filter = input.value.toUpperCase();
            filter = DOMPurify.sanitize(filter, {SAFE_FOR_JQUERY: true});
            var content = document.getElementById("menu");
            var li = content.getElementsByTagName('li');
            // Loop through all list items, and hide those who don't match the search query
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByTagName("a")[0]; 
                txtValue = a.text; 
                if (txtValue.toUpperCase().indexOf(filter) > -1) { 
                    li[i].style.display = "";

                } else {
                    li[i].style.display = "none";
                }
            }
        }

        document.getElementById("searchPosts").onkeyup = function() { // search db instead of js search because not all posts are loaded
            loading = true;
            var input = document.getElementById('searchPosts').value;
            input = DOMPurify.sanitize(input, {SAFE_FOR_JQUERY: true});
            if (input.length > 0) {
                $.ajax({ 
                    url: "content.php",
                    data: { searchstring: input },
                    type: "POST"
                    }).done(function(response) {
                        $('#content').html(response);
                        document.title = "The Blog - Search results";
                    });
            }
            else {
                setUser(uid); // this will reload the content for the selected user (or user 0 if none selected) if emptying search box
            }
            return false;
        }
    }

    // populate content
    function getPosts(uid, limit, offset) {
        $.ajax({ 
            url: "content.php",
            data: { uid: uid, limit: limit, offset: offset }, 
            type: "POST" 
            }).done(function(response) {
                if (response != '') { // don't try to add if there are no more posts
                    if (offset == 0) {
                        $("#content").html(response); 
                    }
                    else {
                        $("#content").append(response);
                    }
                }
                if (response == '' && offset == 0) {
                    $("#content").html("<h2>No posts by this author</h2>");
                }
                loading = false;
        }); 
    }
 
    // set css for vertical images
    function setVertImg() {
        $(".post img").each(function(){
            var $this = $(this);
            if ($this.width() < $this.height()) {
                $this.addClass("vertical");
            }
        });
    }

    // set icon and target on links to external sites (if they contain http)
    function setExtLinks() {
        $('.post a[href^=http]').addClass('external');
        $('.post a[href^=http]').attr('target', '_blank');
    }

    // start vert img check after images have loaded
    $(window).on("load", setVertImg );
    
    // enable search 
    $(document).ready(function() {
        startSearch();
        setExtLinks();
    });