<?php
require_once('../MODULES/MODULES.php');

$USERS = new USERS();

if(isset($_SESSION['created_user_id'])) {
    $userID = $_SESSION['created_user_id'];
}

if(isset($_POST['updateRole'])) {
$userrole = $_POST['role_id'];

$USERS->update_role_access($userID,$userrole);
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/role.css">
    <title>RolesPage</title>
</head>

<body>

    <form class="container" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="container">
        <div class="radiocont">
          <div class="form-container user">
          <i class="fas fa-user"></i>
            <label for="client">Client</label>
            <input value="1" name="role_id" type="radio">
          </div>
          <div class="form-container admin">
          <i class="fas fa-user-tie"></i>
            <label for="admin">Admin</label>
            <input value="2" name="role_id"  type="radio">
          </div>
      </div>
        <button class="confirm" type="submit" name="updateRole">Confirm</button>
    </form>
        <!-- <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Already have an accont!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Log In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>First time on O'Pep!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div> -->
    </div>

    <script src="./assets/js/script.js"></script>
</body>

</html>