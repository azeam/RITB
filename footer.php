<footer>
    <p>ReInventing The Blog | Dennis Hägg <?php echo date("Y"); ?> | <a href="https://github.com/azeam/RITB" target="_blank">GitHub</a> | 
        <?php 
        // if on admin/login page link to mainpage, otherwise link to admin page 
        // (which will send to login page if not logged in, better than linking to login if session is active)
        if (strpos($_SERVER['REQUEST_URI'], "admin") !== false){
            echo "<a href='../'>Main page</a>";
        }
        else {
            echo "<a href='admin/admin.php'>Log in</a>";
        }
        ?>
    </p>
</footer>