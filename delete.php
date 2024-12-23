<?php
// Connect to MySQL
$servername = "localhost";
$username = "root";
$password = ""; // Assuming no password for localhost
$database = "bookshop";
$port = 3310;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$id = "";
$file_name = "";
$name = "";
$author = "";
$category = "";
$file_path = "";

// Check if ID is provided in the URL
if(isset($_GET['id'])) {
    // Sanitize ID to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Retrieve data for the provided ID
    $sql = "SELECT * FROM files WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch data and store in variables
        $row = $result->fetch_assoc();
        $file_name = $row["file_name"];
        $name = $row["name"];
        $author = $row["author"];
        $category = $row["category"];
        $file_path = $row["file_path"];
    } else {
        echo "No record found for id: " . $id;
    }
}

// DELETE operation
if(isset($_POST['delete'])) {
    // Sanitize ID to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    // Delete record from the database
    $sql = "DELETE FROM files WHERE id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to dashboard.php after successful deletion
        header("Location: display.php");
        exit; // Ensure script execution stops after redirection
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style1.css">
    <title>Delete Book</title>
	<style>
	.container {
    width: 80%; 
    margin: 0 auto;
    border: 1px solid #ddd; 
    border-radius: 8px;
    background-color: white; 
    padding: 20px; 
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
}


	
	
	</style>
</head>
<body>
    <div class="container">
        <h2>Detail Book</h2>
        <p><strong>File Name:</strong> <?php echo $file_name; ?></p>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <p><strong>Author:</strong> <?php echo $author; ?></p>
        <p><strong>Category:</strong> <?php echo $category; ?></p>
        <p><strong>File Path:</strong> <?php echo $file_path; ?></p>

        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="submit" name="delete" value="Delete">
            <a href="display.php">Cancel</a>
        </form>
    </div>
</body>
</html>
