<?php
// search.php
$uploadDir = 'uploads/';
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

function getVideoMetadata($filename) {
    global $uploadDir;
    $jsonFile = $uploadDir . pathinfo($filename, PATHINFO_FILENAME) . '.json';
    if (file_exists($jsonFile)) {
        return json_decode(file_get_contents($jsonFile), true);
    }
    return null;
}

function simplify($word) {
    // Remove trailing 's' or 'es' to handle basic plurals
    return rtrim($word, 's');
}

$searchResults = [];
if (!empty($searchQuery)) {
    $searchTerms = array_map('simplify', explode(' ', strtolower($searchQuery)));
    $allFiles = scandir($uploadDir);
    foreach ($allFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4' && !strpos($file, '_preview')) {
            $metadata = getVideoMetadata($file);
            if ($metadata) {
                $title = isset($metadata['title']) ? $metadata['title'] : pathinfo($file, PATHINFO_FILENAME);
                $description = isset($metadata['description']) ? $metadata['description'] : '';
                
                $content = strtolower($title . ' ' . $description);
                $matchScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strpos($content, $term) !== false) {
                        $matchScore++;
                    }
                }
                
                if ($matchScore > 0) {
                    $searchResults[] = [
                        'file' => $file,
                        'title' => $title,
                        'description' => $description,
                        'score' => $matchScore,
                        'thumbnail' => $metadata['thumbnail'] ?? ''
                    ];
                }
            }
        }
    }
    
    // Sort results by match score, highest first
    usort($searchResults, function($a, $b) {
        return $b['score'] - $a['score'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - BetaHut</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .video-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out;
        }
        .video-item:hover {
            transform: translateY(-5px);
        }
        .video-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .video-item h4 {
            margin: 10px 0 5px;
            font-size: 16px;
            color: #333;
        }
        .video-item p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .video-item .metadata {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">YourTube</div>
        <form action="search.php" method="get" class="search-bar">
            <input type="text" name="q" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>
        <nav>
            <a href="index.php">Home</a>
            <a href="index.php#upload">Upload</a>
        </nav>
    </header>
    
    <div class="content-wrapper">
        <main>
            <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
            <?php if (empty($searchResults)): ?>
                <p>No results found.</p>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach ($searchResults as $result): ?>
                        <div class="video-item">
                            <a href="video.php?v=<?php echo urlencode($result['file']); ?>">
                                <img src="<?php echo htmlspecialchars($result['thumbnail']); ?>" alt="<?php echo htmlspecialchars($result['title']); ?>">
                                <h4><?php echo htmlspecialchars($result['title']); ?></h4>
                            </a>
                            <p><?php echo htmlspecialchars(substr($result['description'], 0, 100)) . '...'; ?></p>
                            <div class="metadata">
                                Match score: <?php echo $result['score']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <footer>
        <p>&copy; 2024 BetaHut | Privacy | Terms</p>
    </footer>
</body>
</html>