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
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getprogramexercises') {
            getProgramExercises();
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'addathleteprograms') {
            addAthletePrograms();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getathleteprogram') {
            getAthleteProgram();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getprograminfo') {
            getProgramInfo();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'updateexercise') {
            updateExercise();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'deleteexercise') {
            deleteExercise();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'deleteprograms') {
            deletePrograms();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'updateprogram') {
            updatePrograms();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'updateprogramexercises') {
            updateProgramsExercises();
        } 
        else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'getprogramexercisenames') {
            getProgramExerciseNames();
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

        $tsql = "SELECT exerciseID, Name, BodyPart, cover, Description, Sets, Reps, video FROM [dbo].[Exercises]";
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
        $i = 0;
        
        for ($i = 1; $i <= 10; $i++) {
            echo strcmp($_GET["Workout$i"], '0');
            if (!strcmp($_GET["Workout$i"], '0')) break;
        }

        $sql = "INSERT INTO [dbo].[Program_Exercises] (Workout_1, Workout_2, Workout_3, Workout_4, Workout_5, Workout_6, Workout_7, Workout_8, Workout_9, Workout_10, Workout_Count) VALUES ('$workout_1', '$workout_2', '$workout_3', '$workout_4', '$workout_5', '$workout_6', '$workout_7', '$workout_8', '$workout_9', '$workout_10', '$i')";
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
        Description: Fetches the set of exercises that make up a given program

        Return: 
        */
        function getProgramExercises()
        {
            $database = new database();
            $db = $database->getConnection();
    
            $ProgramID = $_GET['ProgramID'];
    
            $sql = "SELECT Workout_1, Workout_2, Workout_3, Workout_4, Workout_5, Workout_6, Workout_7, Workout_8, Workout_9, Workout_10 FROM [dbo].[Program_Exercises] WHERE Program_ID = '$ProgramID'";
            $res = sqlsrv_query($db, $sql);
            $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC );
            if ($r === NULL) {
                echo json_encode(False);
                http_response_code(409);
                return False;
            }

            echo json_encode($r);
            http_response_code(200);
            return True;
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

        $check = "SELECT ap.AthleteUID, ap.ProgramID, p.ProgramID, p.ProgramName, p.Cover FROM [dbo].[Assigned_Programs] AS ap INNER JOIN [dbo].[Programs] AS p ON ap.ProgramID = p.ProgramID WHERE ap.AthleteUID = '$AthleteUID'";
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

     /*
        Description: 

        Return: 

        Example: 
    */
    function getProgramInfo()
    {
        $database = new database();
        $db = $database->getConnection();
        
        $ProgramID = $_GET['ProgramID'];

        $check = "SELECT p.ProgramName, p.Cover, pe.* FROM [dbo].[Programs] p INNER JOIN [dbo].[Program_Exercises] pe ON p.ProgramID = pe.Program_ID WHERE p.ProgramID = '$ProgramID'";

        $stmt = sqlsrv_query($db, $check);
        $r = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC );
        if( $r !== NULL ){
            echo json_encode($r);
            http_response_code(200); 
            sqlsrv_free_stmt($res);
            sqlsrv_close($db);
            return True;
        }
        
        // The requested exercise doesn't exist
        echo "The requested program doesn't exist.";
        http_response_code(409);
        sqlsrv_free_stmt($res);
        sqlsrv_close($db);
        return False;
            
    }

    /*
        Description: 

        Return: 

        Example: 
    */
    function deleteExercise()
    {
        $database = new database();
        $db = $database->getConnection();
        
        $exerciseID = $_GET['exerciseID'];

        $check = "DELETE FROM [dbo].[Exercises] WHERE exerciseID = '$exerciseID'";
        $stmt = sqlsrv_query($db, $check);
        if ($stmt === False) {
            // echo "Error in statement preparation/execution.\n";  
            // exit(print_r(sqlsrv_errors(), True));
            echo json_encode(False);
            http_response_code(500);
            exit(print_r(sqlsrv_errors(), true));
        }
        echo json_encode(True);
        http_response_code(200);
            
    }

     /*
        Description: 

        Return: 

        Example: 
    */
    function deletePrograms()
    {
        $database = new database();
        $db = $database->getConnection();
        
        $programID = $_GET['programID'];

        $check = "DELETE FROM [dbo].[Programs] WHERE ProgramID = '$programID'";
        $stmt = sqlsrv_query($db, $check);
        if ($stmt === False) {
            // echo "Error in statement preparation/execution.\n";  
            // exit(print_r(sqlsrv_errors(), True));
            echo json_encode(False);
            http_response_code(500);
            exit(print_r(sqlsrv_errors(), true));
        } else {
            $deleteExercises = "DELETE FROM [dbo].[Program_Exercises] WHERE Program_ID = '$programID'";
            $st = sqlsrv_query($db, $deleteExercises);
            if ($st === False) {
                // echo "Error in statement preparation/execution.\n";  
                // exit(print_r(sqlsrv_errors(), True));
                echo json_encode(False);
                http_response_code(500);
                exit(print_r(sqlsrv_errors(), true));
            }
        }
        echo json_encode(True);
        http_response_code(200);
            
    }

    /*
    Description: 

    Return: 
    Example: 
    */
    function updateExercise()
    {
        $database = new database();
        $db = $database->getConnection();
        
        // id is auto-incremented
        $id = $_GET['exerciseID'];
        $video = $_GET['Video'];
        $cover = $_GET['Cover'];
        $name = $_GET['Name'];
        $description = $_GET['Description'];
        $sets = $_GET['Sets'];
        $reps = $_GET['Reps'];
        $body_part = strtoupper($_GET['BodyPart']);
            
        $sql = "UPDATE [dbo].[Exercises] SET video = '$video', cover = '$cover', Name = '$name', Description = '$description', Sets = '$sets', Reps = '$reps', BodyPart = '$body_part' WHERE exerciseID = '$id'";
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

    /*
    Description: 

    Return: 
    Example: 
    */
    function updatePrograms()
    {
        $database = new database();
        $db = $database->getConnection();
        
        // id is auto-incremented
        $id = $_GET['ProgramID'];
        $cover = $_GET['Cover'];
        $name = $_GET['ProgramName'];
            
        $sql = "UPDATE [dbo].[Programs] SET ProgramName = '$name', Cover = '$cover' WHERE ProgramID = '$id'";
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

    /*
    Description: 

    Return: 
    Example: 
    */
    function updateProgramsExercises()
    {
        $database = new database();
        $db = $database->getConnection();
        
        $programID = $_GET['ProgramID'];
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

        $sql = "UPDATE [dbo].[Program_Exercises] SET Workout_1 = '$workout_1', Workout_2 = '$workout_2', Workout_3 = '$workout_3', Workout_4 = '$workout_4', Workout_5 = '$workout_5', Workout_6 = '$workout_6', Workout_7 = '$workout_7', Workout_8 = '$workout_8', Workout_9 = '$workout_9', Workout_10 = '$workout_10' WHERE Program_ID = '$programID'";
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
        Description: Fetches the set of exercises that make up a given program

        Return: 
    */
    function getProgramExerciseNames()
    {
        $database = new database();
        $db = $database->getConnection();
    
        $ProgramID = $_GET['ProgramID'];
    
        $sql = "SELECT e1.Name AS workout1_name,
                        e2.Name AS workout2_name,
                        e3.Name AS workout3_name,
                        e4.Name AS workout4_name,
                        e5.Name AS workout5_name,
                        e6.Name AS workout6_name,
                        e7.Name AS workout7_name,
                        e8.Name AS workout8_name,
                        e9.Name AS workout9_name,
                        e10.Name AS workout10_name
                FROM [dbo].[Program_Exercises] pe
                LEFT JOIN [dbo].[Exercises] e1 ON pe.Workout_1 = e1.exerciseID
                LEFT JOIN [dbo].[Exercises] e2 ON pe.Workout_2 = e2.exerciseID
                LEFT JOIN [dbo].[Exercises] e3 ON pe.Workout_3 = e3.exerciseID
                LEFT JOIN [dbo].[Exercises] e4 ON pe.Workout_4 = e4.exerciseID
                LEFT JOIN [dbo].[Exercises] e5 ON pe.Workout_5 = e5.exerciseID
                LEFT JOIN [dbo].[Exercises] e6 ON pe.Workout_6 = e6.exerciseID
                LEFT JOIN [dbo].[Exercises] e7 ON pe.Workout_7 = e7.exerciseID
                LEFT JOIN [dbo].[Exercises] e8 ON pe.Workout_8 = e8.exerciseID
                LEFT JOIN [dbo].[Exercises] e9 ON pe.Workout_9 = e9.exerciseID
                LEFT JOIN [dbo].[Exercises] e10 ON pe.Workout_10 = e10.exerciseID
                WHERE pe.Program_ID = '$ProgramID'";

        $res = sqlsrv_query($db, $sql);
        $r = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC );
        if ($r === NULL) {
            echo json_encode(False);
            http_response_code(409);
            return False;
        }

        echo json_encode($r);
        http_response_code(200);
        return True;
    }
?>
