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



// Fetch files from database
$files = $uploader->getFiles();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploaded Files</title>
    <style>
       .container {
    width: 80%; 
    margin: 0 auto; 
    border: 1px solid #ddd; 
    border-radius: 8px; 
    overflow: hidden; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Style the table */
table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ddd; 
}

th, td {
    padding: 12px;
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f2f2f2; 
}

/* Style the header row */
th {
    background-color: #4CAF50; 
    color: white; 
}
td {
    background-color: white; 
}
    </style>
		<link rel="stylesheet" type="text/css" href="css/style1.css">
</head>
<body>   
 
    <h1>Upload Books</h1>

    <br>
    <?php if (!empty($files)): ?>
    <table>
        <tr>
           
            <th>Name</th>
            <th>Author</th>
            <th>Category</th>
             <th>Image</th>
			 <th>price</th>
			 <th>Actions</th>
           
        </tr>
        <?php foreach ($files as $file): ?>
        <tr>
           
            <td><?php echo $file['name']; ?></td>
            <td><?php echo $file['author']; ?></td>
            <td><?php echo $file['category']; ?></td>
            <td><?php echo $file['price']; ?></td>
            <td><img src="<?php echo $file['file_path']; ?>" alt="Uploaded Image" style="max-width: 200px; max-height: 200px;"></td>
			<td>
                <a  href="edit.php?id=<?php echo $file['id']; ?>">Edit</a> <br><br>
                <a href="delete.php?id=<?php echo $file['id']; ?>" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
            </td>
				
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>No files uploaded yet.</p>
    <?php endif; ?>
</body>
</html>
