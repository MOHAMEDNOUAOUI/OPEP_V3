<?php
include_once('./app/config/db.php');
$ARTICLE = new Article();
if(isset($_POST['articleid']) && isset($_POST['articletitle']) && isset($_POST['articlemessage']) && isset($_FILES['articleimg'])) {
    $articleid = $_POST['articleid'];
    $articletitle = $_POST['articletitle'];
    $articlemessage =$_POST['articlemessage'];

    if ($_FILES['articleimg']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['articleimg']['tmp_name'];
       
        $fileContent = file_get_contents($fileTmpPath);

        if ($articletitle != '' && $articlemessage != '') {
            
            $ARTICLE->update_article($articletitle,$fileTmpPath,$articlemessage,$articleid);
        
        } else {
            echo "Article title or message is empty!";
        }
    }

}
?>


