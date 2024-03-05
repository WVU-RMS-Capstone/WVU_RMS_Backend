<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    include '../config.php';
    
    /*
    Description: 

    Return: True stating the exercise was added (200), False if something went wrong (500), or the ID of a 
    duplicate exercise which already exists (409) 

    Example: https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createxercise&Video=https://words.com&Cover=img.img&Name=rdl&Description=one-legged-deadlifts&Sets=3&Reps=10&BodyPart=knee
    */
    function selectItems(id, table, name, db) {
        
        $check = "SELECT $id FROM [dbo].[&table] WHERE Name = '$name'";
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

        $sql = "INSERT INTO [dbo].[Notes] (Date, Note, writtenBy, athlete) VALUES ('$date', '$note', '$writtenBy', '$athlete')";
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