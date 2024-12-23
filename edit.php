<?php

class FileUploader {
    private $targetDir = "upload/";
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getFileById($id) {
        $query = "SELECT * FROM files WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateFile($id, $name, $author, $category, $file) {
        $file_name = basename($file["name"]);
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_file = $this->targetDir . uniqid() . "." . $imageFileType;

        // Check if a new file is uploaded
        if (!empty($file["tmp_name"])) {
            // Check if the file is an image
            $check = getimagesize($file["tmp_name"]);
            if ($check === false) {
                return "File is not an image.";
            }
            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file["tmp_name"], $target_file)) {
                return "Sorry, there was an error uploading your file.";
            }
        } else {
            // If no new file uploaded, keep the existing file
            $query = "SELECT file_path FROM files WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $target_file = $row['file_path'];
        }

        // Update file details in the database
       $query = "UPDATE files SET name = ?, author = ?, category = ?, file_path = ? WHERE id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("ssssi", $name, $author, $category, $target_file, $id);
    if ($stmt->execute()) {
        // File updated successfully, redirect to addbook.php
        header("Location: display.php");
        exit();
    } else {
        return "Error updating file.";
    }
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
    $id = $_POST['id'];
    $name = $_POST['name'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $file = $_FILES["fileToUpload"];

    // Update file record
    $result = $uploader->updateFile($id, $name, $author, $category, $file);
    echo $result;
}

// Check if ID parameter is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Retrieve file details by ID
    $file = $uploader->getFileById($id);
    if (!$file) {
        echo "File not found.";
        exit();
    }
} else {
    echo "ID parameter is missing.";
    exit();
}
?>


<html>
<head>
    <title>Edit Book</title>
	<link rel="stylesheet" type="text/css" href="css/style1.css">
</head>
<body>
    <h1>Edit Book</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $file['id']; ?>">
        Name: <input type="text" name="name" value="<?php echo $file['name']; ?>"><br>
        Author: <input type="text" name="author" value="<?php echo $file['author']; ?>"><br>
        Category: <input type="text" name="category" value="<?php echo $file['category']; ?>"><br>
        Select new image to upload:
        <input type="file" name="fileToUpload" id="fileToUpload"><br>
        Current Image: <br>
        <img src="<?php echo $file['file_path']; ?>" alt="Current Image" style="max-width: 200px; max-height: 200px;"><br>
        <input type="submit" value="Update File">
		<a href="display.php" value="cancle">Cancel</a>
    </form>
</body>
</html>
