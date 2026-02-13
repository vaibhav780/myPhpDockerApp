<!DOCTYPE html>
<html>
<head><title>Key Presses</title></head>
<body>
    <h3>Key Presses</h3>
    <p>Press any key to see the result:</p>
    <input type="text" id="target">
    <p id="result" style="color: blue; font-size: 20px;"></p>

    <script>
        document.addEventListener('keydown', function(e) {
            document.getElementById('result').innerHTML = "You entered: " + e.key.toUpperCase();
        });
    </script>
</body>
</html>