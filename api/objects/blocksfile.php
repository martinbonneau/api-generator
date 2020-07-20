<?php
include_once dirname(__DIR__).'/config/database.php';


// 'Blocksfile' object
class Blocksfile{
 
    // database connection and table name
    private $conn;
    private $table_name = "blocksfiles";
 
    // object properties
	public $id;
	public $fileid;
	public $blockid;


 
    // constructor
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }
 



    // get a blocksfile record
    public function get(){
        if($this->id === "all") // ID is set by handler in ../blocksfile.php
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




    // create new blocksfile record
    function post()
    {
        // insert query
        $query = "INSERT INTO " . $this->table_name . "
                SET
				fileid = :fileid,
				blockid = :blockid";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);

        // bind the values
		$stmt->bindParam(':fileid', $this->fileid);
		$stmt->bindParam(':blockid', $this->blockid);

        
    
        // execute the query, also check if query was successful
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId() ;
            return true;
        }
    
        return false;
    }






    // put a blocksfile record
    public function put(){

        $query = "UPDATE " . $this->table_name . "
                SET
				fileid = :fileid,
				blockid = :blockid
                WHERE id = :id";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);

        // bind the values
		$stmt->bindParam(':id', $this->id);
		$stmt->bindParam(':fileid', $this->fileid);
		$stmt->bindParam(':blockid', $this->blockid);


        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }








    // delete a blocksfile record
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