<?php
// header.php
?>
<header>
    <div class="logo">BetaHut</div>
    <form action="search.php" method="get" class="search-bar">
        <input type="text" name="q" placeholder="Search" required>
        <button type="submit">Search</button>
    </form>
    <nav>
        <a href="index.php">Home</a>
        <a href="index.php#upload">Upload</a>
    </nav>
</header>