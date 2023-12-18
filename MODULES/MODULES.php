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



        public function __construct() {
            $this->db = DATABASE::getconnection();
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



        public function __construct() {
            $this->db = DATABASE::getconnection();
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
                $this->category_id = $row;
                return $$this->category_id;
            }
            else {
                return "ERROR IN Get_category FUNCTION";
            }
        }

        public function modify_category ($categoryname,$categoryid) {
            $update = $this->db->Prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
            $update->execute([$categoryname,$categoryid]);
        }

        public function get_ALL_categories () {
            $select = $this->db->prepare("SELECT * FROM category");
            $select->execute();
            $result = $select->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
    }




    class PRODUCTS {

        private $db;


    public function __construct() {
            $this->db = DATABASE::getconnection();
        }



        public function retrieve_products () { 
            $select = $this->db->prepare("SELECT * FROM plants");
            $select->execute();
            $row = $select->fetchALL(PDO::FETCH_ASSOC);
            return $row;
    }


    public function recherche_plant_by_name ($name) {
        $select = $this->db->prepare("SELECT * FROM plants WHERE plant_name LIKE ?");
        $select->execute(["%$name%"]);
        $rows = $select->fetchALL(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function filter_plant_by_category ($categoryID) {
        $category_plant = $this->db->prepare("SELECT * FROM plants JOIN category ON category.category_id = plants.category_id WHERE category.category_id = ?");
        $category_plant->execute([$categoryID]);
        $result = $category_plant->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}


class panier {
    private $db;

    public function __construct() {
        $this->db = DATABASE::getconnection();
    }

    function Add_to_cart ($userID, $plantID) {
        try {
            $select = $this->db->prepare("INSERT INTO cart (user_id) VALUES (?)");
            $select->execute([$userID]);
            if ($idcart = $this->db->lastInsertId()) {
                $insert = $this->db->prepare("INSERT INTO cart_items (cart_id, plant_id) VALUES (?, ?)");
                $insert->execute([$idcart, $plantID]);
            } else {
                echo "Failed to get cart ID";
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

    public function order_ALL ($user) {
            $cart = $this->get_ALL_panier_products($user);
            $totalprice =$this->calculateTotalAmount($user);
         if(count($cart) > 0) {
            $insertOrderQuery = $this->db->prepare( "INSERT INTO orders (user_id, total_amount, cart_item_id) VALUES (?, ?, ?)");
            foreach($cart as $cartitem) {
                $cartItemID = $cartitem['cartitem_id'];
                $insertOrderQuery->execute([$user,$totalprice,$cartItemID]);
            }
            $this->delete_ALL_FROM_cart($user);
         }
        }
        


    function get_ALL_panier_products ($userid) {
        $select = $this->db->prepare("SELECT * FROM plants p JOIN cart_items ci ON p.plant_id = ci.plant_id JOIN cart c ON c.cart_id = ci.cart_id JOIN users u ON u.user_id = c.user_id WHERE c.user_id = ? AND status = 'PENDING'");
        $select->execute([$userid]);
        $result = $select->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function calculateTotalAmount ($user) {
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
    private $theme_image;

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
        $articles = $this->db->prepare("SELECT * FROM article WHERE article_id = '$articleID'");
        $articles->execute([$articleID]);
        $result = $articles->fetchAll(PDO::FETCH_ASSOC);
        return $result;
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
        WHERE article_tag.tag_id IN (?)");
        $filter->execute([$placeholders]);
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
    $data = [
        "row_count" => $rows,
        "fetched_result" => $result
    ];
    return $data;
    }
}

class Tag { 
    private $db;

    public function __construct() {
        $this->db = DATABASE::getconnection();
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
}

class comments {
    private $db;

        public function __construct() {
            $this->db = DATABASE::getconnection();
        }

        public function load($articleID) {
            $comment = $this->db->prepare('SELECT * FROM comments WHERE  article_id = ? AND comment_status = "COMMENTED"');
            $comment->execute([$articleID]);
            $result = $comment->fetch(PDO::FETCH_ASSOC);
            return $result;
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