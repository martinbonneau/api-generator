<?php
include_once dirname(__DIR__).'/config/database.php';


// 'Localisation' object
class Localisation{
 
    // database connection and table name
    private $conn;
    private $table_name = "localisations";
 
    // object properties
	public $id;
	public $location;
	public $fileid;


 
    // constructor
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }
 



    // get a localisation record
    public function get(){
        if($this->id === "all") // ID is set by handler in ../localisation.php
        {
            // return all entities
            $query = "  SELECT * #_ACTION_ #dont_return_all you have to select only right fields 
                        FROM " . $this->table_name;

        }
        else {
            // return one entity
            $query = "  SELECT * #_ACTION_ #dont_return_all you have to select only usefull fields
                        FROM " . $this->table_name . "
                        WHERE id = " . $this->id;
        }
        
        // prepare the query
        $stmt = $this->conn->prepare($query);

        //execute the query
        if($stmt->execute()){
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return false;
    }




    // create new localisation record
    function post()
    {
        // insert query
        $query = "INSERT INTO " . $this->table_name . "
                SET
				location = :location,
				fileid = :fileid";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);

        // bind the values
		$stmt->bindParam(':location', $this->location);
		$stmt->bindParam(':fileid', $this->fileid);

        
    
        // execute the query, also check if query was successful
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId() ;
            return true;
        }
    
        return false;
    }






    // put a localisation record
    public function put(){

        $query = "UPDATE " . $this->table_name . "
                SET
				location = :location,
				fileid = :fileid
                WHERE id = :id";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);

        // bind the values
		$stmt->bindParam(':id', $this->id);
		$stmt->bindParam(':location', $this->location);
		$stmt->bindParam(':fileid', $this->fileid);


        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }








    // delete a localisation record
    public function delete(){
        if(isset($this->id))
        {
            $query = "  DELETE 
                        FROM    " . $this->table_name . "
                        WHERE id = :id";

            // prepare the query
            $stmt = $this->conn->prepare($query);

            // bind the values
            $stmt->bindParam(':id', $this->id);

            // execute and check if query was successful
            if($stmt->execute()) { return true; }
            
            //error
            return false;
        }
        
        //important error because here, the object should have an ID
        else
        {
            return false;
        }
    }    
}