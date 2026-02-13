<!DOCTYPE html>
<html>
<body>
    <h3>File Uploader</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" id="file-upload" name="file">
        <input type="submit" id="file-submit" value="Upload">
    </form>
    <?php if($_FILES) echo "File Uploaded: " . $_FILES['file']['name']; ?>
</body>
</html>