<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// use JWT Namespace
use \Firebase\JWT\JWT;

include_once  'validate_token.php';

// get posted data
$data = json_decode(file_get_contents("php://input"));

// get rt if present
$rt=isset($data->rt) ? $data->rt : "";
$jwt=isset($data->jwt) ? $data->jwt : "";


if($rt && $jwt) {
    //check the token
    
    // if authentication is ok, contains logged user_id
    $userid = validate_refresh_token($jwt, $rt);
}
else { echo json_encode(array("Error" => "Can't login", "Code" => 105));exit; }


if($userid) {

    //make new tokens
    $tokens = make_token($userid);
        
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
    http_response_code(401);
    echo json_encode(array("message" => "Invalid RT."));
}
///////////////////////////////////////////////////////
