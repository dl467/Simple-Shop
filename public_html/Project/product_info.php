<?php
require_once(__DIR__ . "/../../lib/functions.php");
require_once(__DIR__ . "/../../partials/nav.php");

$id = se($_GET, "id", 0, false);
$db = getDB();
$stmt = $db->prepare("SELECT id, name, description, category, cost, stock, image FROM Products where id = :id");
try{
    $stmt->execute([":id" => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $results = $item;
    }
} catch (PDOException $e) {
    echo "<pre>" . var_export($e, true) . "</pre>";
}
?>

<script>
    function addToCart(item, cost) {
        console.log("TODO add item to cart", item);
        let example = 1;
        if (example === 1) {
            let http = new XMLHttpRequest();
            http.onreadystatechange = () => {
                if (http.readyState == 4) {
                    if (http.status === 200) {
                        let data = JSON.parse(http.responseText);
                        console.log("received data", data);
                        flash(data.message, "success");
                    }
                    console.log(http);
                }
            }
            http.open("POST", "api/add_cart.php", true);
            let data = {
                product_id: item,
                quantity: 1,
                unit_cost: cost
            }
            let q = Object.keys(data).map(key => key + '=' + data[key]).join('&');
            console.log(q)
            http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            http.send(q);
        } else if (example === 2) {
            let data = new FormData();
            data.append("product_id", item);
            data.append("quantity", 1);
            data.append("unit_cost", cost);
            fetch("api/add_cart.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: data
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                    flash(data.message, "success");
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        } else if (example === 3) {
            $.post("api/add_cart.php", {
                    product_id: item,
                    quantity: 1,
                    unit_cost: cost
                    
                }, (resp, status, xhr) => {
                    console.log(resp, status, xhr);
                    let data = JSON.parse(resp);
                    flash(data.message, "success");
                },
                (xhr, status, error) => {
                    console.log(xhr, status, error);
                });
        }
    }
</script>

<div class="container-fluid">
  <div class="card mb-3" style="max-width: 600px;">
    <div class="row g-0">
        <div class="col-md-4">
            <?php if (se($results, "image", "", false)) : ?>
                <img src="<?php se($results, "image"); ?>" class="card-img-top" alt="...">
            <?php endif; ?>
        </div>
        <div class="col-md-8">
        <div class="card-body">
            <h5 class="card-title">Name: <?php se($results, "name"); ?></h5>
            <p class="card-text">Category: <?php se($results, "category"); ?></p>
            <p class="card-text">Description: <?php se($results, "description"); ?></p>
            <?php if (has_role("Admin")) : ?>
                <a href="admin/edit_item.php?id=<?php se($results, "id"); ?>">Edit</a>
            <?php endif; ?>
        </div>
            <div class="card-footer">
                Cost: <?php se($item, "cost"); ?> &ensp; Stock: <?php se($item, "stock"); ?>&ensp;
                <button onclick="addToCart('<?php se($item, 'id'); ?> ',' <?php se($item, 'cost'); ?>')" class="btn btn-primary">Add to Cart</button>
            </div>
        </div>
    </div>
  </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>