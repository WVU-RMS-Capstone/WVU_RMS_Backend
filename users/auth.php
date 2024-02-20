<?php 
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';
    // if we are not requesting to this server
    
    
    if (strpos($_SERVER['REQUEST_URI'], '/users/auth.php') !== False) {
        $database = new database();
        $db = $database->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'login') {
            login();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'createaccount') {
            createAccount();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'middleware') {
            middlewareAuth($_GET['userid']);
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'logout') {
            logout($_GET['userid']);
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getUserInfo') {
            getUserInfo();
        }
        else{
            echo "Specified action not available.";
            http_response_code(201);
            exit();
        }
    }
    // example of a createaccount URL below 
    // EXAMPLE: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&name=grantiscool&password=22222222&firstname=Grant&lastname=Holzemer&middlename=Perry&type=P&playernumber=999999999&code=99999999&position=WR
    function createAccount(){
        $first_name = $_GET['firstName'];
        $last_name = $_GET['lastName'];
        $userUID = $_GET['UID'];
        $email = $_GET['email'];

        // init db connection
        $database = new database();
        $db = $database->getConnection();
        echo 'did we reach?';
        // Check if username exists
        $check = "SELECT UID FROM [dbo].[TestUsers] WHERE lastName = '$last_name'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );
        echo 'testing';

        if( $r !== NULL ){
            echo 'Username Already Exists.';
            echo json_encode("ID: $r[0]");
            http_response_code(409); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        // post new User to DB
        $sql = "INSERT INTO [dbo].[TestUsers] (FirstName, LastName, UID, Email) VALUES ('$first_name', '$last_name', '$userUID', '$email')";
        $stmt = sqlsrv_query($db, $sql);
        if($stmt === False){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), True));  
            echo json_encode(False);
            return False;
        }

        // Free resources
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($db);
        echo json_encode(True);
        echo 'testing';
        http_response_code(200);
        return True;
    }

    // get request to login evaluates the username and password credientials
    // returns session ID as cookie
    // EXAMPLE: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=login&name=grantiscool&password=22222222
    function login(){
        // new conect
        $database = new database();
        $db = $database->getConnection();

        $userUID = $_GET['UID'];
        $email = $_GET['email'];

        $tsql = "SELECT UID, firstName, lastName FROM [dbo].[TestUsers] WHERE email = '$email'";
        $stmt = sqlsrv_query($db, $tsql);
        if( $stmt === false ){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), true));  
        }
        
        if(!($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC ))){
            echo json_encode("Username does not exist. Create Account.");
            http_response_code(401); 
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($db);
            return False;
        }
        if ($userUID === $row[0] && $email === $row[3]){
            
            // Generate Session Token
            // Send session token into database
            // send session token as cookie back to user
            // Validate session token with user ID in database 
            // and make sure session token is not expired
            // if not expired then user authentification is good
            // if expired require user to re-login
            
            /* 
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: 0'); 
            */

            $userUID = $row[0];
            // $session_token = bin2hex(random_bytes(32));
            // if(middlewareAuth($UserID) === true){
            //     echo "Already active session.";
            //     http_response_code(200); 
            //     return True;
            // }

            // statement prep to add new active session token
            // $postTokenSQLQuery = "INSERT INTO [dbo].[Sessions] (UserID, SessionToken, ExpirationDateTime) VALUES ($UserID, '$session_token', DATEADD(HOUR, 8, GETDATE()));";
            // $statement = sqlsrv_query($db, $postTokenSQLQuery);
            // if( $statement === false ){  
            //     echo "Error in statement preparation/execution.\n";  
            //     exit( print_r( sqlsrv_errors(), true));  
            //     return False;
            // }

            // // Set cookie in http response header 8 hours (3600*8)
            // setcookie('session_token', $session_token, time() + 3600*8, '/', 'restapi-playerscompanion.azurewebsites.net', true, true);
            
            // return token as json (not needed just extra)
            // you should be able to get the cookie from the header of the response
            // echo json_encode($session_token);
            // http_response_code(200);    
            return $userUID;
        }else{
            echo json_encode("Invalid Credientials.");
            http_response_code(401); 
            return False;
        }
        
        // release resources and return trues pretty sure this will never execute
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($db);
        echo json_encode(True);
        http_response_code(200);    
        return True;
    }

    function logout($UserID){
        $db = new database();
        $conn = $db->getConnection();
        $query = "DELETE FROM [dbo].[Sessions] WHERE UserID = $UserID";
        $stmt = sqlsrv_query($conn, $query);
        if($stmt === False){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), true));  
            return false;
        }
        echo json_encode("User logged out successfully.");
        return true;
    }

    // before any request/post is requred we run the middleware auth to evaluate
    // session cookie token is active
    // UserID is validated with session cookie database and if session cookie is not
    // expired then allow user
    // returns true or false
    function middlewareAuth($UserID){
        $database = new database();
        $db = $database->getConnection();
        deleteExpiredSessions();
        $sql = "SELECT SessionToken FROM [dbo].[Sessions] WHERE UserID = '$UserID'";
        $stmt = sqlsrv_query($db, $sql);
        if( $stmt === false ){  
            // echo json_encode(false);
            return false;
        }
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC );
        if($row !== NULL){
            // echo json_encode(true);
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($db);
            return true;
        }else{
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($db);
            // echo json_encode(false);
            return false;
        }
    }

    // if the user has a expired session prompt them to relogin. This just deletes all inactive tokens from db
    function deleteExpiredSessions(){
        $db = new database();
        $conn = $db->getConnection();

        $query = "DELETE FROM [dbo].[Sessions] WHERE ExpirationDateTime < GETDATE()";
        $stmt = sqlsrv_query($conn, $query);
        if($stmt === False){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), true));  
            return false;
        }
        return true;
    }

?>

