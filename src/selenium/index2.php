<?php
// Handle File Upload Simulation
$uploadStatus = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    $uploadStatus = "File " . basename($_FILES["fileToUpload"]["name"]) . " uploaded successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Selenium Sandbox</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #eceff1; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #2196f3; }
        #drag-source, #drag-target { width: 100px; height: 100px; padding: 10px; border: 2px dashed #bbb; margin: 10px; display: inline-block; }
        #drag-source { background-color: #ffeb3b; cursor: grab; }
        .hidden-content { display: none; margin-top: 10px; padding: 10px; background: #e8f5e9; border: 1px solid #4caf50; }
        iframe { width: 100%; height: 150px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<h1>Advanced Testing Sandbox <small>(Docker PHP Edition)</small></h1>

<div class="grid">

    <div class="card" id="drag-drop-section">
        <h3>1. Drag and Drop</h3>
        <p>Drag the yellow square into the box.</p>
        <div id="drag-source" draggable="true" ondragstart="event.dataTransfer.setData('text', 'source')">DRAG ME</div>
        <div id="drag-target" ondrop="drop(event)" ondragover="allowDrop(event)">DROP HERE</div>
        <script>
            function allowDrop(ev) { ev.preventDefault(); }
            function drop(ev) { 
                ev.preventDefault(); 
                document.getElementById('drag-target').innerHTML = "✅ Dropped!";
                document.getElementById('drag-target').style.backgroundColor = "#c8e6c9";
            }
        </script>
    </div>

    <div class="card">
        <h3>2. Dynamic Content</h3>
        <button id="startButton" onclick="startLoading()">Start Loading Element</button>
        <div id="loading" style="display:none;">⏳ Loading...</div>
        <div id="finish" class="hidden-content">
            <h4>Hello World!</h4>
            <p>I appeared dynamically via JavaScript.</p>
        </div>
        <script>
            function startLoading() {
                document.getElementById('startButton').disabled = true;
                document.getElementById('loading').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('finish').style.display = 'block';
                }, 5000);
            }
        </script>
    </div>

    <div class="card">
        <h3>3. iFrame Context</h3>
        <p>Switch context to interact with the button inside.</p>
        <iframe srcdoc="<html><body><button id='iframe-btn' onclick='this.innerHTML=&quot;Clicked!&quot;'>Click Me Inside iFrame</button></body></html>" name="test-frame" id="test-frame"></iframe>
    </div>

    <div class="card">
        <h3>4. File Upload</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="file-upload">
            <input type="submit" value="Upload File" name="submit" id="file-submit">
        </form>
        <p id="upload-msg" style="color: green;"><?php echo $uploadStatus; ?></p>
    </div>

    <div class="card">
        <h3>5. Hover Interaction</h3>
        <div id="hover-box" style="padding: 20px; background: #9c27b0; color: white; text-align: center;">
            Hover over me to see the menu
        </div>
        <div id="hover-menu" style="display: none; padding: 10px; background: #f3e5f5;">
            <a href="#">Hidden Link 1</a> | <a href="#">Hidden Link 2</a>
        </div>
        <script>
            const box = document.getElementById('hover-box');
            const menu = document.getElementById('hover-menu');
            box.onmouseover = () => menu.style.display = 'block';
            box.onmouseout = () => menu.style.display = 'none';
        </script>
    </div>

</div>

</body>
</html>