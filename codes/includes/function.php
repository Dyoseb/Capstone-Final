<?php
	date_default_timezone_set('Asia/Manila');
	error_reporting(E_ALL);
	ini_set('display_errors', 0);
	header('Access-Control-Allow-Origin: *'); 
	session_start();

	define('DB_SERVER','localhost');
	define('DB_USER','root');
	define('DB_PASS' ,'');
	define('DB_PORT', '3305');
	define('DB_NAME', 'capstone');

    class queries {

        public function __construct(){
			$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);
			$this->dbh=$con;
			// Check connection
			if (mysqli_connect_errno()) {
				echo "Failed to connect to MySQL: " . mysqli_connect_error();
	 		}
		}

        public function get_session(){
	        return $_SESSION['login'];
	    }


        public function get_appointments(){
            $select = mysqli_query($this->dbh,"SELECT * FROM appointments");
	    	return $select;
        }

        public function get_medical_records(){
            $select = mysqli_query($this->dbh,"SELECT * FROM medicalrecords");
	    	return $select;
        }
    }
?>