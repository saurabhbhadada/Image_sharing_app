<?php
//turn on php error reporting
require_once("session.php");
require_once("class.user.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$auth_user = new USER();
// get user id from session
$user_id = $_SESSION['user_session'];

// fetch data from data base	
$stmt = $auth_user->runQuery("SELECT * FROM users WHERE user_id=:user_id");
$stmt->execute(array(":user_id"=>$user_id));
$userRow=$stmt->fetch(PDO::FETCH_ASSOC);

$database = new Database();
$db = $database->dbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$name     = $_FILES['file']['name'];
	$tmpName  = $_FILES['file']['tmp_name'];
	$error    = $_FILES['file']['error'];
	$size     = $_FILES['file']['size'];
    $ext	  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  	$name= $userRow['user_email'].$name;
	switch ($error) {
		case UPLOAD_ERR_OK:
			$valid = true;
			//validate file extensions
			if ( !in_array($ext, array('jpg','jpeg','png','gif')) ) {
				$valid = false;
				$response = 'Invalid file extension.';
			}
			//validate file size
			if ( $size/1024/1024 > 2 ) {
				$valid = false;
				$response = 'File size is exceeding maximum allowed size.';
			}
			//upload file
			if ($valid) {
				// create a database entry
				echo $userRow['user_email'];
				$stmt_1=$db->prepare("INSERT INTO images(img_name,owner) 
		                                               VALUES(:imgname, :own)");
				$stmt_1->bindparam(":imgname", $name);
				$stmt_1->bindparam(":own", $userRow['user_email']);
				$stmt_1->execute();
				$targetPath =  dirname( __FILE__ ) . DIRECTORY_SEPARATOR. 'uploads' . DIRECTORY_SEPARATOR. $name;
				move_uploaded_file($tmpName,$targetPath); 
				header( 'Location: index.php' ) ;
				exit;
			}
			break;
		case UPLOAD_ERR_INI_SIZE:
			$response = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			break;
		case UPLOAD_ERR_PARTIAL:
			$response = 'The uploaded file was only partially uploaded.';
			break;
		case UPLOAD_ERR_NO_FILE:
			$response = 'No file was uploaded.';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$response = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$response = 'Failed to write file to disk. Introduced in PHP 5.1.0.';
			break;
		default:
			$response = 'Unknown error';
		break;
	}

	echo $response;
}
?>