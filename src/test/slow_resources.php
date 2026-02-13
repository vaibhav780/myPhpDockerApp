<?php
// Simulate a very slow server response (30 seconds)
sleep(30);
?>
<!DOCTYPE html>
<html>
<head><title>Slow Resource</title></head>
<body>
    <h3 id="slow-header">Finally Loaded!</h3>
    <p>If your Selenium timeout was set to 10 seconds, this test failed.</p>
</body>
</html>