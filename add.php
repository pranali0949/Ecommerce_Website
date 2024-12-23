<?php

class FileUploader {
    private $targetDir = "upload/";
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

   public function uploadFile($file, $name, $author, $category, $price) { 
        $file_name = basename($file["name"]);
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $check = getimagesize($file["tmp_name"]);

        if ($check === false) {
            return "File is not an image.";
}
	

        $allowed_formats = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowed_formats)) {
            return "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        $new_file_name = uniqid() . "." . $imageFileType;
        $target_file = $this->targetDir . $new_file_name;

        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            return "Sorry, there was an error uploading your file.";
        }

        // Save file details to database
        $query = "INSERT INTO files (file_name, name, author, category, price, file_path) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("ssssss", $file_name, $name, $author, $category, $price, $target_file);
        if (!$stmt->execute()) {
            return "Error saving file details to database.";
        }

        // For demonstration, let's return the uploaded file details
        $result = array(
            "file_name" => $file_name,
            "name" => $name,
            "author" => $author,
            "category" => $category,
            "file_path" => $target_file
        );
        return $result;
    }

    public function getFiles() {
        $query = "SELECT * FROM files";
        $result = $this->db->query($query);
        $files = array();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $files[] = $row;
            }
        }
        return $files;
    }
	
}

// Create a MySQL database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "bookshop";
$port = 3310;

$db = new mysqli($servername, $username, $password, $database, $port);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$uploader = new FileUploader($db); 

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create instance of FileUploader class
    $uploader = new FileUploader($db);

    // Call uploadFile method and handle the result
    $uploadResult = $uploader->uploadFile($_FILES["fileToUpload"], $_POST['name'], $_POST['author'], $_POST['category'], $_POST['price']); // Pass price as the fourth argument

    if (is_array($uploadResult)) {
        // File uploaded successfully, refresh the page to display the updated table
        header("Location: display.php");
        exit();
    } else {
        // Error occurred during upload, display error message
        echo $uploadResult;
    }
}

// Fetch files from database
$files = $uploader->getFiles();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploaded Books</title>

		<link rel="stylesheet" type="text/css" href="css/style1.css">
</head>
<body>   
 
    <h1>Upload Books</h1>
 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    Name: <input type="text" name="name"><br><br>
    Author: <input type="text" name="author"><br><br>
    Category: <input type="text" name="category"><br>
    Price: <input type="text" name="price"><br> <!-- Add this line -->
    Select image to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>
    <br>

</body>
</html>
