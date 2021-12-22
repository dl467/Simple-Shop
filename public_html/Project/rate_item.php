<?php
require_once(__DIR__ . "/../../partials/nav.php");

$product_id = se($_GET, "id", -1, false);
$user_id = get_user_id();
$db = getDB();
$isIn = false;

$stmt = $db->prepare("SELECT order_id FROM OrderItems WHERE product_id=:pid");
try{
    $stmt->execute([":pid" => $product_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log(var_export($e, true));
}//echo "<pre>" . var_export($results, true).  "</pre>";

foreach ($results as $result){
    $stmt = $db->prepare("SELECT user_id FROM Orders WHERE id=:id");
    try{
        $stmt->execute([":id" => se($result, "order_id", -1, false)]);
        $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log(var_export($e, true));
    }
    foreach ($userList as $users){
        if(in_array($user_id, $users)){
            $isIn = true;
            break;
        }
    }
}
if (!$isIn){
    flash("User has not purchased the product yet, therefore can't leave a review.", "warning");
}

if (isset($_POST["submit"]) && $isIn){
    $db2 = getDB();
    $ratings = se($_POST, "rating", 0, false);
    $comment = se($_POST, "comment", "", false);

    $stmt2 = $db2->prepare("INSERT INTO Ratings(product_id, user_id, ratings, comment) VALUES (:pid, :uid, :rt, :ct)");
    try{
        $stmt2->execute([":pid" => $product_id, ":uid" => $user_id, ":rt" => $ratings, ":ct" => $comment]);
    } catch (PDOException $e) {
        error_log(var_export($e, true));
    }
    flash("Review has been sent. Thank you for the feedback!", "success");
}
?>

<form method="POST">
    <div class="container-fluid">
            <div class="form-group col-md-1">
                <label class="form-label" for="rating">Rating</label>
                <input class="form-control" type="number" name="rating" id="rating" placeholder="1-5" max=5 min=1 />
            </div>
            <div class="mb-3">
                <label class="form-label" for="comment">Comment</label>
                <textarea class="form-control" type="text" name="comment" id="comment" placeholder="Product is overpriced ..."></textarea>
            </div>
            <input type="submit" class="mt-3 btn btn-primary" value="Submit" name="submit" />
    </div>        
</form>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>