<?php
session_start();
include 'connectdb.php';
include 'nav.php';
$logged_in = $_SESSION['logged_in'] ?? false;
$role = $_SESSION['role'] ?? '';

// Search functionality
$news = [];
if(isset($_POST['submit'])){
    $search = $_POST['search'];
    
    // Prepared statement for safer search
    $stmt = $conn->prepare("SELECT * FROM news WHERE title LIKE ? ORDER BY time_created DESC");
    $searchTerm = "%$search%"; // Use LIKE for partial matches
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
}

// If no search performed, fetch all articles
if (empty($news)) {
    $stmt = $conn->prepare("SELECT * FROM news ORDER BY time_created DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List News</title>
    <link rel="stylesheet" href="list_news.css">
    <link rel="stylesheet" href="main.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cambo&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');
    </style>
</head>
<body>
<h1 class="title"><u>All Articles</u></h1>
<br>
<?php
if($role === 'admin'){
    echo '<a href="news_upload.php" class="content-creatorbtn">Create Article</a>'; 
}
?>
<br> <br>
<a class="rumoursbtn" href="latest_rumours.php">Latest GTFC Rumours</a> <br>
<br>
    <div class="refresh">
        <a> <a href="list_news.php"> Click here to refresh articles</a></a>
    </div>
    <div class="search-button">
        <form method='POST'>
            <input type="text" placeholder="Search for a article or author" name="search">
            <button name="submit">Search</button>
        </form>
    </div>

    <div class="posts-container">
        <?php
        // Display posts
        if (!empty($news)) {
            foreach ($news as $row) {
                echo '<section class="newsCard">';
                  if (!empty($row['picture'])) {
                    echo '<img src="' . htmlspecialchars($row['picture']) . '" alt="Article image" class="listnews-article-image">' . '<br>';
                }
                echo '<p><a href="retrieve_news.php?id=' . htmlspecialchars($row['id']) . '">' . '<br>',  htmlspecialchars($row['title']) . '</a></p>';

                if($role === 'admin'){
                    echo '<p>
                        <a href="edit_news.php?id=' . htmlspecialchars($row['id']) . '">Edit</a> |
                        <a onclick="return confirm(\'Do You Really Want To Delete This?\')" href="delete_news.php?id=' . htmlspecialchars($row['id']) . '">Delete</a>
                    </p>';
                }

                echo '<p>Description: ' . htmlspecialchars($row['description']) . '</p>';
                echo '<p>Article written: ' . htmlspecialchars($row['time_created']) . '</p>';
                echo '</section>';
            }
        } else {
            echo "<p>No articles found.</p>";
        }
        ?>
    </div>
</body>
</html>
