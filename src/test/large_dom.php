<!DOCTYPE html>
<html>
<head><title>Large & Deep DOM</title></head>
<body>
    <h3>Large & Deep DOM</h3>
    <div id="sibling-1.1">
        <div id="sibling-2.1">
            <div id="sibling-3.1">
                <div id="no-id-here">
                    <span class="target">Find me if you can!</span>
                </div>
            </div>
        </div>
    </div>

    <table id="large-table" border="1">
        <?php for($i=1; $i<=50; $i++): ?>
            <tr>
                <?php for($j=1; $j<=50; $j++): ?>
                    <td class="column-<?= $j ?>">Item <?= $i ?>.<?= $j ?></td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>
</body>
</html>