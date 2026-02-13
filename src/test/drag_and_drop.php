<!DOCTYPE html>
<html>
<head>
    <title>Drag and Drop</title>
    <style>
        .column { height: 150px; width: 150px; float: left; border: 2px solid #333; background-color: #ccc; margin: 10px; text-align: center; cursor: move; }
        .column header { color: #fff; text-shadow: #000 0 1px; box-shadow: 5px; padding: 5px; background: linear-gradient(to bottom, #eee, #ccc); }
    </style>
</head>
<body>
    <div id="columns">
        <div class="column" id="column-a" draggable="true"><header>A</header></div>
        <div class="column" id="column-b" draggable="true"><header>B</header></div>
    </div>
</body>
</html>