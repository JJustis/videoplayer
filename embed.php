<?php
// embed.php
$videoName = $_GET['v'];
$uploadDir = 'uploads/';
$videoFile = $uploadDir . $videoName;
$metadataFile = $uploadDir . pathinfo($videoName, PATHINFO_FILENAME) . '.json';

$metadata = json_decode(file_get_contents($metadataFile), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $metadata['title']; ?> - YourTube Embed</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
        }
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
    <div class="video-container">
        <video controls>
            <source src="<?php echo $videoFile; ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</body>
</html>