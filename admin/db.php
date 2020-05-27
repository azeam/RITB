<?php
    // template from LTU

    require_once("access.php"); 
    
    function db_connect() {
        $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

        if(mysqli_connect_errno())
        {
            $msg = "Error: Connection failed: ";
            $msg .= mysqli_connect_error();
            $msg .= " (" . mysqli_connect_errno() . ")";
            exit($msg); 
        }
 
        // set utf8 encoding while communicating with db
        mysqli_set_charset ( $connection , 'utf8' );
        return $connection;
    }

    function db_disconnect($connection) {
        if(isset($connection))
            mysqli_close($connection);
    }

    // perform query
    function db_query($connection, $query) {
        $result = mysqli_query($connection, $query);
        if(!$result)
            echo '<br>Error: '.$query.'<br>' . db_error($connection) . '<br>';
        return $result;
    }

    // return query as array
    function db_select($connection, $query) {
        $rows = array();
        $result = db_query($connection, $query);
        if($result)
        {
            while ($row = mysqli_fetch_assoc($result))
            {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    // escape dynamic parameters
    function db_escape($connection, $str) {
        return mysqli_real_escape_string($connection, $str);
    }

    // get latest error
    function db_error($connection)  {
        if(isset($connection))
            return mysqli_error($connection);
        return mysqli_connect_error();
    }

?>