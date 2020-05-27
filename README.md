# RITB
ReInventing The Blog - A blog CMS that does nothing new

RIBT stands for ReInventing The Blog, it is a blog CMS just like the plethora of other blog CMS' out there, except that it offers nothing new and most things it can do - it does worse than the rest. It is written from the ground up as part of a PHP/MySQL university class. For now it is not maintained and will not be actively improved upon, except for security issues reported. Feel free to register a user and write something, go hard with Kali or manual penetration tests (as long as you report any weaknesses to the github page or just use it for whatever you wish. You can find the demo page at [ritb.org](https://www.ritb.org).

Maybe some day I will have time to actually write something useful and turn this in to an active blog or improve the code. Until then it serves as a very simple base to be further worked upon when building a PHP backend. At the moment I'm working on a more advanced web shop/blog using this as a base. The editor has been re-worked to a WYSIWYG-editor + many other changes not included here. The code for that project is still private but will probably be open-sourced once it is in a more functional state.

In case someone wants to actually use this code the database structure can be imported from the file `admin/ritb_orgritb.sql` and you also need to create a `admin/access.php` file with the following content:  
`<?php
// db login
    define('DB_NAME','your-database-name');
    define('DB_USER','username-for-database');
    define('DB_PASS','password-for-database');
    define('DB_SERVER','url-to-server:3306'); 
?>`