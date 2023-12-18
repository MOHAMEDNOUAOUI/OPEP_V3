<?php
require_once("../MODULES/MODULES.php");

if(isset($_GET['themesearch'])){
    $idtheme = $_GET['themesearch'];
    $articles = new Article();
    $row = $articles->load($idtheme);
    foreach($row as $article) {
        $imageData = base64_encode($article->__get("article_image"));
        $imageType = 'image/jpeg';
        $articleID = $article->__get("article_id");
        ?>
        <div onclick="attachClickListeners(<?php echo $article->__get('article_id')?>)" class="card  ml-7 border border-green-500 rounded-xl transition-transform duration-300 ease-in-out transform hover:scale-105 hover:shadow-2xl mr-4" data-key="<?php echo $article->__get("article_id")?>">
<div class="ml-5 mr-5 mt-5 mb-5">
            <h1  class="text-2xl text-center font-semibold mb-3 "><?php echo $article->__get("article_title")?></h1>
            <img class="h-96 w-96 mb-5" src="data:<?php echo $imageType ?>;base64,<?php echo $imageData ?>" alt="Article Image">
            <h3 class=" font-sans"><?php echo $article->__get("article_text")?></h3>
        </div> 
        </div>
    <?php    
}
}
?>



                    