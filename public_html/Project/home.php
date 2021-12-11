<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")){

$db = getDB();

$stmt = $db->prepare("SELECT id, total_price, payment_method, address, created FROM Orders WHERE user_id=:uid LIMIT 10");
try{
    $stmt->execute([":uid" => get_user_id()]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log(var_export($e, true));
}//echo "<pre>" . var_export($results, true).  "</pre>";
}

if (has_role("Admin")){
    $db = getDB();

    $stmt = $db->prepare("SELECT id, total_price, payment_method, address, created FROM Orders LIMIT 10");
    try{
        $stmt->execute([]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    } catch (PDOException $e) {
        error_log(var_export($e, true));
    }//echo "<pre>" . var_export($results, true).  "</pre>";
}
?>

<div class="container-fluid">
    <h1>Home</h1>
</div>

<div class="container-fluid">
    <?php
    if (is_logged_in(true)) {
        echo "Welcome home, " . get_username();
        //comment this out if you don't want to see the session variables
        //echo "<pre>" . var_export($_SESSION, true) . "</pre>"; 
    }
    
    ?> 
</div>

    <div class="card-body">
        <div class="card-title">
            <div class="fw-bold fs-3">
                <h4>Past Orders</h4>
            </div>
        </div>
        <div class="card-text">
            <table class="table ">
                <thead style="text-align:center">
                    <th>Order Id</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Actions</th>
                </thead>
                <tbody>
                    <?php if (!$results || count($results) == 0) : ?>
                        <tr>
                            <td colspan="100%">No Orders Made</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($results as $result) : ?>
                            <tr style="text-align:center">
                                <td><?php se($result, "id");?></td>
                                <td>$<?php se($result, "total_price"); ?></td>
                                <td><?php se($result, "payment_method"); ?></td>
                                <td><?php se($result, "address"); ?></td>
                                <td><?php se($result, "created"); ?></td>
                                <td><a href="view_orders.php?id=<?php se($result, "id"); ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>