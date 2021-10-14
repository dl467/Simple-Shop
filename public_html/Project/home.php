<?php
require(__DIR__."/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
<<<<<<< HEAD
if(is_logged_in(true)) {
 echo "Welcome, " . get_username(); 

 echo "<pre>" . var_export($_SESSION, true) . "</pre>";

=======
if(is_logged_in()) {
 flash("Welcome, " . get_user_email()); 
>>>>>>> main
}
else{
  flash("You're not logged in");
}
?>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>