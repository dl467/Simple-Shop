<?php
require(__DIR__."/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
if(is_logged_in(true)) {
 echo "Welcome, " . get_username(); 

 echo "<pre>" . var_export($_SESSION, true) . "</pre>";

}
else{
  echo "You're not logged in";
}
?>