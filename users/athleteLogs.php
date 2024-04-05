<?php 
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';    
    
    if (strpos($_SERVER['REQUEST_URI'], '/users/athleteLogs.php') !== False) {
        $database = new database();
        $db = $database->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'addnotes') {
            AddNotes();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getroster') {
            getRoster();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getdailylogs') {
            getDailyLogs();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getnotes') {
            getNotes();
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
        $writtenBy = $_GET['MadeBy'];
        $athlete = $_GET['Athlete'];

        $sql = "INSERT INTO [dbo].[Notes] (Date, Note, MadeBy, Athlete) VALUES ('$date', '$note', '$writtenBy', '$athlete',)";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === False) {
            echo json_encode(False);
            http_response_code(500);
            return False;
        }
        echo json_encode(True);
        http_response_code(200);
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

        $check = "SELECT FirstName, LastName, UID, AthleteImage FROM [dbo].[Users] WHERE Role = 'Athlete'";
        $stmt = sqlsrv_query($db, $check);
        if ($stmt === false) {
            echo "Something went wrong fetching the programs";
            http_response_code(500);
            exit(print_r(sqlsrv_errors(), true));
        }

        $rows = array();
        $i = 0;

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $i++;
            $rows[] = array('data' => $row);
        }
        if ($i == 0) {
            $rows = "No athletes have been added yet.";
        }

        echo json_encode($rows);
        http_response_code(200);
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

    function getNotes() {
        $database = new database();
        $db = $database->getConnection();

        $athlete = $_GET['Athlete'];

        $sql = "SELECT Date, Note, MadeBy FROM [dbo].[Notes] WHERE Athlete = '$athlete'";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === false) {
            echo "Something went wrong fetching the notes";
            http_response_code(500);
            exit(print_r(sqlsrv_errors(), true));
        }

        $rows = array();
        $i = 0;

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $i++;
            $rows[] = array('data' => $row);
        }
        if ($i == 0) {
            $rows = "No notes have been added yet.";
        }

        echo json_encode($rows);
        http_response_code(200);
    }
?>
