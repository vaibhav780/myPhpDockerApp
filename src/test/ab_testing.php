<?php
// Randomly decide which version to show
$version = (rand(0, 1) == 0) ? "A" : "B";
$title = ($version == "A") ? "A/B Test Variation 1" : "No-A/B Test Control";
?>
<!DOCTYPE html>
<html>
<head>
    <title>A/B Test</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .content { border: 2px dashed #999; padding: 20px; background: #fafafa; }
    </style>
</head>
<body>
    <div class="content">
        <h1><?php echo $title; ?></h1>
        <p>This page randomly changes its title between "Variation 1" and "Control" on every refresh. Use Selenium to verify that either title is acceptable.</p>
        <p>Current internal version: <strong><?php echo $version; ?></strong></p>
    </div>
</body>
</html>