<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
</head>
<body>
    <h1>Contact Us</h1>
    <form action="contact.php" method="post" enctype="multipart/form-data">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="message">Message:</label><br>
        <textarea id="message" name="message" required></textarea><br><br>
        
        <label for="file">Upload a file:</label><br>
        <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required><br><br>
        
        <input type="submit" value="Send">
    </form>

    <?php

        $conn = new mysqli('localhost', 'root', '', 'contact_form');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

    // Step 3: Handle the form submission and insert data into the database
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $message = htmlspecialchars(trim($_POST['message']));
        
        // File upload handling
        $target_dir = "uploads/"; // Directory to save uploaded files
        
        // Check if the file was uploaded
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
            $target_file = $target_dir . basename($_FILES["file"]["name"]);
            $uploadOk = 1;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file is a valid upload
            if (isset($_POST["submit"]) && isset($_FILES["file"])) {
                $check = getimagesize($_FILES["file"]["tmp_name"]);
                if ($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    $uploadOk = 0;
                }
            }

            // Check file size (limit to 2MB)
            if ($_FILES["file"]["size"] > 2000000) {
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
                echo "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error or if no file was uploaded
            if ($uploadOk == 0 || !isset($_FILES["file"]["name"]) || empty($_FILES["file"]["name"])) {
                echo "Sorry, your file was not uploaded.";
            } else {
                // If everything is ok, try to upload the file
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                    // Prepare and bind
                    $stmt = $conn->prepare("INSERT INTO contact (name, email, message, file_path) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $name, $email, $message, $target_file);

                    // Execute the statement
                    if ($stmt->execute()) {
                        echo "<p>Thank you for your message, $name. It has been sent.</p>";
                        echo "<h2>Your Submitted Details:</h2>";
                        echo "<p>Name: $name</p>";
                        echo "<p>Email: $email</p>";
                        echo "<p>Message: $message</p>";
                        echo "<p>File uploaded: <a href='$target_file'>View File</a></p>";
                    } else {
                        echo "<p>Sorry, there was an error sending your message. Please try again later.</p>";
                    }

                    // Close the statement and connection
                    $stmt->close();
                    $conn->close();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }
    }
    ?>
</body>
</html>
