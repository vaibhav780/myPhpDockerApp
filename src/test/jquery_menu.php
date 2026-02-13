<!DOCTYPE html>
<html>
<head>
    <title>JQuery UI Menu</title>
    <style>
        .menu-item { width: 150px; background: #ddd; padding: 10px; cursor: pointer; position: relative; }
        .sub-menu { display: none; position: absolute; left: 150px; top: 0; background: #bbb; width: 100px; }
        .menu-item:hover .sub-menu { display: block; }
    </style>
</head>
<body>
    <h3>JQuery UI Menu</h3>
    <div class="menu-item" id="enabled">
        Enabled
        <div class="sub-menu">
            <div class="menu-item" id="downloads">
                Downloads
                <div class="sub-menu" style="left: 100px;">
                    <div class="menu-item"><a href="index.php">PDF</a></div>
                    <div class="menu-item"><a href="index.php">Excel</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>