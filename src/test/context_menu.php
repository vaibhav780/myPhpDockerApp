<!DOCTYPE html>
<html>
<head>
    <title>Context Menu</title>
    <style>
        #hot-spot {
            width: 250px;
            height: 150px;
            border: 2px dashed #000;
            text-align: center;
            padding-top: 60px;
            background: #fff9c4;
        }
    </style>
</head>
<body>
    <h3>Context Menu</h3>
    <p>Right-click in the box below to see a JS alert.</p>
    
    <div id="hot-spot" oncontextmenu="showMsg()">
        Right-click here
    </div>

    <script>
        function showMsg() {
            alert("You selected a context menu");
        }
    </script>
</body>
</html>