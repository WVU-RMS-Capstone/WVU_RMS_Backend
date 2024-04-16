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
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getuserinfo') {
            getUserInfo();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'updateuser') {
            updateUser();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'getuserimage') {
            getUserImage();
        }
        else{
            echo "Specified action not available.";
            http_response_code(201);
            exit();
        }
    }

    /*
    Description: Create Account method will open a new database connections, then pull the first name,
    last name, UID, email and role from the url that is sent. The variables from the url are then stored
    inside of constant variables so that the information can be checked and stored inside of the table Users. 

    Return: True, confirm the account was stored inside the table

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@&role=Athlete
    */   
    function createAccount(){
        $first_name = $_GET['firstName'];
        $last_name = $_GET['lastName'];
        $userUID = $_GET['UID'];
        $email = $_GET['email'];
        $role = $_GET['role'];

        // init db connection
        $database = new database();
        $db = $database->getConnection();
        
        // Check if username exists
        $check = "SELECT UID FROM [dbo].[Users] WHERE lastName = '$last_name'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );

        if( $r !== NULL ){
            echo 'Username Already Exists.';
            echo json_encode("ID: $r[0]");
            http_response_code(409); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        // post new User to DB
        $sql = "INSERT INTO [dbo].[Users] (FirstName, LastName, UID, Email, Role) VALUES ('$first_name', '$last_name', '$userUID', '$email', '$role')";
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
        http_response_code(200);
        return True;
    }

    /*
    Description: Login method will open a new database connections, then pull the email and UID from the
    url that is sent. The variables UID and email are stored in constants used for the select statement
    that will pull all information from the table Users. 

    Return: Users role either Athlete or Trainer

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=login&UID=0000000000000000000000000000&email=test@
    */
    function login(){
        // new conect
        $database = new database();
        $db = $database->getConnection();

        $userUID = $_GET['UID'];
        $email = $_GET['email'];

        $tsql = "SELECT UID, firstName, lastName, Email, Role FROM [dbo].[Users] WHERE UID = '$userUID'";
        $stmt = sqlsrv_query($db, $tsql);
        if( $stmt === false ){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), true));  
        }
        
        // Check to see if the user is stored and created within the database
        if(!($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_NUMERIC ))){
            echo json_encode("Username does not exist. Create Account.");
            http_response_code(401); 
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($db);
            return False;
        }
        
        // If the UID and the email match the row from the select statement
            // Get the row that contains the role of the user and store it into a variable
            // return the role of that user
        if ($userUID === $row[0] && $email === $row[3]){
            // store role variable
            $role = $row[4];
            
            // free resources
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($db);

            // print role to the site
            echo json_encode($role);
            http_response_code(200);  
            
            // return the role of the user
            return $role;
        }else{
            echo json_encode("Invalid Credientials.");
            http_response_code(401); 
            return False;
        }
    }

     /*
    Description: 

    Return: 

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=login&UID=0000000000000000000000000000&email=test@
    */
    function getUserInfo(){
        $database = new database();
        $db = $database->getConnection();

        $userUID = $_GET['UID'];

        $sql = "SELECT firstName, lastName, Email, Role, AthleteImage FROM [dbo].[Users] WHERE UID = '$userUID'";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === False) {
            echo json_encode(False);
            http_response_code(500);
            return False;
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $imageName = $userUID . '.jpg';

        // Get the image file
        $imagePath =  "/home/site/images/profile_images/$imageName";
        $image = file_get_contents($imagePath);

        // Encode the image as a base64 string
        $base64Image = 'data:image/jpeg;base64,' . base64_encode($image);
        
        // Add the base64 image to the row
        $row['AthleteImage'] = $base64Image;

        $rows[] = array('data' => $row);
        echo json_encode($rows);
        http_response_code(200);
        return true;
    }

     /*
    Description: 

    Return: 

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=login&UID=0000000000000000000000000000&email=test@
    */
    function updateUser(){
        $database = new database();
        $db = $database->getConnection();

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $first_name = $data['firstName'];
        $last_name = $data['lastName'];
        $userUID = $data['UID'];
        $email = $data['email'];
        $base64Image = $data['image'];

        // Remove the 'data:image/jpeg;base64,' part
        $base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);

        // Decode the base64 string
        $image = base64_decode($base64Image);

        // Generate a unique name for the image
        $imageName = $userUID . '.jpg';

        // Save the image to a directory on your server
        file_put_contents("/home/site/images/profile_images/$imageName", $image);

        $sql = "UPDATE [dbo].[Users] SET firstName = '$first_name', lastName = '$last_name', Email = '$email', AthleteImage = '$imageName' WHERE UID = '$userUID'";
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
    
    function getUserImage() {
        $database = new database();
        $db = $database->getConnection();

        $userUID = $_GET['UID'];

        $imageName = $userUID . '.jpg';

        // Get the image file
        $imagePath =  "/home/site/images/profile_images/$imageName";
        $image = file_get_contents($imagePath);

        // Encode the image as a base64 string
        $base64Image = 'data:image/jpeg;base64,' . base64_encode($image);
        
        echo json_encode($base64Image);
        http_response_code(200);
        return true;
    }
?>

