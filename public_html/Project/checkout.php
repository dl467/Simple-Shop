<?php
require_once(__DIR__ . "/../../partials/nav.php");
require_once(__DIR__ . "/../../lib/functions.php");

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


if (isset($_POST["purchase"])) {
    $address = se($_POST, "street", "", false) . ", " . se($_POST, "city", "", false) . ", " . se($_POST, "zip", "", false) . ", " . se($_POST, "state", "", false);
    $payment_method = se($_POST, "paymentMethod", "", false);
    $total_price = se($_POST, "totalPrice", "", false);
    $user_id = get_user_id();

   if ($total_price == $total && isset($address) && isset($payment_method)){
        $order_id = purchase($user_id, $total_price, $payment_method, $address);
        flash("Purchase Cart Complete", "success");
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO OrderItems(order_id, product_id, quantity, unit_price) SELECT :order_id, product_id, quantity, unit_cost FROM Cart WHERE user_id = :uid");
        try{
            $stmt->execute([":order_id" => $order_id, ":uid" => get_user_id()]);
        } catch (PDOException $e) {
            error_log("Error adding items to OrderItems" . var_export($e->errorInfo, true));
        }    

        /*
        $db2 = getDB();
        $stmt2 = $db2->prepare("DELETE FROM Cart WHERE user_id = :uid");
        try{
        $stmt2->execute([":uid" => get_user_id()]);
        } catch (PDOException $e) {
        error_log("Error deleting cart from checkout" . var_export($e->errorInfo, true));
        }    
        */

    //redirect("confirm.php");
    }
   else{
        flash("Missing Inputs or Confirm Total does not match total cost", "warning");
   }

}


?>

<div class="container-fluid">
    <h1>Checkout</h1>
    <br>
    <h5>Cart</h5>
    <?php if (count($results) == 0) : ?>
        <p>No added items in cart</p>
    <?php else : ?>
        <table class="table">
        <?php foreach ($results as $index => $record) : //echo("<pre>" . var_export($record, true) . "</pre>");?>
                <?php if ($index == 0) : ?>
                    <thead style="text-align:center">
                        <?php foreach ($record as $column => $value) : //echo("<pre>" . var_export($column, true) . "</pre>");?>
                            <th><?php se($column); ?></th>
                        <?php endforeach; ?>
                    </thead>
                <?php endif; ?>
                <tr style="text-align:center">
                    <?php foreach ($record as $column => $value) : //echo("<pre>" . var_export($column, true) . "</pre>");?>
                        <td><?php se($value, null, "N/A"); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            <thead style="text-align:center">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total Cost: $<?php echo $total;?> </th>
            </thead>
        </table>
    <?php endif; ?>
</div>

<div class="container-fluid">
<br>
<h5>Shipping Address</h5>
    <form method="POST">
    <div class="form-row">
        <!--
            <div class="form-group col-md-3">
            <label for="FirstName">First Name</label>
            <input type="text" class="form-control" id="FirstName" placeholder="Joe">
            </div>
            <div class="form-group col-md-3">
            <label for="LastName">Last Name</label>
            <input type="text" class="form-control" id="LastName" placeholder="Mama">
            </div>
        -->

    </div>
    <div class="form-group col-md-3">
            <label for="inputAddress">Address</label>
            <input type="text" class="form-control" name="street" id="street" placeholder="542 W. 15th Street">
    </div>
    <div class="form-group col-md-2">
            <label for="inputAddress">City</label>
            <input type="text" class="form-control" name="city" id="city" placeholder="Fort Lee">
    </div>
    <div class="form-group col-md-1">
            <label for="inputAddress">Zip</label>
            <input type="text" class="form-control" name="zip" id="zip" placeholder="07123">
    
            <label for="inputAddress">State</label>
            <input type="text" class="form-control" name="state" id="state" placeholder="NJ">
    </div>
    <br>
    <div class="form-row">
    <h5>Payment</h5>
        <div class="form-group col-md-2">
            <label for="paymentMethod">Payment Method</label>
            <input type="text" class="form-control" name="paymentMethod" id="paymentMethod" placeholder="Cash, Visa, MasterCard, Amex, etc">
        </div>
        <div class="form-group col-md-1">
            <label for="confirm">Confrim Total</label>
            <input type="number" class="form-control" name="totalPrice" id="totalPrice" placeholder="Cart Total">
        
            <br> 
            <input class="btn btn-primary" type="submit" name="purchase" value="Purchase"/>
        </div>
    </div>
    </form>
</div>


<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>