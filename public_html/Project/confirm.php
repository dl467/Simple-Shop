<?php
require_once(__DIR__ . "/../../partials/nav.php");

if(!is_logged_in()){
    flash("You must login to view confirm", "warning");
    redirect("login.php");
}


if (!isset($user_id)) {
    $user_id = get_user_id();
}

$db = getDB();
$total_price = $_SESSION["total"];

$stmt = $db->prepare("SELECT id FROM Orders WHERE user_id=:uid and total_price=:tp");
try{
    $stmt->execute([":uid" => $user_id, ":tp" => $total_price]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log(var_export($e, true));
}
//echo "<pre>" . var_export($results, true).  "</pre>";

foreach($results as $h){
    $order_id = se($h, "id", 0, false);
}

$db = getDB();
$stmt = $db->prepare("SELECT product_id, quantity, unit_price FROM OrderItems WHERE order_id=:oi");
try{
    $stmt->execute([":oi" => $order_id]);
    $order_item_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(var_export($e, true));
}
//echo "<pre>" . var_export($order_item_products, true).  "</pre>";

?>
<div class="container-fluid">
    <h1>Confirmation Page</h1>
    <br>
    <h3 style="text-align:center">Thank You For Your Purchase</h3>
    <h5>Ordered Items</h5>
    <table class="table">
        <?php foreach ($order_item_products as $index => $record) : //echo("<pre>" . var_export($record, true) . "</pre>");?>
                <?php if ($index == 0) : ?>
                    <thead style="text-align:center">
                        <?php foreach ($record as $column => $value) : //echo("<pre>" . var_export($value, true) . "</pre>");?>
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
                <tr></tr>
                <tr><th colspan="100%">Total Cost: $<?php se($_SESSION, "total", 0);?></th></tr>
                <tr></tr>
            </thead>
        </table>
</div>


<?php
unset($_SESSION["total"]);
require_once(__DIR__ . "/../../partials/flash.php");
?>