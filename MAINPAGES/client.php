<?php
require_once('../MODULES/MODULES.php');
$USERS = new USERS();
$PLANTS = new PRODUCTS();
$PANIER = new panier();
$CATEGORY = new CATEGORY();
$userid = $_SESSION['emaillogin'];

if(isset($_POST['logout'])) {
  session_abort();
  session_destroy();
  header('location: ../index.php');
}

if(isset($_POST['ADDTOCART'])) {
  $idplant = $_POST['ADDTOCART'];
  $PANIER->Add_to_cart($userid,$idplant);
}

if(isset($_POST['clear'])) {
  $PANIER->delete_ALL_FROM_cart($userid);
}


// returnin total price
$total_price = $PANIER->calculateTotalAmount($userid);

if(isset($_POST['order'])) {
  $PANIER->order_ALL($userid);
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="shortcut icon" href="../assets/imgs/logoG.png" type="image/x-icon">

  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/home.css">

  <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
      integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    >

  <title>Home </title>
</head>
<body>
    
<header class="header" id="header">
    <nav class="nav container">
      <a href="#" class="nav__logo">
        <img src="../assets/imgs/logoG.png" alt="logo">
      </a>
      <div class="search">
        <form method="POST">
          <input name="plant_name" type="text" placeholder="Search here">
          <i class="ri-search-2-line"></i>
        </form>
      </div>
      <div class="nav__menu" id="nav-menu">
        <ul class="nav__list">
          <li class="nav__item">
            <a href="#home" class="nav__link active-link">Home</a>
          </li>
          <li class="nav__item">
            <a href="#products" class="nav__link">Products</a>
          </li>
          <!-- shopping cart -->
          <li>
            <a href="./blog.php" class="button--flex navbar__button">
              <i class="fa-brands fa-hive"></i>
            </a>
          </li>
          <li>
            <button class="button--flex navbar__button cart-open">
              <i class="ri-shopping-bag-line"></i>
            </button>
          </li>
          <!-- log out -->
          <li>
            <form method="post">
              <button type="submit" name="logout" class="button--flex navbar__button">
                <i class="ri-logout-box-r-line"></i>
              </button>
            </form>
          </li>
        </ul>


      </div>
    </nav>




    <div class="popup-container">
      <div class="cart_popup">
        <button class="cart-close">
          <i class="ri-close-circle-line"></i>
        </button>
        <img class="logo" src="../assets/imgs/logoW.png" alt="">
        <h1>Your Plants</h1>
        <ul class="cartItemsList">
          <?php
          $panier_products = $PANIER->get_ALL_panier_products($userid);
          foreach ($panier_products as $cartItem) { ?>
            <li>
              <div class="pic">
                <img src="../assets/imgs/<?php echo $cartItem["plant_img"]; ?>" alt="">
              </div>
              <div class="info">
                <p><?php echo $cartItem["plant_name"]; ?></p>
              </div>
              <div class="price">
                <p><?php echo $cartItem["plant_price"] ?>$</p>
              </div>
              <div class="removePlant">
                <p><?php echo $cartItem["quantity"]; ?></p> 
              </div>
            </li>
          <?php

          }
          ?>

        </ul>
        <div class="check-out">
          <div class="total"><?php echo $total_price ?> $</div>

          <form method="post">
            <button type="submit" name="clear" class="clear">Clear All</button>
            <button name="order" class="check">Check Out</button>
          </form>

        </div>
      </div>
    </div>
  </header>



<!-- AFFICHAGE DU PRODUCTS -->
<div class="main">

<section class="home" id="home">
      <div class="home__container container grid">
        <img src="../assets/imgs/home.png" alt="" class="home__img">

        <div class="home__data">
          <h1 class="home__title">
            Plants will make <br> your life better
          </h1>
          <p class="home__description">
            Create incredible plant design for your offices or apastaments.
            Add fresness to your new ideas.
          </p>
          <a href="#products" class="button button--flex">
            Explore <i class="ri-arrow-right-down-line button__icon"></i>
          </a>
        </div>


      </div>
    </section>
    
</div>


<section class="product section container" id="products">

<!--filter name-->
<h2 class="section__title-center">
        Check out our products
      </h2>

            <!--  filter category here -->

      <form  method="post" class="select-container">
        <select class="form-select" name="category_id" id="category">
          <option value="ALL" name="ALL" selected>Plant Category</option>
          <?php
          $categoryname = $CATEGORY->get_ALL_categories();
          foreach ($categoryname as $category) {
            ?>
             <option name="category_id" value="<?php echo $category["category_id"]; ?>">
              <?php echo $category["category_name"]; ?>
            </option>
            <?php
          }
          
          ?>
        </select>
        <div class="icon-container">
          <i class="ri-arrow-down-s-fill"></i>
        </div>
        <button class="select-btn" name="filter-category" type="submit">Filter</button>
      </form>

      <!-- filter ategory up -->


<div class="product__container grid">
<?php
if(isset($_POST['filter-category']) && $_POST['category_id'] != 'ALL') {
  $categoryid = $_POST['category_id'];
  $plantbycategory = $PLANTS->filter_plant_by_category($categoryid);
  foreach($plantbycategory as $plantt) {
    ?>
    <article class="product__card">
    <div class="product__circle"></div>
    
    <img src="../assets/imgs/<?php echo $plantt["plant_img"]; ?>" alt="" class="product__img">
    
    <h3 class="product__title"><?php echo $plantt["plant_name"]; ?></h3>
    <span class="product__price"><?php echo $plantt["plant_price"]; ?>$</span>
    <form method="post">
    <button name="ADDTOCART" value="<?php echo $plantt['plant_id']?>" type="submit" class="button--flex product__button">
    <i class="ri-shopping-bag-line"></i>
    </button>
    </form>
    </article>
    <?php
}
}
elseif(isset($_POST['plant_name'])) {
      $plant_name = $_POST['plant_name'];
      $plantt = $PLANTS->recherche_plant_by_name($plant_name);
      foreach($plantt as $plantt) {
?>
<article class="product__card">
    <div class="product__circle"></div>
    
    <img src="../assets/imgs/<?php echo $plantt["plant_img"]; ?>" alt="" class="product__img">
    
    <h3 class="product__title"><?php echo $plantt["plant_name"]; ?></h3>
    <span class="product__price"><?php echo $plantt["plant_price"]; ?>$</span>
    <form method="post">
    <button name="ADDTOCART" value="<?php echo $plantt['plant_id']?>" type="submit" class="button--flex product__button">
    <i class="ri-shopping-bag-line"></i>
    </button>
    </form>
    </article>
<?php
      
    }
  }
  else {
    $row = $PLANTS->retrieve_products();
foreach($row as $plant) {
    ?>
    <article class="product__card">
    <div class="product__circle"></div>
    
    <img src="../assets/imgs/<?php echo $plant["plant_img"]; ?>" alt="" class="product__img">
    
    <h3 class="product__title"><?php echo $plant["plant_name"]; ?></h3>
    <span class="product__price"><?php echo $plant["plant_price"]; ?>$</span>
    <form method="post">
    <button name="ADDTOCART" value="<?php echo $plant['plant_id']?>" type="submit" class="button--flex product__button">
    <i class="ri-shopping-bag-line"></i>
    </button>
    </form>
    </article>
    <?php
    }
  }
?>
</div>
</section>





<!--FOOTERR -->

<footer class="footer section">
    <div class="footer__container container grid">
      <div class="footer__content">
        <a href="#" class="nav__logo">
          <img src="./assets/imgs/logoG.png" alt="">
        </a>




      </div>

      <div class="footer__content">
        <h3 class="footer__title">Our Address</h3>

        <ul class="footer__data">
          <li class="footer__information">1234 - Safi</li>
          <li class="footer__information">P2306</li>

        </ul>
      </div>

      <div class="footer__content">
        <h3 class="footer__title">Contact Us</h3>

        <ul class="footer__data">
          <li class="footer__information">0617196324</li>

          <div class="footer__social">
            <a href="https://www.facebook.com/" class="footer__social-link">
              <i class="ri-facebook-fill"></i>
            </a>
            <a href="https://www.instagram.com/" class="footer__social-link">
              <i class="ri-instagram-line"></i>
            </a>
            <a href="https://twitter.com/" class="footer__social-link">
              <i class="ri-twitter-fill"></i>
            </a>
          </div>
        </ul>
      </div>
    </div>


  </footer>

<script>
  document.querySelector('.check').addEventListener('click' , function () {
    location.reload();
  })
</script>
<script src="../assets/js/home.js"></script>
</body>
</html>