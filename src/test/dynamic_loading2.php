<!DOCTYPE html>
<html>
<head><title>Dynamic Loading</title></head>
<body>
    <h3>Example 1: Element on page that is hidden</h3>
    <button onclick="this.style.display='none'; document.getElementById('loading').style.display='block'; 
        setTimeout(()=>{document.getElementById('loading').style.display='none'; document.getElementById('finish').style.display='block';}, 5000)">Start</button>
    <div id="loading" style="display:none;">Loading...</div>
    <div id="finish" style="display:none;"><h4>Hello World!</h4></div>
</body>
</html>