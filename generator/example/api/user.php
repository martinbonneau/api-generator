<?php

/*
This file accept requests, check the token validity and then launch the right method of the object
*/

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, UPDATE, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//before anything : check the token
include_once 'validate_token.php';

// get posted data
$data = json_decode(file_get_contents("php://input"));

// get jwt if present
$jwt=isset($data->jwt) ? $data->jwt : "";

//check the token
$is_token_valid = validate_token($jwt);


if ( $is_token_valid )
{
    //token is ok, user can manipulate datas

    include_once './objects/user.php';
	$user = new User();


	//check if requested url look like /user or /user/id 
	if(isset($_GET["id"])) { // requested url look like /user/id
		//convert 	/0 		-> all
		//			/0000 	-> all
		//			/00001 	-> 1
		$user->id = intval($_GET["id"]) <= 0 ? "all" : intval($_GET["id"]);
	}
	else { // requested url look like /user
		$user->id = "all";
	}


	//check user's rights
	include_once './objects/user.php';
	$user_from_db = new User();
	$user_from_db->id = $is_token_valid; //contains user id of the JWT
	$user_from_db->type = $user_from_db->get()[0]["TYPE"]; //return "admin", "user", etc...

	switch($_SERVER["REQUEST_METHOD"])
	{

		// keep in mind that, at this point, only authentified users
		//	can manipulates data



		case 'GET':
			$user = $user->get();
			if($user) {
				//success
				echo json_encode($user);
			}
			break;







		case 'POST': //create new user


			// #authorisation
			if($user_from_db->type !== "admin"){
				echo json_encode(array(	"Error" => "Can't create object",
										"Code" => 403));
				exit;
			}


			//we cant create a user with a targeted id
			if($user->id !== "all") { 
				echo json_encode(array(	"Error" => "Bad request",
										"Code" => "101"));
				exit;
			}

			//check if all variables of the object are present
			if (
				isset($data->login) &&
				isset($data->password) &&
				isset($data->type) &&
				isset($data->name)
			   )
			{
				// sanitize
				$user->login=htmlspecialchars(strip_tags($data->login));
				$user->password=htmlspecialchars(strip_tags($data->password));
				$user->type=htmlspecialchars(strip_tags($data->type));
				$user->name=htmlspecialchars(strip_tags($data->name));



				//hash password
				$user->password = password_hash($user->password, PASSWORD_BCRYPT);

				//execute the creation
				if($user->post())
				{
					//successfully created a user
					//clear password before send result
					unset($user->password);
					echo json_encode($user);
				}
				else
				{
					//failed
					echo json_encode(array("Error" => "Can't create object", "Code" => "102"));
				}
			}
			else
			{
				//failed
				echo json_encode(array("Error" => "Can't create object", "Code" => "103"));
			}
			
			break;




			
		case 'PUT':

			//can't update object if id is not specified
			if($user->id === "all") { 
				echo json_encode(array(	"Error" => "Bad request",
										"Code" => "101"));
				exit;
			}

			/**
			 * _ACTION_ #update
			 * 
			 * If you don't want that all field are needed to update objects,
			 * adjust :
			 * 		- issets check (directly bellow),
			 * 		- the sanitize section
			 * 		- the hash password section (the code is not generate if you don't have the "password" field)
			 * 		- the method must be changed to 'PATCH' and not PUT
			 * 			\--> you don't understand why ? Search on google "PUT or PATCH"
			 */
			if (
				isset($data->login) &&
				isset($data->password) &&
				isset($data->type) &&
				isset($data->name)
			   )
			{
				//#authorisation
				// set the user id to the requester id (id found in JWT)
				// with this, user can update only itself
				$user->id = $user_from_db->id;

				// sanitize
				$user->login=htmlspecialchars(strip_tags($data->login));
				$user->password=htmlspecialchars(strip_tags($data->password));
				$user->type=htmlspecialchars(strip_tags($data->type));
				$user->name=htmlspecialchars(strip_tags($data->name));


				//hash password
				$user->password = password_hash($user->password, PASSWORD_BCRYPT);
				//execute the update of the user
				if($user->put())
				{
					//successfully updated
					//clear password before send result
					unset($user->password);

					/**
					 * _ACTION_ #encapsulates_datas
					 * 
					 * If your object is used to login and you store datas in JWT,
					 * you have to generate a new token
					 * 
					 * example :
					 * include_once 'validate_token.php';
					 * make_token($user->id);
					 */
					echo json_encode($user);
				}
				else
				{
					//failed
					echo json_encode(array("Error" => "Can't create object", "Code" => "202"));
				}
			}
			else
			{
				//failed
				echo json_encode(array("Error" => "Can't create object", "Code" => "103"));
			}
			break;
				






		case 'DELETE':
			if($user->id === "all") { 
				echo json_encode(array(	"Error" => "Bad request",
										"Code" => "101"));
				exit;
			}
			
			else {

				//#authorisation
				// set the user id to the requester id (id found in JWT)
				// with this, user can delete only itself
				$user->id = $user_from_db->id;

				//delete the object
				if($user->delete()) {
					//successfully deleted
					echo json_encode(array(	"message" => "Successfully deleted",
											"id" => $user->id));
				}
				
				else
				{
					//error
					echo json_encode(array("message" => "Item not deleted", "Error" => "201"));
				}
			}
			break;




		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			echo json_encode(array("Error" => "Method Not Allowed"));
			break;
	}
	
	//memory release
	unset($user);
}

elseif ( $is_token_valid === False )
{
    //token verification failed
    echo json_encode(array("message" => "Access denied."));
    exit;
}

else
{
	//error occured
	echo json_encode(array("Error" => "$is_token_valid"));
	exit;
}
