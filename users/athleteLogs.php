<?php 
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';
    // if we are not requesting to this server
    
    
    if (strpos($_SERVER['REQUEST_URI'], '/users/athleteLogs.php') !== False) {
        $database = new database();
        $db = $database->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'addNotes') {
            AddNotes();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getroster') {
            getRoster();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getdailylogs') {
            getDailyLogs();
        }
        else{
            echo "Specified action not available.";
            http_response_code(201);
            exit();
        }
    }

    /*
    Description: 

    Return: Set of all premade programs

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=fetchpremadeprograms
    */
    function AddNotes() {
        $database = new database();
        $db = $database->getConnection();

        // id is auto-incremented
        $date = $_GET['Date'];
        $note = $_GET['Note'];
        $writtenBy = $_GET['writtenBy'];
        $athlete = $_GET['athlete'];

        $check = "SELECT noteID FROM [dbo].[Notes] WHERE athlete = '$athlete'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );
        if( $r !== NULL ){
            // echo 'Exercise Already Exists.';
            echo json_encode($r[0]);
            http_response_code(409); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        $sql = "INSERT INTO [dbo].[Notes] (Date, Note, writtenBy, athlete) VALUES ('$date', '$note', '$writtenBy', '$athlete',)";
        $stmt = sqlsrv_query($db, $sql);
        if($stmt === False){  
            // echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), True));  
            echo json_encode(False);
            http_response_code(500);
            return False;
        }
        echo json_encode(True);
        return true;
    }

    /*
    Description: 

    Return: Set of all premade programs

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=fetchpremadeprograms
    */
    function getRoster() {
        $database = new database();
        $db = $database->getConnection();

        // CAN WE PULL FROM USERS AND CHECK IF USER == AT then skip and add the rest?
        $check = "SELECT athletes FROM [dbo].[Roster]";
        $res = sqlsrv_query($db, $check);

        $row = array();
        while($r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC )){
            $row[] = array('data' => $r);
        }

        // free resources
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($db);

        // print roster to the site
        echo json_encode($row);
        http_response_code(200);  
            
        // return the roster
        return $row;
    }

     /*
    Description: 

    Return: Set of all premade programs

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=fetchpremadeprograms
    */
    function getDailyLogs() {
        $database = new database();
        $db = $database->getConnection();

        $check = "SELECT athletes, record FROM [dbo].[Attendance]";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );
        
        // May need to change if check
        if( $r !== NULL ){
            // echo 'Exercise Already Exists.';
            echo json_encode($r[0]);
            http_response_code(409); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        /*
        y is athlete attended
        n is athlete did not attend

        if record == 1
            then y++;
        else record == 0
            then n++;
        */
        // free resources
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($db);

        // print role to the site
        echo json_encode($athlete);
        http_response_code(200);  
            
        // return the role of the user
        return $athlete;
    }
?>