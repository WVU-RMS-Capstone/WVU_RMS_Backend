<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';

    if (strpos($_SERVER['REQUEST_URI'], '/users/programs.php') !== False) {
        $database = new database();
        $db = $database->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'createexercise') {
            createExercise();
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetchpremadeprograms')
        else {
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
    function fetchPremadePrograms() {
        $database = new database();
        $db = $database->getConnection();
    }
    
    /*
    Description: 

    Return: True stating the exercise was added (200), False if something went wrong (500), or the ID of a 
    duplicate exercise which already exists (409) 

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createxercise&Video=https://words.com&Cover=img.img&Name=rdl&Description=one-legged-deadlifts&Sets=3&Reps=10&BodyPart=knee
    */
    function createExercise() {
        $database = new database();
        $db = $database->getConnection();

        // id is auto-incremented
        // $workout_id = $_GET['workoutID'];
        $video = $_GET['Video'];
        $cover = $_GET['Cover'];
        $name = $_GET['Name'];
        $description = $_GET['Description'];
        $sets = $_GET['Sets'];
        $reps = $_GET['Reps'];
        $body_part = $_GET['BodyPart'];

        $check = "SELECT exerciseID FROM [dbo].[Exercises] WHERE Name = '$name'";
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

        $sql = "INSERT INTO [dbo].[Exercises] (Video, Cover, Name, Description, Sets, Reps, BodyPart) VALUES ('$video', '$cover', '$name', '$description', '$sets', '$reps', '$body_part')";
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
?>