<?php
// video.php
$videoName = $_GET['v'];
$uploadDir = 'uploads/';
$videoFile = $uploadDir . $videoName;
$metadataFile = $uploadDir . pathinfo($videoName, PATHINFO_FILENAME) . '.json';
$commentsFile = $uploadDir . pathinfo($videoName, PATHINFO_FILENAME) . '_comments.json';
$chatFile = $uploadDir . pathinfo($videoName, PATHINFO_FILENAME) . '_chat.json';

$metadata = json_decode(file_get_contents($metadataFile), true);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment'])) {
        // Handle comment submission
        $comment = [
            'text' => $_POST['comment'],
            'date' => date('Y-m-d H:i:s')
        ];
        $comments = file_exists($commentsFile) ? json_decode(file_get_contents($commentsFile), true) : [];
        $comments[] = $comment;
        file_put_contents($commentsFile, json_encode($comments));
    } elseif (isset($_POST['chat_message'])) {
        // Handle chat message submission
        $chatMessage = [
            'text' => $_POST['chat_message'],
            'date' => date('Y-m-d H:i:s')
        ];
        $chatMessages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];
        $chatMessages[] = $chatMessage;
        file_put_contents($chatFile, json_encode($chatMessages));
        echo json_encode(['success' => true]);
        exit;
    }
}

$comments = file_exists($commentsFile) ? json_decode(file_get_contents($commentsFile), true) : [];
$chatMessages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];

// Get suggested videos
$allVideos = array_filter(scandir($uploadDir), function($file) use ($videoName) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'mp4' && $file !== $videoName && !strpos($file, '_preview');
});
$suggestedVideos = array_slice($allVideos, 0, 5); // Get 5 random videos

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$videoUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$embedUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/videoplayer/embed.php?v=" . urlencode($videoName);

function getVideoTitle($filename) {
    global $uploadDir;
    $jsonFile = $uploadDir . pathinfo($filename, PATHINFO_FILENAME) . '.json';
    if (file_exists($jsonFile)) {
        $metadata = json_decode(file_get_contents($jsonFile), true);
        return $metadata['title'] ?? pathinfo($filename, PATHINFO_FILENAME);
    }
    return pathinfo($filename, PATHINFO_FILENAME);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $metadata['title']; ?> - BetaHut</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php'; ?>
    <div class="content-wrapper">
        <main>
            <div class="video-sidebar-container">
                <div class="video-container">
                    <section id="video-player">
                        <video width="100%" height="440" controls>
                            <source src="<?php echo $videoFile; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <h2><?php echo $metadata['title']; ?></h2>
                        <p><?php echo $metadata['description']; ?></p>
                        <p>Uploaded on: <?php echo $metadata['upload_date']; ?></p>
                        
                        <div class="share-buttons">
                            <button onclick="shareOnFacebook()">Share on Facebook</button>
                            <button onclick="shareOnTwitter()">Share on Twitter</button>
                            <button onclick="showEmbedCode()">Embed</button>
                        </div>
                        
                        <div id="embed-code" style="display: none;">
                            <textarea id="embed-textarea" rows="4" readonly></textarea>
                            <button onclick="copyEmbedCode()">Copy Embed Code</button>
                        </div>
                    </section>
                    
                    <section id="comments">
                        <h3>Comments</h3>
                        <form method="post">
                            <textarea name="comment" placeholder="Add a comment" required></textarea>
                            <input type="submit" value="Post Comment">
                        </form>
                        <div class="comment-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <p><?php echo htmlspecialchars($comment['text']); ?></p>
                                    <small>Posted on: <?php echo $comment['date']; ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
                
                <div class="sidebar">
                    <div class="chat-container">
                        <h3>Live Chat</h3>
                        <div id="chat-messages">
                            <?php foreach ($chatMessages as $message): ?>
                                <div class="chat-message">
                                    <p><?php echo htmlspecialchars($message['text']); ?></p>
                                    <small><?php echo $message['date']; ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <form id="chat-form">
                            <input type="text" id="chat-input" placeholder="Type your message..." required>
                            <button type="submit">Send</button>
                        </form>
                    </div>
                    
                <div class="suggested-videos">
    <h3>Suggested Videos</h3>
    <?php foreach ($suggestedVideos as $video): ?>
        <?php
        $suggestedMetadataFile = $uploadDir . pathinfo($video, PATHINFO_FILENAME) . '.json';
        $suggestedMetadata = json_decode(file_get_contents($suggestedMetadataFile), true);
        $thumbnailUrl = $suggestedMetadata['thumbnail'] ?? '';
        $videoTitle = $suggestedMetadata['title'] ?? pathinfo($video, PATHINFO_FILENAME);
        $videoDescription = $suggestedMetadata['description'] ?? '';
        $uploadDate = isset($suggestedMetadata['upload_date']) ? date('M d, Y', strtotime($suggestedMetadata['upload_date'])) : '';
        ?>
        <div class="suggested-video-item">
            <a href="video.php?v=<?php echo urlencode($video); ?>">
                <img src="<?php echo htmlspecialchars($thumbnailUrl); ?>" alt="<?php echo htmlspecialchars($videoTitle); ?>">
                <div class="video-info">
                    <div>
                        <h4><?php echo htmlspecialchars($videoTitle); ?></h4>
                        <p><?php echo htmlspecialchars($videoDescription); ?></p>
                    </div>
                    <div class="video-meta">
                        Uploaded on <?php echo $uploadDate; ?>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
                </div>
            </div>
        </main>
    </div>
    
    <footer>
        <p>&copy; 2024 BetaHut | Privacy | Terms</p>
    </footer>

    <script>
        // ... (keep the existing JavaScript for sharing, embedding, and chat functionality)

        function shareOnFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent('<?php echo $videoUrl; ?>'), 'facebook-share-dialog', 'width=626,height=436');
        }

        function shareOnTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent('<?php echo $videoUrl; ?>') + '&text=' + encodeURIComponent('Check out this video: <?php echo $metadata['title']; ?>'), 'twitter-share-dialog', 'width=626,height=436');
        }

        function showEmbedCode() {
            var embedCode = '<iframe width="560" height="315" src="<?php echo $embedUrl; ?>" frameborder="0" allowfullscreen></iframe>';
            document.getElementById('embed-textarea').value = embedCode;
            document.getElementById('embed-code').style.display = 'block';
        }

        function copyEmbedCode() {
            var embedTextarea = document.getElementById('embed-textarea');
            embedTextarea.select();
            document.execCommand('copy');
            alert('Embed code copied to clipboard!');
        }

        // Live Chat functionality
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const chatMessages = document.getElementById('chat-messages');

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (chatInput.value.trim() !== '') {
                sendChatMessage(chatInput.value);
                chatInput.value = '';
            }
        });

        function sendChatMessage(message) {
            fetch('video.php?v=<?php echo urlencode($videoName); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'chat_message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChat();
                }
            });
        }

        function updateChat() {
            fetch('get_chat.php?v=<?php echo urlencode($videoName); ?>')
            .then(response => response.json())
            .then(data => {
                chatMessages.innerHTML = '';
                data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'chat-message';
                    messageDiv.innerHTML = `
                        <p>${message.text}</p>
                        <small>${message.date}</small>
                    `;
                    chatMessages.appendChild(messageDiv);
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
        }

        setInterval(updateChat, 5000); // Update chat every 5 seconds
    </script>
</body>
</html>