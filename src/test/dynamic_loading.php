<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Loading</title>
    <style>
        #finish { display:none; color: green; font-size: 24px; margin-top: 20px; }
        #loading { display:none; width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <h3>Dynamically Loaded Page Elements</h3>
    <button id="start-btn" onclick="startTest()">Start</button>
    
    <div id="loading"></div>
    <div id="finish"><h4>Hello World!</h4></div>

    <script>
        function startTest() {
            document.getElementById('start-btn').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            
            setTimeout(function() {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('finish').style.display = 'block';
            }, 5000); // 5 second delay to challenge Selenium timeouts
        }
    </script>
</body>
</html>