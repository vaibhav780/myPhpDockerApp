<!DOCTYPE html>
<html>
<head>
    <title>Disappearing Elements</title>
    <style>
        ul { list-style: none; }
        li { display: inline-block; margin: 10px; padding: 10px; background: #eee; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h3>Disappearing Elements</h3>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">Portfolio</a></li>
        <?php if (rand(0, 1) == 1): ?>
            <li id="gallery-link"><a href="#">Gallery</a></li>
        <?php endif; ?>
    </ul>
    <p>Refresh the page to see if the "Gallery" button appears or disappears!</p>
</body>
</html>