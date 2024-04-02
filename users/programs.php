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
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetchpremadeprograms') {
            fetchPremadePrograms();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'createprogram') {
            createProgram();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetchallexercises') {
            fetchAllExercises();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'fetchexercise') {
            fetchExercise();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'addprogramexercises') {
            addProgramExercises();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'addathleteprograms') {
            addAthletePrograms();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getathleteprogram') {
            getAthleteProgram();
        } 
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
    function fetchPremadePrograms()
    {
        $database = new database();
        $db = $database->getConnection();

        $tsql = "SELECT ProgramID, ProgramName, Cover FROM [dbo].[Programs]";
        $stmt = sqlsrv_query($db, $tsql);
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
            $rows = "No programs have been added yet.";
        }

        echo json_encode($rows);
        http_response_code(200);
    }

    /*
    Description:

    Return: Full details of the exercise given by the exerciseID url parameter
    */
    function fetchExercise() {
        $database = new database();
        $db = $database->getConnection();

        $exerciseID = $_GET['exerciseID'];
        
        $check = "SELECT * FROM [dbo].[Exercises] WHERE exerciseID = '$exerciseID'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC );
        if( $r !== NULL ){
            echo json_encode($r);
            http_response_code(200); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return True;
        }
        
        // The requested exercise doesn't exist
        echo "The requested exercise doesn't exist.";
        http_response_code(409);
        sqlsrv_free_stmt($res);
        sqlsrv_close($db);
        return False;
    }

    /*
        Description: 

        Return: ID, name, and body part of all exercises in the database

        Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=fetchallexercises
        */
    function fetchAllExercises()
    {
        $database = new database();
        $db = $database->getConnection();

        $tsql = "SELECT exerciseID, Name, BodyPart FROM [dbo].[Exercises]";
        $stmt = sqlsrv_query($db, $tsql);
        if ($stmt === false) {
            echo "Something went wrong fetching the exercises";
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
            $rows = "No exercises have been added yet.";
        }
        echo json_encode($rows);
        http_response_code(200);
    }

    /*
        Description: 

        Return: True stating the exercise was added (200), False if something went wrong (500), or the ID of a 
        duplicate exercise which already exists (409) 

        Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createxercise&Video=https://words.com&Cover=img.img&Name=rdl&Description=one-legged-deadlifts&Sets=3&Reps=10&BodyPart=knee
        */
    function createExercise()
    {
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
        $body_part = strtoupper($_GET['BodyPart']);

        $check = "SELECT exerciseID FROM [dbo].[Exercises] WHERE Name = '$name'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array($res, SQLSRV_FETCH_NUMERIC);
        if ($r !== NULL) {
            // echo 'Exercise Already Exists.';
            echo json_encode($r[0]);
            http_response_code(409);
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        $sql = "INSERT INTO [dbo].[Exercises] (Video, Cover, Name, Description, Sets, Reps, BodyPart) VALUES ('$video', '$cover', '$name', '$description', '$sets', '$reps', '$body_part')";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === False) {
            // echo "Error in statement preparation/execution.\n";  
            exit(print_r(sqlsrv_errors(), True));
            echo json_encode(False);
            http_response_code(500);
            return False;
        }
        echo json_encode(True);
        return true;
    }

    /*
        Description: NOT FINISHED (need to figure out how to pull from exercise table to then store as comma seperated list inside program table)

        Return: True stating the exercise was added (200), False if something went wrong (500), or the ID of a 
        duplicate exercise which already exists (409) 

        Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createxercise&Video=https://words.com&Cover=img.img&Name=rdl&Description=one-legged-deadlifts&Sets=3&Reps=10&BodyPart=knee
        */
    function createProgram()
    {
        $database = new database();
        $db = $database->getConnection();

        // id is auto-incremented
        // $workout_id = $_GET['workoutID'];
        $cover = $_GET['Cover'];
        $program_name = $_GET['ProgramName'];

        $check = "SELECT programID FROM [dbo].[Programs] WHERE ProgramName = '$program_name'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array($res, SQLSRV_FETCH_NUMERIC);
        if ($r !== NULL) {
            // echo 'Exercise Already Exists.';
            echo json_encode($r[0]);
            http_response_code(409);
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return False;
        }

        $sql = "INSERT INTO [dbo].[Programs] (Cover, ProgramName) VALUES ('$cover', '$program_name')";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === False) {
            // echo "Error in statement preparation/execution.\n";  
            exit(print_r(sqlsrv_errors(), True));
            echo json_encode(False);
            http_response_code(500);
            return False;
        }
        echo json_encode(True);
        return true;
    }

    /*
        Description:

        Return: 

        Example: 
        */
    function addProgramExercises()
    {
        $database = new database();
        $db = $database->getConnection();

        // id is auto-incremented
        $workout_1 = $_GET['Workout1'];
        $workout_2 = $_GET['Workout2'];
        $workout_3 = $_GET['Workout3'];
        $workout_4 = $_GET['Workout4'];
        $workout_5 = $_GET['Workout5'];
        $workout_6 = $_GET['Workout6'];
        $workout_7 = $_GET['Workout7'];
        $workout_8 = $_GET['Workout8'];
        $workout_9 = $_GET['Workout9'];
        $workout_10 = $_GET['Workout10'];

        $sql = "INSERT INTO [dbo].[Program_Exercises] (Workout_1, Workout_2, Workout_3, Workout_4, Workout_5, Workout_6, Workout_7, Workout_8, Workout_9, Workout_10) VALUES ('$workout_1', '$workout_2', '$workout_4', '$workout_4', '$workout_5', '$workout_6', '$workout_7', '$workout_8', '$workout_9', '$workout_10')";
        $stmt = sqlsrv_query($db, $sql);
        if ($stmt === False) {
            // echo "Error in statement preparation/execution.\n";  
            exit(print_r(sqlsrv_errors(), True));
            echo json_encode(False);
            http_response_code(500);
            return False;
        }
        echo json_encode(True);
        return true;
    }

     /*
        Description: 

        Return: 
        Example: 
        */
    function addAthletePrograms()
    {
        $database = new database();
        $db = $database->getConnection();
    
        // id is auto-incremented
        $ProgramID = $_GET['ProgramID'];
        $AthleteUID = $_GET['AthleteUID'];
        
        // Check if username exists
        $check = "SELECT ID FROM [dbo].[Assigned_Programs] WHERE AthleteUID = '$AthleteUID'";
        $res = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_NUMERIC );
        
        if( $r !== NULL ){
            // UPDATE TABLE
            $sql = "UPDATE [dbo].[Assigned_Programs] SET ProgramID = '$ProgramID' WHERE AthleteUID = '$AthleteUID'";
            $stmt = sqlsrv_query($db, $sql);
            if ($stmt === False) {
                // echo "Error in statement preparation/execution.\n";  
                // exit(print_r(sqlsrv_errors(), True));
                echo json_encode(False);
                http_response_code(500);
                return False;
            }
            echo json_encode(True);
            http_response_code(200);
            return true;
        } else {
            $sql = "INSERT INTO [dbo].[Assigned_Programs] (ProgramID, AthleteUID) VALUES ('$ProgramID', '$AthleteUID')";
            $stmt = sqlsrv_query($db, $sql);
            
            if ($stmt === False) {
                // echo "Error in statement preparation/execution.\n";  
                // exit(print_r(sqlsrv_errors(), True));
                echo json_encode(False);
                http_response_code(500);
                return False;
            }
            echo json_encode(True);
            http_response_code(200);
            return true;
        }
    }

     /*
        Description: 

        Return: 

        Example: 
    */
    function getAthleteProgram()
    {
        $database = new database();
        $db = $database->getConnection();
        
        $AthleteUID = $_GET['AthleteUID'];

        $check = "SELECT AthleteUID, ProgramID, ID FROM [dbo].[Assigned_Programs] WHERE AthleteUID = '$AthleteUID'";
        $stmt = sqlsrv_query($db, $check);
        if ($stmt === false) {
            echo "Something went wrong fetching the exercises";
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
            $rows = "Not Assigned Program";
        }
        echo json_encode($rows);
        http_response_code(200);
            
    }
?>
