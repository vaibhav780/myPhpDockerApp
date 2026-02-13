<?php
$content = [
    ["img" => "https://placehold.co/80?text=User1", "text" => "Accusamus et iusto odio dignissimos ducimus qui."],
    ["img" => "https://placehold.co/80?text=User2", "text" => "Et harum quidem rerum facilis est et expedita distinctio."],
    ["img" => "https://placehold.co/80?text=User3", "text" => "Nam libero tempore, cum soluta nobis est eligendi optio."],
    ["img" => "https://placehold.co/80?text=User4", "text" => "Temporibus autem quibusdam et aut officiis debitis aut."],
];
shuffle($content);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Content</title>
    <style>.row { display: flex; align-items: center; margin-bottom: 20px; }</style>
</head>
<body>
    <h3>Dynamic Content</h3>
    <?php for($i=0; $i<3; $i++): ?>
        <div class="row">
            <img src="<?= $content[$i]['img'] ?>">
            <p><?= $content[$i]['text'] ?></p>
        </div>
    <?php endfor; ?>
    <p><a href="dynamic_content.php">Click here</a> to refresh and see the content change.</p>
</body>
</html>