<?php
require('../MODULES/MODULES.php');

if(!isset($_SESSION["emaillogin"])) {
    header("login.php");
}
elseif(isset($_SESSION["emaillogin"])){
    $userid = $_SESSION["emaillogin"];
}


?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container grid grid-cols-2 ml-8  ">
    <?php
    $theme = new theme();
    $themearray = $theme->fetchAllThemes();
    foreach($themearray as $theme) {
        ?>

        <div class="card mt-5  max-w-xl text-center shadow-xl shadow-black  border border-green-500 rounded-xl transition-transform duration-300 ease-in-out transform hover:scale-110   " data-value="<?php echo $theme->__get("theme_id")?>">
        <h1 class="font-mono mb-5 underline text-2xl"><?php echo $theme->__get("theme_name")?></h1>
        </div>

        <?php
    }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script>

    var cards =document.querySelectorAll('.card');

    cards.forEach(btn => {
        btn.addEventListener("click" , function () {
            let value = this.getAttribute('data-value');
            console.log(value);
            window.location.href = 'articles.php?theme=' + value;
        })
    })

  </script>
</body>
</html>