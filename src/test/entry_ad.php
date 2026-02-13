<!DOCTYPE html>
<html>
<head>
    <title>Entry Ad</title>
    <style>
        #modal { position: fixed; top: 20%; left: 30%; width: 40%; background: white; border: 2px solid black; padding: 20px; z-index: 1000; }
        #overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 999; }
    </style>
</head>
<body>
    <div id="overlay"></div>
    <div id="modal">
        <h3>This is a Modal Window</h3>
        <p>Close this to interact with the page.</p>
        <button onclick="document.getElementById('modal').remove(); document.getElementById('overlay').remove();">Close</button>
    </div>
    <h2>Entry Ad Example</h2>
    <p>The modal appeared as soon as you loaded the page.</p>
</body>
</html>