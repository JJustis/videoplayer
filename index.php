<?php
// index.php
$uploadDir = 'uploads/';
$allFiles = scandir($uploadDir);

// Filter to get only the main video files (excluding previews)
$videoFiles = array_filter($allFiles, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'mp4' && strpos($file, '_preview') === false;
});

// Sort files by modification time to get the most recent ones
usort($videoFiles, function($a, $b) use ($uploadDir) {
    return filemtime($uploadDir . $b) - filemtime($uploadDir . $a);
});

$featuredVideos = array_slice($videoFiles, 0, 5); // Get 5 recent videos

function getVideoMetadata($filename) {
    global $uploadDir;
    $jsonFile = $uploadDir . pathinfo($filename, PATHINFO_FILENAME) . '.json';
    if (file_exists($jsonFile)) {
        return json_decode(file_get_contents($jsonFile), true);
    }
    return [
        'title' => pathinfo($filename, PATHINFO_FILENAME),
        'description' => '',
        'thumbnail' => 'default-thumbnail.jpg', // Fallback
        'preview' => 'default-preview.mp4'     // Fallback
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourTube - 2010 Style</title>
    <link rel="stylesheet" href="styles.css">
</head>
    <style>
        .upload-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .upload-section h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            max-width: 100%;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input[type="file"],
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 10px 0;
        }

        .form-group input[type="file"]::file-selector-button {
            padding: 8px 16px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background-color: #0056b3;
        }

        .submit-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
<body>
<?php include 'header.php'; ?>
    
    <div class="content-wrapper">
        <main>
            <section id="featured">
                <h2>Featured Videos</h2>
                <div class="video-grid">
                    <?php foreach ($featuredVideos as $video): 
                        $metadata = getVideoMetadata($video);
                        $thumbnailFile = $metadata['thumbnail'];
                    ?>
                        <div class="video-item">
                            <a href="video.php?v=<?php echo urlencode($video); ?>">
                                <img src="<?php echo $thumbnailFile; ?>" alt="Thumbnail" class="thumbnail" />
                                <div class="video-info">
                                    <h4><?php echo htmlspecialchars($metadata['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($metadata['description']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
                   <section id="upload" class="upload-section">
                <h2>Upload Your Video</h2>
                <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="video">Choose mp4 File:</label>
                        <input type="file" name="video" id="video" accept="video/mp4,video/webm,video/ogg,video/quicktime" required>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail URL:</label>
                        <input type="text" name="thumbnail" id="thumbnail" placeholder="Enter thumbnail image URL" required>
                    </div>
                    <div class="form-group">
                        <label for="title">Video Title:</label>
                        <input type="text" name="title" id="title" placeholder="Enter video title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Video Description:</label>
                        <textarea name="description" id="description" placeholder="Enter video description"></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Upload Video</button>
                </form>
            </section>
        </main>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; 2024 BetaHut | <a href="#">Privacy</a> | <a href="#">Terms</a></p>
        </div>
    </footer>
</body>
</html>
