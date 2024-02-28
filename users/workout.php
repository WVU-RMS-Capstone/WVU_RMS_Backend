<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';

    if (strpos($_SERVER['REQUEST_URI'], '/users/workout.php') !== False) {
        $database = new database();
        $db = $database->getConnection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'createexercise') {
            createExercise();
        }
        else{
            echo "Specified action not available.";
            http_response_code(201);
            exit();
        }
    }
    /*
    Description: 

    Return: True stating the workout was added

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
        $reps = $_GET['Gets'];
        $body_part = $_GET['BodyPart'];

        $check = "SELECT exerciseID FROM [dbo].[Exercises] WHERE Name = '$name'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );
        echo 'reaching';
        if( $r !== NULL ){
            echo 'Workout Already Exists.';
            echo json_encode("ID: $r[0]");
            http_response_code(409); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        $sql = "INSERT INTO [dbo].[Exercises] (Video, Cover, Name, Description, Sets, Reps, BodyParts) VALUES ('$video', '$cover', '$name', '$description', '$sets', '$reps', '$body_part')";
        $stmt = sqlsrv_query($db, $sql);
        if($stmt === False){  
            echo "Error in statement preparation/execution.\n";  
            exit( print_r( sqlsrv_errors(), True));  
            echo json_encode(False);
            return False;
        }
        echo json_encode(True);
        return true;
    }
?>