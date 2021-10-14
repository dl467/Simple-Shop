<?php
session_start();
<<<<<<< HEAD
require(__DIR__ . "/../../lib/functions.php");
reset_session();

=======
session_unset();
session_destroy();
session_start();
require(__DIR__ . "/../../lib/functions.php");
>>>>>>> main
flash("Successfully logged out", "success");
header("Location: login.php");