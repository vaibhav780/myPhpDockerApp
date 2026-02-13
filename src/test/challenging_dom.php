<?php
// Generate random strings for IDs and Classes to "confuse" Selenium
$id_suffix = substr(md5(mt_rand()), 0, 8);
$button_colors = ['primary', 'success', 'alert'];
shuffle($button_colors);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Challenging DOM</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .button { padding: 10px 20px; color: white; border: none; cursor: pointer; margin: 5px; }
        .primary { background: #2196f3; }
        .success { background: #4caf50; }
        .alert { background: #f44336; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Challenging DOM</h2>
    <p>The buttons below change their IDs and Classes on every refresh. Try to click them!</p>

    <button id="btn-<?php echo $id_suffix; ?>" class="button <?php echo $button_colors[0]; ?>">Blue Button</button>
    <button id="btn-alt-<?php echo $id_suffix; ?>" class="button <?php echo $button_colors[1]; ?>">Green Button</button>
    
    <table>
        <tr>
            <th>Lorem</th><th>Ipsum</th><th>Action</th>
        </tr>
        <?php for($i=0; $i<3; $i++): ?>
        <tr>
            <td>Data <?php echo $i; ?></td>
            <td>Ipsum <?php echo $i; ?></td>
            <td><a href="#edit">edit</a> | <a href="#delete">delete</a></td>
        </tr>
        <?php endfor; ?>
    </table>
</body>
</html>