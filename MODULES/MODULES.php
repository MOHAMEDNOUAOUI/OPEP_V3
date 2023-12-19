    <?php
  session_start();
  class DATABASE {
    private static $HOST = "127.0.0.1";
    private static $username = "root";
    private static $password = "";
    private static $database = "opepv2";
    private static $connection;

    public static function getconnection() {
        if (!isset(self::$connection)) {
            try {
                self::$connection = new PDO("mysql:host=" . self::$HOST . ";dbname=" . self::$database, self::$username, self::$password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
        }
        return self::$connection;
    }
}


    class USERS {

        private $db;

        private $user_id;
        private $user_name;

        private $user_email;

        private $user_password;

        private $stats;
        
        public function __construct() {
            $this->db = DATABASE::getconnection();
        }

        public function logout () {
            // unset($_SESSION['created_user_id']);
            // unset($_SESSION['emaillogin']);
            session_unset();
            session_destroy();
            header('location: ../index.php');
        }


        public function statistique () {
            $stat = $this->db->query("SELECT COUNT(user_id) as usercount FROM users");
            $result = $stat->fetch(PDO::FETCH_ASSOC);
            $this->stats = $result['usercount'];
            return $this->stats;
        }


        public function checkEmail($email) {
            $select = $this->db->prepare("SELECT * FROM users WHERE user_email = ?");
            $select->execute([$email]);
            $result = $select->fetch(PDO::FETCH_ASSOC);
            return $select->rowCount();
        }

        public function get_user_id($email) {
            $select = $this->db->prepare("SELECT user_id FROM users WHERE user_email = ?");
            $select->execute([$email]);
            $row = $select->fetch(PDO::FETCH_COLUMN);
            return $row;
        }
        

        public function getLastInsertedId() {
            return $this->db->lastInsertId();
        }

        public function register($name, $email, $password) {
            $select = $this->db->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
            
            if ($select) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                if ($select->execute([$name,$email,$hashed_password])) {
                    $_SESSION["created_user_id"] = $this->getLastInsertedId();
                    header("Location: ./MAINPAGES/role.php");
                    exit;
                } else {
                    return false;
                }
            } else {
                echo "ERROR REGISTER";
            }
        }


        function update_role_access($IDuser, $roleid) {
            $SELECT = $this->db->prepare("UPDATE users SET role_id = ? WHERE user_id = ?");
            if($SELECT->execute([$roleid,$IDuser])) {
                header('location: ../index.php');
            }
            else {
                echo "Error updating role";
            }  
        }



        public function login ($roleID) {
                    $role = $roleID;
                    if($role === 1) {
                        header('location: ./MAINPAGES/client.php');
                        exit;
                    }
                    elseif($role === 2) {
                        header('location: ./MAINPAGES/dashboard.php');
                        exit;
                    }
                }


        public function check_login ($email, $passwordinput) {
            $select = $this->db->prepare("SELECT * FROM users WHERE user_email = ?");
            $select->execute([$email]); 
            $result = $select->fetch(PDO::FETCH_ASSOC);
            $rows = $select->rowCount();
            if($rows > 0) {
                $pass = password_verify($passwordinput, $result["user_password"]);
                if($pass) {
                    $roleID = $result['role_id'];
                    $id = $this->get_user_id($email);
                    $_SESSION['emaillogin'] = $id;
                    $_SESSION['idrole'] = $result['role_id'];
                    $this->login($roleID);
                    return true;
                }
                else {
                    echo "INVALID PASSWORD !!!";
                }
                
            }
            else {
                echo "NO ACCOUNT WAS FOUND !!!";
            }


    }


}


    class Category {
        public $db;

        private $categorys;
        private $category_name;
        private $category_id;

        private $stats;



        public function __construct() {
            $this->db = DATABASE::getconnection();
        }


        public function setCategoryId($categoryId) {
            $this->category_id = $categoryId;
        }
        
        public function getCategoryId() {
            return $this->category_id;
        }
        
        public function setCategoryName($categoryName) {
            $this->category_name = $categoryName;
        }
        
        public function getCategoryName() {
            return $this->category_name;
        }


        function add_category_to_DB ($input) {
                $check = $this->db->prepare("SELECT * FROM category WHERE category_name LIKE ?");
                $check->execute([$input]);
                $result = $check->fetch(PDO::FETCH_ASSOC);
                if($check->rowCount() > 0) {
                    return true;
                }
                else {
                    $add = $this->db->prepare("INSERT INTO category (category_name) VALUES (?)");
                     $add->execute([$input]);
                }
        }



        public function statistique () {
            $stat = $this->db->query("SELECT COUNT(category_id) as categorycount FROM category");
            $result = $stat->fetch(PDO::FETCH_ASSOC);
            $this->stats = $result['categorycount'];
            return $this->stats;
        }

        public function Set_category($category_name) {
            $insert = $this->db->Prepare("INSERT INTO category (category_name) VALUES (?)");
            $insert->execute([$category_name]);
        }

        public function Get_category ($categoryID) {
            $select = $this->db->prepare("SELECT * FROM category WHERE category_id = ?");
            $select->execute([$categoryID]);
            $row = $select->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->category_id = $row['category_name'];
                return $this->category_id;
            }
            else {
                return "ERROR IN Get_category FUNCTION";
            }
        }

        public function modify_category () {
            $update = $this->db->Prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
            $update->execute([$this->getCategoryName(),$this->getCategoryId()]);
        }

        public function delete_category() {
            $delete = $this->db->prepare("DELETE FROM category WHERE category_id = ?");
            $delete->execute([$this->getCategoryId()]);
        }

        public function get_ALL_categories () {
            $select = $this->db->prepare("SELECT * FROM category");
            $select->execute();
            $result = $select->fetchAll(PDO::FETCH_ASSOC);
            $categoys = [];
            foreach($result as $row) {
                $category = new Category();
                $category->setCategoryId($row['category_id']);
                $category->setCategoryName($row['category_name']);
                $categorys [] = $category;
            }
            return $categorys;
        }
    }




    class PRODUCTS {

        private $db;

        private $plantName;
        private $plantId;
        private $plantIMG;
        private $plantPrice;
        private $categoryID;
        private $stats;


    public function __construct() {
            $this->db = DATABASE::getconnection();
        }


        public function setPlantName($plantName) {
            $this->plantName = $plantName;
        }
    
        public function getPlantName() {
            return $this->plantName;
        }

        public function setPlantId($plantId) {
            $this->plantId = $plantId;
        }
    
        public function getPlantId() {
            return $this->plantId;
        }
    
        public function setPlantIMG($plantIMG) {
            $this->plantIMG = $plantIMG;
        }
    
        public function getPlantIMG() {
            return $this->plantIMG;
        }
    
        public function setPlantPrice($plantPrice) {
            $this->plantPrice = $plantPrice;
        }
    
        public function getPlantPrice() {
            return $this->plantPrice;
        }
    
        public function setCategoryID($categoryID) {
            $this->categoryID = $categoryID;
        }
    
        public function getCategoryID() {
            return $this->categoryID;
        }
    



        public function statistique () {
            $stat = $this->db->query("SELECT COUNT(plant_id) as plantcount FROM plants");
            $result = $stat->fetch(PDO::FETCH_ASSOC);
            $this->stats = $result['plantcount'];
            return $this->stats;
        }


        public function retrieve_products () { 
            $select = $this->db->query("SELECT * FROM plants");
            $row = $select->fetchALL(PDO::FETCH_ASSOC);

            $plants = [];
            foreach($row as $result) {
                $products = new PRODUCTS();
                $products->setPlantName($result['plant_name']);
                $products->setPlantIMG($result['plant_img']);
                $products->setCategoryID($result['category_id']);
                $products->setPlantPrice($result['plant_price']);
                $products->setPlantId($result['plant_id']);
                $plants[] = $products;
            }
            return $plants;
    }


    public function recherche_plant_by_name() {
        $select = $this->db->prepare("SELECT * FROM plants WHERE plant_name LIKE :name");
        $name = '%' . $this->getPlantName() . '%'; 
        $select->bindValue(':name', $name, PDO::PARAM_STR);
        $select->execute();
        
        $productsArray = []; 
    
        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $products = new PRODUCTS();
            $products->setPlantName($row['plant_name']);
            $products->setPlantIMG($row['plant_img']);
            $products->setCategoryID($row['category_id']);
            $products->setPlantPrice($row['plant_price']);
            $products->setPlantId($row['plant_id']);
    
            $productsArray[] = $products; 
        }
    
        return $productsArray; 
    }
    

    public function filter_plant_by_category ($categoryID) {
        $category_plant = $this->db->prepare("SELECT * FROM plants JOIN category ON category.category_id = plants.category_id WHERE category.category_id = ?");
        $category_plant->execute([$categoryID]);
        $result = $category_plant->fetchAll(PDO::FETCH_ASSOC);
        $plants = [];
        foreach($result as $row) {
            $products = new PRODUCTS();
            $products->setPlantName($row['plant_name']);
            $products->setPlantIMG($row['plant_img']);
            $products->setCategoryID($row['category_id']);
            $products->setPlantPrice($row['plant_price']);
            $products->setPlantId($row['plant_id']);
            $plants [] = $products;
        }
        return $plants;
    }


    public function ADD_PLANT() {
        $plantName = $this->getPlantName();
        if ($plantName !== NULL) {
            $check = $this->recherche_plant_by_name();
            if (!empty($check) && $this->getPlantName() === $plantName) {
                return false; 
            } else {
                $file = $this->getPlantIMG();
                $folder = '../assets/imgs/' . $file;
                $fileTmp = $_FILES['plant_img']['tmp_name'];
    
                $query = $this->db->prepare("INSERT INTO plants(plant_name, plant_img, plant_price, category_id) VALUES(?,?,?,?)");
                $query->execute([$plantName, $file, $this->getPlantPrice(), $this->getCategoryID()]);
    
                if ($query) {
                    move_uploaded_file($fileTmp, $folder);
                    return true; 
                }
            }
        }
        return false; 
    }

    public function deleteP() {
        $plant = $this->getPlantId();
        $query = $this->db->prepare("DELETE FROM plants WHERE plant_id = :plantID");
        $query->bindValue(':plantID' ,$plant,PDO::PARAM_INT);
        $query->execute();
      }
    

}


class panier {
    private $db;

    private $userID;

    private $plantID;

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }

    public function set_userID ($userid) {
        $this->userID = $userid;
    }
    public function get_userID () {
        return $this->userID;
    }

    public function set_plantID ($plantID) {
        $this->plantID = $plantID;
    }

    public function get_plantID () {
        return $this->plantID;
    }

    function Add_to_cart () {
        try {
            $select = $this->db->prepare("INSERT INTO cart (user_id) VALUES (:userID)");
            $select->bindValue(':userID' , $this->get_userID() , PDO::PARAM_INT);
            if ($select->execute()) {
                $idcart = $this->db->lastInsertId();
                $insert = $this->db->prepare("INSERT INTO cart_items (cart_id, plant_id) VALUES (:cartID, :plantID)");
                $insert->bindValue(':cartID' , $idcart, PDO::PARAM_INT);
                $insert->bindValue(':plantID' , $this->get_plantID() , PDO::PARAM_INT);
                    if($insert->execute()) {
                        return true;
                    } 
                    else {
                    echo "Failed to get cart ID";
                }
            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    

    public function delete_ALL_FROM_cart ($user) {
        try {
            $select = $this->db->prepare("UPDATE cart_items ci
            JOIN cart c ON ci.cart_id = c.cart_id
            SET ci.status = 'SOLD'
            WHERE c.user_id = ? AND ci.status = 'PENDING'");
            $select->execute([$user]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function select_cart_items ($userID) {
        $select = $this->db->prepare("SELECT * FROM cart_items JOIN cart ON cart.cart_id = cart_items.cart_id WHERE cart.cart_id = ?");
        $select->execute([$userID]);
        $result = $select->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function get_ALL_panier_products ($userid) {
        $select = $this->db->prepare("SELECT * FROM plants p JOIN cart_items ci ON p.plant_id = ci.plant_id JOIN cart c ON c.cart_id = ci.cart_id JOIN users u ON u.user_id = c.user_id WHERE c.user_id = ? AND status = 'PENDING'");
        $select->execute([$userid]);
        $result = $select->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function  calculateTotalAmount ($user) {
        $query = $this->db->prepare("SELECT SUM(p.plant_price) AS total_amount
            FROM cart_items ci
            JOIN plants p ON ci.plant_id = p.plant_id
            JOIN cart c ON ci.cart_id = c.cart_id
            WHERE c.user_id = ? AND ci.status = 'PENDING'");
    
        $query->execute([$user]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $total_price = $result['total_amount'];
            return $total_price;
        } 
        else {
            $total_price = 0;
            return $total_price;
        }
    }


}


class theme {
    private $db;
    private $theme_id;
    private $theme_name;
    private $theme_img;

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }

    // public function setThemeId($id) {
    //     $this->theme_id = $id;
    // }

    // public function getThemeId() {
    //     return $this->theme_id;
    // }

    // public function setThemeName($name) {
    //     $this->theme_name = $name;
    // }

    // public function getThemeName() {
    //     return $this->theme_name;
    // }

    // public function setThemeImage($image) {
    //     $this->theme_image = $image;
    // }

    // public function getThemeImage() {
    //     return $this->theme_image;
    // }
    public function __get($property){
        return $this->$property;
    }
    public function __set($property, $value) {
        $this->$property = $value;
    }

    public function fetchAllThemes() {
        try {
            $query = "SELECT * FROM theme";
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $themes = [];

            foreach ($result as $row) {
                $theme = new Theme();
                $theme->__set("theme_id", $row["theme_id"]);
                $theme->__set("theme_name", $row["theme_name"]);
                $theme->__set("theme_img", $row["theme_img"]);
                $themes[] = $theme;
            }

            return $themes;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function getThemeNameById($themeId) {
        try {
            $query = "SELECT theme_name FROM theme WHERE theme_id = ?";
            $stat= $this->db->prepare($query);
            $stat->execute([$themeId]);
            $result = $stat->fetch(PDO::FETCH_ASSOC);
            return $result['theme_name'];
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

    public function getThemeIdByName($themeName) {
        try {
            $query = "SELECT theme_id FROM theme WHERE theme_name = ?";
            $stat = $this->db->prepare($query);
            $stat->execute([$themeName]);
            $result = $stat->fetch(PDO::FETCH_ASSOC);
            return $result['theme_id'];
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

    public function setTheme($themeName) {
        try {
            $query = "INSERT INTO theme (theme_name) VALUES (?)";
            $statement = $this->db->prepare($query);
            $statement->execute([$themeName]);
            return true;
        } catch(PDOException $e) {
            return $e->getMessage();
        }
    }

}


class Article {
    private $db;
    private $article_id;
    private $article_title;

    private $article_text;

    private $article_image;

    private $theme_ID;

    private $article_user;

    

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }

    public function __get($property){
        return $this->$property;
    }
    public function __set($property, $value) {
        $this->$property = $value;
    }


    public function update_article($articletitle, $fileContent, $articlemessage, $articleid) {
        try {
            $query = 'UPDATE article SET article_title = :articletitle, article_img = :fileContent, article_text = :articlemessage WHERE article_id = :articleid';
            $stmt = $this->db->prepare($query);
    
            
            $stmt->bindParam(':articletitle', $articletitle);
            $stmt->bindParam(':fileContent', $fileContent);
            $stmt->bindParam(':articlemessage', $articlemessage);
            $stmt->bindParam(':articleid', $articleid);
    
            
            $stmt->execute();
            
           
            if ($stmt->rowCount() > 0) {
                return "Article updated successfully.";
            } else {
                return "No changes made to the article.";
            }
        } catch (PDOException $e) {
            return "Error updating article: " . $e->getMessage();
        }
    }
    
    public function load($themeId) {
        $selec = $this->db->prepare("SELECT * FROM article WHERE theme_ID = ? LIMIT 10");
        $selec->execute([$themeId]);
        $result = $selec->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];

        foreach ($result as $row) {
            $articless = new Article();
            $articless->__set("article_id", $row["article_id"]);
            $articless->__set("article_title",$row["article_title"]);
            $articless->__set("article_image",$row["article_img"]);
            $articless->__set("article_text",$row["article_text"]);
            $articless->__set("article_user",$row["article_user"]);
            $articless->__set("theme_ID",$row["theme_ID"]);
            $articles [] =  $articless;
        }
        return $articles;
    }

    public function load_article_by_id ($articleID) {
        $articles = $this->db->prepare("SELECT * FROM article WHERE article_id = ?");
        $articles->execute([$articleID]);
        $result = $articles->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];

        foreach ($result as $row) {
            $articless = new Article();
            $articless->__set("article_id", $row["article_id"]);
            $articless->__set("article_title",$row["article_title"]);
            $articless->__set("article_image",$row["article_img"]);
            $articless->__set("article_text",$row["article_text"]);
            $articless->__set("article_user",$row["article_user"]);
            $articless->__set("theme_ID",$row["theme_ID"]);
            $articles [] =  $articless;
        }
        return $articles;
    }

    public function Numpages($themeId) {
        $page = $this->db->prepare("SELECT COUNT(article_id) as totalarticle FROM article WHERE theme_ID = ?");
        $page->execute([$themeId]);
        $result = $page->fetch(PDO::FETCH_ASSOC);
        $Numpages = $result['totalarticle'];
        return $Numpages;
    }

    public function search($search,$themeId) {
        $searchfield = $this->db->prepare("SELECT * FROM article WHERE article_title LIKE '%$search%' AND theme_id = ? ");
        $searchfield->execute([$themeId]);
        $result = $searchfield->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];

        foreach ($result as $row) {
            $articless = new Article();
            $articless->__set("article_id", $row["article_id"]);
            $articless->__set("article_title",$row["article_title"]);
            $articless->__set("article_image",$row["article_img"]);
            $articless->__set("article_text",$row["article_text"]);
            $articless->__set("article_user",$row["article_user"]);
            $articless->__set("theme_ID",$row["theme_ID"]);
            $articles [] =  $articless;
        }
        return $articles;
    }

    public function FILTER($ids) {
        $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
        $filter = $this->db->prepare("SELECT DISTINCT article.* FROM article 
        JOIN article_tag ON article_tag.article_id=article.article_id
        JOIN tag ON tag.tag_id = article_tag.tag_id
        WHERE article_tag.tag_id IN ($placeholders)");
        $filter->execute($ids);
        $result = $filter->fetchAll(PDO::FETCH_ASSOC);
        $articles = [];

        foreach ($result as $row) {
            $articless = new Article();
            $articless->__set("article_id", $row["article_id"]);
            $articless->__set("article_title",$row["article_title"]);
            $articless->__set("article_image",$row["article_img"]);
            $articless->__set("article_text",$row["article_text"]);
            $articless->__set("article_user",$row["article_user"]);
            $articless->__set("theme_ID",$row["theme_ID"]);
            $articles [] =  $articless;
        }
        return $articles;
    }

    public function pagination ($page,$themeID) {
        $pagination = ($page - 1) *10;
    
    $select = $this->db->prepare("SELECT * FROM article WHERE theme_id = ? LIMIT ?,10");
    $select->execute([$themeID,$pagination]);
    $result = $select->fetchAll(PDO::FETCH_ASSOC);
    return $result;
    }

    public function Get_ThemID ($articleID) {
        $theme = $this->db->prepare("SELECT theme_ID FROM `article` WHERE article_id = ?");
        $theme->execute([$articleID]);
        $result = $theme->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function check_Article_USER($articleID,$user_id) {
        $selection = $this->db->prepare("SELECT * FROM article,users WHERE  article.article_id = ? AND  users.user_id = ?");
    $selection->execute([$articleID,$user_id]);
    $result = $selection->fetch(PDO::FETCH_ASSOC);
    $rows = $selection->rowCount();
    $articles = [];
        $articless = new Article();
        $articless->__set("article_id", $result["article_id"]);
        $articless->__set("article_title",$result["article_title"]);
        $articless->__set("article_image",$result["article_img"]);
        $articless->__set("article_text",$result["article_text"]);
        $articless->__set("article_user",$result["article_user"]);
        $articless->__set("theme_ID",$result["theme_ID"]);
        $articles [] =  $articless;
    $data = [
        "row_count" => $rows,
        "fetched_result" => $articles,
        "user_c" => $result
    ];
    return $data;
    }
}


class command {
    private $db;

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }




    public function order_ALL ($user) {
        $PANIER = new panier();
        $totalPRICE = $PANIER->calculateTotalAmount($user);
        $ALL_PANIER_PRODUCTS = $PANIER->get_ALL_panier_products($user);
        if(count($ALL_PANIER_PRODUCTS) > 0) {
            $insertOrderQuery = $this->db->prepare("INSERT INTO orders (user_id, total_amount, cart_item_id) VALUES (:userID, :total, :cart_item)");
            foreach ($ALL_PANIER_PRODUCTS as $cartItem) {
                $cartItemID = $cartItem['cartitem_id'];
          
                $insertOrderQuery->bindValue(':userID' , $user , PDO::PARAM_INT);
                $insertOrderQuery->bindValue(':total' , $totalPRICE , PDO::PARAM_INT);
                $insertOrderQuery->bindValue(':cart_item' , $cartItemID , PDO::PARAM_INT);
                $insertOrderQuery->execute();
              }

              $PANIER->delete_ALL_FROM_cart($user);
        }
        else {
            return false;
        }
    }


}

class Tag { 
    private $db;

    private $tagID;
    private $tagName;

    private $themeid;
    private $themename;

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }

    public function setTagID($tagID) {
        $this->tagID = $tagID;
    }

    public function getTagID() {
        return $this->tagID;
    }

    public function setThemeid ($themeid) {
        $this->themeid = $themeid;
    }

    public function getThemeid() {
        return $this->themeid;
    }

    public function setThemename ($themename) {
        $this->themename = $themename;
    }

    public function getThemename () {
        return $this->themename;
    }

    
    public function setTagName($tagName) {
        $this->tagName = $tagName;
    }

    public function getTagName() {
        return $this->tagName;
    }

    public function load($themeId) {
        $tag = $this->db->prepare("SELECT * FROM tag
                JOIN theme_tag ON theme_tag.tag_id = tag.tag_id
                JOIN theme ON theme.theme_id = theme_tag.theme_id
                WHERE theme.theme_id = ?;
                ");
        $tag->execute([$themeId]);
        $result = $tag->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function getALLtags () {
        $tags = $this->db->query("SELECT * FROM tag t JOIN theme_tag tt on t.tag_id = tt.tag_id JOIN theme th ON tt.theme_id = th.theme_id");
        $result = $tags->fetchAll(PDO::FETCH_ASSOC);
        $Tags = [];
        foreach($result as $row) {
            $tags = new Tag();
            $tags->setTagID($row['tag_id']);
            $tags->setTagName($row['tag_name']);
            $tags->setThemename($row['theme_name']);
            $tags->setThemeid($row['theme_id']);
            $Tags [] = $tags;
        }
        return $Tags;
    }
}

class comments {
    private $db;

    private $comment_text;
    private $comment_id;

    private $article_id;

    private $comment_date;

    private $user_id;

        public function __construct() {
            $this->db = DATABASE::getconnection();
        }

        public function __get($property){
            return $this->$property;
        }
        public function __set($property, $value) {
            $this->$property = $value;
        }

        public function load($articleID) {
            $comment = $this->db->prepare('SELECT * FROM comments WHERE  article_id = ? AND comment_status = "COMMENTED"');
            $comment->execute([$articleID]);
            $result = $comment->fetchALL(PDO::FETCH_ASSOC);
            $comments = [];

            foreach($result as $row) {
                $comment = new comments();
                $comment->__set("comment_text",$row['comment_text']);
                $comment->__set("comment_id",$row['comment_id']);
                $comment->__set('article_id',$row['article_id']);
                $comment->__set('comment_date',$row['comment_date']);
                $comment->__set('user_id',$row['user_id']);
                $comments [] =$comment;
            }
            
            return $comments;
        }

        public function fetchcomment_byid($commentID) {
            $selected = $this->db->prepare("SELECT *
                    FROM comments
                    JOIN users ON comments.user_id = users.user_id
                    WHERE comments.comment_id = ?");
                $selected->execute([$commentID]);
            $result = $selected->fetch(PDO::FETCH_ASSOC);
                return $result;
        }
}
    ?>