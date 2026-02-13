<!DOCTYPE html>
<html>
<head>
    <title>Add/Remove Elements</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .added-manually { 
            display: block; 
            margin-top: 10px; 
            padding: 5px 15px; 
            background: #f44336; 
            color: white; 
            border: none; 
            cursor: pointer; 
        }
        .add-btn { padding: 10px 20px; background: #2196f3; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h3>Add/Remove Elements</h3>
    <button class="add-btn" onclick="addElement()">Add Element</button>
    
    <div id="elements"></div>

    <script>
        function addElement() {
            const btn = document.createElement("button");
            btn.innerHTML = "Delete";
            btn.className = "added-manually";
            btn.onclick = function() { this.remove(); };
            document.getElementById("elements").appendChild(btn);
        }
    </script>
</body>
</html>