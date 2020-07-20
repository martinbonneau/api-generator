<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
use \Firebase\JWT\JWT;

/**
 * Check the token authencity
 */
function validate_token($token)
{
    // required to decode jwt
    include_once 'config/core.php';
    include_once 'libs/php-jwt-master/src/BeforeValidException.php';
    include_once 'libs/php-jwt-master/src/ExpiredException.php';
    include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once 'libs/php-jwt-master/src/JWT.php';

    // if jwt is not empty
    if($token){
        try {
            
            // decode jwt
            $decoded = JWT::decode($token, $key, array('HS256'));


            /**
             * I adjusted the JWT to return the following string if 
             *      the token is expired to improve security
             *      with the Refresh Token (see in make_token() method)
             */
            if(gettype($decoded) === "array" && isset($decoded["Error"]) && $decoded["Error"] === 'Expired token') {
                http_response_code(401);
                echo json_encode(array("Error" => "Expired token"));
                exit;
            }


            /**
             * _ACTION_
             * 
             * This commented code allow a second token verification
             * It verify that the user who makes the request exist in DB
             * 
             * Without this code : if the user is deleted, he can always request the api
             * by using his token.
             * 
             * So the control is improved.
             */
            //check if user is in database
            include_once 'objects/user.php';
            $user = new User();
            $user->id = $decoded->sub;

            //if it's not null, it means that the user exist in DB with same id which is in payload
            //      I use the optionnal isset() to prevent a PHP:Notice in log file
            if($user->get()[0]["ID"])
            {
                //user exists
                return $user->id;
            }
            else { return false; }
            

            /**
             * _ACTION_
             * 
             * If you enable the previous commented code, delete the following return (or turn it to false)
             */
            //return true;
        }
        
        // if decode fails, it means jwt is invalid
        catch (Exception $e) { return $e->getMessage(); }
    }

    // show error message if jwt is empty
    else { return false; }
}






/**
 * This function will create a token
 */
function make_token($id) {

    include 'config/core.php';
    include_once 'libs/php-jwt-master/src/BeforeValidException.php';
    include_once 'libs/php-jwt-master/src/ExpiredException.php';
    include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once 'libs/php-jwt-master/src/JWT.php';


    /**
     * Prepare iat variable
     * 
     * The variable must be an integer
     */
    $now = new Datetime('now', new DateTimeZone("EUROPE/Paris"));
    $iat = $now->getTimestamp();


    /**
     * Prepare expiration time of the token
     * Default : 1 hours
     */
    $exp = $now->add(new DateInterval('PT1H'));
    $exp = $exp->getTimestamp();



    $token = array(
        /**
         * JWT variables
         * 
         * They are set in /api/config/core.php
         * and are optionnals
         * 
         * It's recommanded to use them
         */
        "iss" => $iss,
        "aud" => $aud,
        "iat" => $iat,
        "exp" => $exp,
        "sub" => $id,
        /**
         * _ACTION_ #encapsulates_datas
         * 
         * Here we encapsulate some datas in the payload of the token.
         * Don't put sensitives or critical informations in this array
         * 
         * Also check if variables are already declared by reading
         * docs in /api/config/core.php
         * 
         * Maybe you have to get the user's data before build this token :
         * 
         */
    );

    // generate jwt
    $jwt = JWT::encode($token, $key);




    //////////////////////////// Refresh Token generation ////////////////////////////

    /**
     * We give a uniq refresh token to user for a re-authentication without
     *    login / password.
     * 
     * This refresh token (RT) will be store in database and associated to
     *      the right user_id
     * 
     * The expiration of this token is by default : 30 days
     * 
     * Warning : the user_id must NOT be in the RT to prevent hack of it
     */

    //first : check if the user has already a RT in DB
    include_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    $query = "  SELECT id
                FROM REFRESH_TOKEN
                WHERE user = :userid";
    
    // prepare the query
    $stmt = $conn->prepare($query);
    
    //bind params
    $stmt->bindParam(':userid', $id);


    // execute the query, also check if query was successful
    if(!$stmt->execute()){
        return(array(
            "Error"=> "Can't login",
            "Code" => "204"
        ));
    }
    

    /**
     * We generate the RT here
     * 
     * The RT looks like :
     * TIME.UNIQID.ENTROPY
     * 
     * Adjust expiration time as you want
     */
    $now = new Datetime('now', new DateTimeZone("EUROPE/Paris"));

    $exp_rt = $now->add(new DateInterval('P30D'));
    $exp_rt = $exp_rt->format("YmdHis");
    $refresh_token = uniqid($exp_rt . ".", true);



    $num = $stmt->rowCount();


    if($num === 0) {
        //the user has no RT

        // insert query
        $query = "INSERT INTO REFRESH_TOKEN
                SET
                RT = :rt,
                EXPIRATION = :expiration,
                USER = :userid";
    }

    elseif ($num === 1) {
        //user has already a RT, just update fields with new token
        $query = "UPDATE REFRESH_TOKEN
                SET
                RT = :rt,
                EXPIRATION = :expiration
                WHERE user = :userid";
    }

    else { echo json_encode(array("Error" => "Can't login.", "Error Code" => "205")); exit; }

    // prepare the query
    $stmt = $conn->prepare($query);

    // bind the values
    $stmt->bindParam(':rt', $refresh_token);
    $stmt->bindParam(':expiration', $exp_rt);
    $stmt->bindParam(':userid', $id);

    // execute the query, also check if query was successful
    if($stmt->execute()){
        return(array(
            "jwt"=> $jwt,
            "rt" => $refresh_token
        ));
    }

    else { echo json_encode(array("Error" => "Can't login.", "Error Code" => "203")); exit; }

    
}


/**
 * This function confirm that the given refresh_token is present in db and
 *      that the user id stored in the jwt is associated with the RT
 */
function validate_refresh_token($jwt, $refresh_token) {
    // required to decode jwt
    include_once 'config/core.php';
    include_once 'libs/php-jwt-master/src/BeforeValidException.php';
    include_once 'libs/php-jwt-master/src/ExpiredException.php';
    include_once 'libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once 'libs/php-jwt-master/src/JWT.php';

    // if jwt is not empty
    if($refresh_token && $jwt){
        try {
            ///////////////////////////// PART 1 : is the RT valid ? /////////////////////////////

            //get the saved expiration time
            $exp = explode(".", $refresh_token)[0];

            //i split like this before because of max int value...
            $date = intval(substr($exp, 0, 8));
            $time = intval(substr($exp, 8, 6));


            //do same with datetime.now
            $now = new Datetime('now', new DateTimeZone("EUROPE/Paris"));
            $date_now = intval($now->format('Ymd'));
            $time_now = intval($now->format('His'));



            //compare dates
            if($date <= $date_now && $time <= $time_now) {
                //RT expired
                echo json_encode(array("message" => "Expired RT", "Code" => 208));
                exit;
            }

 
            ///////////////////////////// PART 2 : is the JWT valid ? /////////////////////////////
            // decode jwt
            $decoded = JWT::decode($jwt, $key, array('HS256'));


            //  is the given JWT valid
            if(gettype($decoded) !== "object" && !isset($decoded["Error"]) && validate_token($jwt)) {
                // the token is not expired, we don't need to use the RT
                echo "super";
                return True;                
            }

            //get user_id
            if(gettype($decoded) === "array" && isset($decoded["Error"]) && $decoded["Error"] === "Expired token")
            {
                $user_id = $decoded["jwt"]->sub;
            }
            else
            {
                $user_id = $decoded->sub;
            }




            ///////////////////////////// PART 3 : is the RT's user ? /////////////////////////////
            include_once './config/database.php';
            $database = new Database();
            $conn = $database->getConnection();
            
            //check in db if the RT match with the user_id
            //  if true --> retrieve the expiration of the RT
            $query = "  SELECT EXPIRATION, RT
                        FROM REFRESH_TOKEN
                        WHERE user = :user"; 
                        
            // prepare the query
            $stmt = $conn->prepare($query);

            // bind the values
            $stmt->bindParam(':user', $user_id);

            // execute the query, also check if query was successful
            if($stmt->execute()){
                $num = $stmt->rowCount();

                if($num === 1) {
                    $res = $stmt->fetch(PDO::FETCH_ASSOC);

                    
                    //check if both exp are same (should be)
                    if ($exp === $res["EXPIRATION"])
                    {
                        //refresh token is valid
                        return $user_id;
                    }

                    else
                    {
                        return false;
                    }
                    
                }

                else { echo json_encode(array("Error" => "Can't login.", "Error Code" => "206")); exit; }
            


            }
            else { echo json_encode(array("Error" => "Can't login.", "Error Code" => "207")); exit; }

        }
        
        // if decode fails, it means jwt is invalid
        catch (Exception $e) { return $e->getMessage(); }
    }

    // show error message if rt is empty
    else { return false; }
}

