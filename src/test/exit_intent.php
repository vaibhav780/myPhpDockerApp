<!DOCTYPE html>
<html>
<head>
    <title>Exit Intent</title>
    <style>
        #ouibounce-modal { display: none; position: fixed; top: 20%; left: 30%; width: 40%; background: white; border: 3px solid #f44336; padding: 20px; z-index: 1000; }
    </style>
</head>
<body onmouseleave="document.getElementById('ouibounce-modal').style.display='block'">
    <h3>Exit Intent</h3>
    <p>Move your mouse out of the browser viewport to trigger the modal.</p>
    <div id="ouibounce-modal">
        <h2>Exit Intent Detected!</h2>
        <button onclick="this.parentElement.style.display='none'">Close</button>
    </div>
</body>
</html>