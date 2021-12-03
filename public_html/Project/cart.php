<?php
require(__DIR__ . "/../../partials/nav.php");
if(!is_logged_in()){
    flash("You must login to view Cart", "warning");
    die(header("Location: login.php"));
}

if (isset($_POST["cart_id"])) {
    $db = getDB();
    $insert_quantity = se($_POST,'quantity', -1, false);
    //id is a specific line item
    //if you tried to use item_id or product_id it'd update the quantity of every user's product
    $cart_id = se($_POST, "cart_id", -1, false);
    error_log("Updating quantity to $insert_quantity for cart item $cart_id");
    $stmt = $db->prepare("UPDATE Cart set quantity = :q WHERE product_id = :id AND user_id = :uid");
    try {
        $stmt->execute([":q" => $insert_quantity, ":id" => $cart_id, ":uid" => get_user_id()]);
        flash("Updated Quantity", "success");
    } catch (PDOException $e) {
        error_log("Error updating quantity" . var_export($e->errorInfo, true));
    }
}

/*
if (isset($_GET["total"])) {

    $db = getDB();
    $stmt = $db->prepare("SELECT unit_cost FROM Cart where user_id = :uid");
    try{
        $stmt->execute([":uid" => get_user_id()]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $results = $item;
        }
    } catch (PDOException $e) {
        echo "<pre>" . var_export($e, true) . "</pre>";
    }
}
*/

if (isset($_GET["remove_item"])){
    $db = getDB();
    $item_id = se($_GET, "remove_item", -1, false);
    $stmt = $db->prepare("DELETE FROM Cart WHERE product_id = :id AND user_id = :uid");
    try {
        $stmt->execute([":id" => $item_id, ":uid" => get_user_id()]);
        flash("Removed Item", "success");

    } catch (PDOException $e) {
        error_log("Error removing item" . var_export($e->errorInfo, true));
    }
    
}

if (isset($_GET["remove_all"])) {
    $db = getDB();
    $item_id = se($_GET, "remove_item", -1, false);
    $stmt = $db->prepare("DELETE FROM Cart WHERE user_id = :uid");
    try{
        $stmt->execute([":uid" => get_user_id()]);
        flash("Removed All Items", "success");
    } catch (PDOException $e) {
        error_log("Error removing all items" . var_export($e->errorInfo, true));
    }
}

$db = getDB();
$results = [];

if (!isset($user_id)) {
    $user_id = get_user_id();
}
error_log("cart");
$stmt = $db->prepare("SELECT i.id, name, cost, quantity, description, (quantity * cost) AS subtotal FROM Cart inv JOIN Products i on inv.product_id = i.id WHERE inv.user_id = :uid and quantity > 0");
try {
    $total = 0;
    $stmt->execute([":uid" => $user_id]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
    //echo "<pre>" . var_export($results, true).  "</pre>";

    foreach($r as $each){
        //echo "<pre>" . var_export($each["subtotal"], true) .  "</pre>";
        $total = $total + ((int)$each["subtotal"]);
    }
    //echo "<pre>" . var_export($total, true) .  "</pre>";
} catch (PDOException $e) {
    error_log(var_export($e, true));
    flash("<pre>" . var_export($e, true) . "</pre>");
}

?>

<div class="container-fluid">
    <h5>Cart</h5>
    <br>
        <div class="row">
        <?php foreach ($results as $r) : //echo("<pre>" . var_export($r, true) . "</pre>"); ?>
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <div class="card-text">Product: <?php se($r, "name"); ?></div>
                    </div>
                    <div class="card-body">
                        <div class="card-text">Cost: $<?php se($r, "cost"); ?></div>
                        <div class="card-text">Description: <?php se($r, "description"); ?></div>
                        <br>
                        <div class="input-group">
                        <form method="POST">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Quantity:</span>
                                </div>
                                <input type="hidden" name="cart_id" value="<?php se($r, 'id');?>"/>
                                <input type="number" name="quantity" value="<?php se($r, 'quantity');?>"/>
                                <input class="btn btn-primary" type="submit" value="Update"/>
                            </div>
                        </form>
                        </div>
                        <a href="product_info.php?id=<?php se($r, "id"); ?>">Detail</a>
                        &ensp;&ensp;
                        <?php if (has_role("Admin")) : ?>
                                <a href="admin/edit_item.php?id=<?php se($r, "id"); ?>">Edit</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                    <div class="card-text">Subtotal: $<?php se($r, "subtotal"); ?> </div>
                    
                    <form method="GET">
                        <input type="hidden" name="remove_item" value="<?php se($r, 'id'); ?>"/>
                        <input class="btn btn-primary" type="submit" value="Remove"/>
                    </form>   
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <br>
</div>
<div class="container-fluid">
    <div class="row g-3">
        <div class="col">
            <div class="card-body"type="number" name="total" style="font-size: 20px;">Total Cost: $<?php echo $total;?></div>
        </div>
        <div class="col">
            <form method="GET">
                <input type="hidden" name="remove_all" value="<?php se($r, 'id'); ?>"/>
                <input class="btn btn-primary" type="submit" style="font-size: 20px;" value="Remove all items"/>
            </form>
        </div>
    </div>
<br><br>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>