<?php

/*
This file accept requests, check the token validity and then launch the right method of the object
*/

// required headers
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
if($jwt === ""){
	$jwt = isset($_GET["jwt"]) ? $_GET["jwt"] : "";
}

//check the token
$is_token_valid = validate_token($jwt);


if ( $is_token_valid )
{
    //token is ok, user can manipulate datas

    include_once './objects/localisation.php';
	$localisation = new Localisation();


	//check if requested url look like /localisation or /localisation/id 
	if(isset($_GET["id"])) { // requested url look like /localisation/id
		//convert 	/0 		-> all
		//			/0000 	-> all
		//			/00001 	-> 1
		$localisation->id = intval($_GET["id"]) <= 0 ? "all" : intval($_GET["id"]);
	}
	else { // requested url look like /localisation
		$localisation->id = "all";
	}





	switch($_SERVER["REQUEST_METHOD"])
	{


		case 'GET':
			$localisation = $localisation->get();
			if($localisation) {
				//success
				echo json_encode($localisation);
			}
			break;







		case 'POST': //create new localisation

			//we cant create a user with a targeted id
			if($localisation->id !== "all") { 
				echo json_encode(array(	"Error" => "Bad request",
										"Code" => "101"));
				exit;
			}

			//check if all variables of the object are present
			if (
				isset($data->location) &&
				isset($data->fileid)
			   )
			{
				// sanitize
				$localisation->location=htmlspecialchars(strip_tags($data->location));
				$localisation->fileid=htmlspecialchars(strip_tags($data->fileid));





				//execute the creation
				if($localisation->post())
				{
					//successfully created a localisation

					echo json_encode($localisation);
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
			if($localisation->id === "all") { 
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
				isset($data->location) &&
				isset($data->fileid)
			   )
			{
				// sanitize
				$localisation->location=htmlspecialchars(strip_tags($data->location));
				$localisation->fileid=htmlspecialchars(strip_tags($data->fileid));



				//execute the update of the user
				if($localisation->put())
				{
					//successfully updated


					/**
					 * _ACTION_ #encapsulates_datas
					 * 
					 * If your object is used to login,
					 * you have to generate a new token
					 * 
					 * example :
					 * include_once 'validate_token.php';
					 * make_token($user->id);
					 */
					echo json_encode($localisation);
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
			if($localisation->id === "all") { 
				echo json_encode(array(	"Error" => "Bad request",
										"Code" => "101"));
				exit;
			}
			
			else {
				//delete the object
				if($localisation->delete()) {
					//successfully deleted
					echo json_encode(array(	"message" => "Successfully deleted",
											"id" => $localisation->id));
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
	unset($localisation);
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
