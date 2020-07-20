<?php

// required headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// use JWT Namespace
use \Firebase\JWT\JWT;


// get posted data
$data = json_decode(file_get_contents("php://input"));


/**
 * _ACTION_ #login_fields
 * 
 * Here you have to replace variables names if your login JSON message does not look like following
 * 
 * {
 *      "mail" : "john@example.com"
 *      "password" : "clear_password"
 * }
*/
//sanitize datas
$mail = trim($data->mail); #replace mail by the sended variable in the posted JSON message
$mail = htmlspecialchars(strip_tags($mail));

$password = trim($data->password); #replace password by the sended variable in the posted JSON message 
$password = htmlspecialchars(strip_tags($password));

//////////////// check if email exists ////////////////
/**
 * _ACTION_ #login_fields
 * 
 * Here there's a template of query,
 * Change it if you need
 */
$query = "  SELECT id, password # do you want to store more information in token. add them here and bellow
            FROM USERS  #maybe you have to change this
            WHERE mail = \"$mail\" #and/or the where clause (check if your attribut is called email or mail or other)
            ";


//import database configuration
include_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

//send query
$stmt = $conn->prepare( $query );
$stmt->execute();


// get number of rows
$num = $stmt->rowCount();


if($num === 1) {
    // get record details / values
    $row = $stmt->fetch(PDO::FETCH_ASSOC);



    // retrieve informations to include in the payload
    // According to the 
    // examples :
    //$firstname = $row['firstname']
    $id = $row['id'];
    $hashed_password = $row['password'];


    if(password_verify($password, $hashed_password))
    {
        include_once 'validate_token.php';

        //get both JWT and refresh token
        $tokens = make_token($id);
        
        // set response code
        http_response_code(200);
        echo json_encode(
                array(
                    "message" => "Successful login.",
                    "jwt" => $tokens["jwt"],
                    "rt" => $tokens["rt"]
                )
            );
    }
    
    else
    {
        //wrong password
        http_response_code(401);
        echo json_encode(array("message" => "Login failed.")); // you can custom it to "Wrong password" if you want
    }
}

elseif ($num === 0)
{
    http_response_code(401);
    echo json_encode(array("message" => "The account does not exists."));
}

else
{
    http_response_code(401);
    echo json_encode(array("message" => "Login failed."));
}
///////////////////////////////////////////////////////
