<?php
// upload.php
$uploadDir = 'uploads/';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if video file was uploaded and thumbnail URL was provided
    if (isset($_FILES['video']) && isset($_POST['thumbnail'])) {
        $videoFile = $uploadDir . basename($_FILES['video']['name']);
        $thumbnailUrl = $_POST['thumbnail'];
        $previewFile = $uploadDir . pathinfo($_FILES['video']['name'], PATHINFO_FILENAME) . '_preview.mp4';

        // Basic file type validation for video
        $allowedVideoTypes = ['video/mp4', 'video/mpeg', 'video/quicktime'];

        if (in_array($_FILES['video']['type'], $allowedVideoTypes)) {
            if (move_uploaded_file($_FILES['video']['tmp_name'], $videoFile)) {
                // Generate 5-second preview video
                exec("ffmpeg -i \"$videoFile\" -ss 00:00:05 -t 00:00:05 -c:v libx264 -c:a aac -strict experimental -b:a 192k \"$previewFile\"");
                
                // Save video metadata
                $metadata = [
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'upload_date' => date('Y-m-d H:i:s'),
                    'thumbnail' => $thumbnailUrl, // Store the URL as-is
                    'preview' => basename($previewFile)
                ];
                file_put_contents($uploadDir . pathinfo($_FILES['video']['name'], PATHINFO_FILENAME) . '.json', json_encode($metadata));
                
                header('Location: index.php');
                exit;
            } else {
                echo "Sorry, there was an error uploading your video file.";
            }
        } else {
            echo "Invalid file type. Please upload an MP4 video.";
        }
    } else {
        echo "Please upload a video file and provide a thumbnail URL.";
    }
} else {
    echo "Invalid request method.";
}
?>