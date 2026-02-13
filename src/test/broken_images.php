<!DOCTYPE html>
<html>
<head>
    <title>Broken Images</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        img { border: 1px solid #ccc; margin: 10px; width: 120px; height: 120px; }
        .example { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="example">
        <h3>Broken Images</h3>
        <p>In this example, your automation script should verify if the images are physically rendered on the page.</p>
        
        <img src="https://placehold.co/120x120/000000/FFFFFF/png" alt="Valid Image">

        <img src="asdf.jpg" alt="Broken Image 1">
        <img src="hjkl.jpg" alt="Broken Image 2">
    </div>
</body>
</html>