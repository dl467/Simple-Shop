<?php
require(__DIR__ . "/../../partials/nav.php");

if(!is_logged_in()){
    flash("You must login to view orders", "warning");
    redirect("login.php");
}


$id = se($_GET, "id", -1, false);
$db = getDB();
$stmt = $db->prepare("SELECT product_id, quantity, unit_price FROM OrderItems WHERE order_id=:oi");
try{
    $stmt->execute([":oi" => $id]);
    $order_item_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(var_export($e, true));
}

$count = 0;
foreach($order_item_products as $s){
    $count = $count + (int)se($s, "unit_price", 0 ,false);
}
//echo("<pre>" . var_export($count, true) . "</pre>");

?>


<div class="container-fluid">
    <h1>View Order</h1>
    <br>
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
                    <?php foreach ($record as $column => $value) : //echo("<pre>" . var_export($value, true) . "</pre>");?>
                        <td><?php se($value, null, "N/A"); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            <thead style="text-align:center">
                <tr></tr>
                <tr><th colspan="100%">Total Cost: $<?php se($count)?></th></tr>
                <tr></tr>
            </thead>
        </table>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>