<?php
include '../../controller/PostC.php';
$user_id = $_POST['user_id'];
$author = $_POST['author'];
$message = $_POST['message'];
$time = $_POST['currentTime'];
$id = $_POST['id'];
$image_path = null;
$error_message = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        // Allow certain file formats
        if (in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $error_message = "File is not an image.";
    }
}

$pc = new PostC();
$p = new Post($user_id,$author, $message, $time, $image_path,$status);
$pc->modifyPost($p,$id);

// Redirect with error message if there was an upload issue
if (!empty($error_message)) {
    header('Location: index.php?error=' . urlencode($error_message));
} else {
    header('Location: index.php?success=1');
}
exit();
?>
