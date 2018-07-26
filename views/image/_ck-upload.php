<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CKEditor upload</title>
</head>
<body>
<?php
// Required: anonymous function reference number as explained above.
$funcNum = $_GET['CKEditorFuncNum'] ;
// Optional: instance name (might be used to load a specific configuration file or anything else).
$CKEditor = $_GET['CKEditor'] ;

// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
$url = $model->url;

echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url');</script>";
?>
</body>
</html>