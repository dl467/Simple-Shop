<?php
require(__DIR__ . "/../../partials/nav.php");

$col = se($_GET, "col", "total", false);
if (!in_array($col, ["total", "created"])) {
    $col = "total"; //default value, prevent sql injection
}

$order = se($_GET, "order", "asc", false);
if (!in_array($order, ["asc", "desc"])) {
    $order = "asc"; //default value, prevent sql injection
}


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
$total_history = 0;
foreach ($results as $result){
    $total_history = $result["total_price"] + $total_history;
}
?>
<div class="container-fluid">
<form class="row row-cols-auto g-3 align-items-center">
    <div class="col">
        <div class="input-group">
            <div class="input-group-text">From</div>
            <input class="form-control" name="date_start" value="" />
        </div>
    </div>
    <div class="col">
        <div class="input-group">
            <div class="input-group-text">To</div>
            <input class="form-control" name="date_end" value="" />
        </div>
    </div>
        <div class="col">
            <div class="input-group">
                <div class="input-group-text">Sort</div>
                <!-- make sure these match the in_array filter above-->
                <select class="form-control" name="col" value="<?php se($col); ?>">
                    <option value="total">Total</option>
                    <option value="created">Created</option>
                </select>

                <script>
                    document.forms[0].col.value = "<?php se($col); ?>";
                </script>

                <select class="form-control" name="order" value="<?php se($order); ?>">
                    <option value="asc">Up</option>
                    <option value="desc">Down</option>
                </select>
                <script>
                    document.forms[0].order.value = "<?php se($order); ?>";
                </script>
            </div>
        </div>
        <div class="col">
            <div class="input-group">
                <input type="submit" class="btn btn-primary" value="Apply" />
            </div>
        </div>
    </form>
</div>

<div class="container-fluid">
    <h1>Purchase History</h1>
    <?php if (has_role("Admin")) : ?>
    <h4>Total Price : $<?php echo $total_history?></h4>
    <?php endif; ?>
</div>
    <div class="card-body">
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
        <?php include(__DIR__ . "/../../partials/pagination.php"); ?>
    </div>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>