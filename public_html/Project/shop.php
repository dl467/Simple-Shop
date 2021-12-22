<?php
require_once(__DIR__ . "/../../partials/nav.php");

$results = [];
$db = getDB();
//Sort and Filters
$col = se($_GET, "col", "cost", false);
//allowed list
if (!in_array($col, ["cost", "stock", "name", "created", "rating"])) {
    $col = "cost"; //default value, prevent sql injection
}
$order = se($_GET, "order", "asc", false);
//allowed list
if (!in_array($order, ["asc", "desc"])) {
    $order = "asc"; //default value, prevent sql injection
}
$name = se($_GET, "name", "", false);

//split query into data and total
$base_query = "SELECT id, name, description, category, cost, stock, image, average_rating FROM Products";
$total_query = "SELECT count(1) as total FROM Products";
//$query = "SELECT id, name, description, category, cost, stock, image FROM Products WHERE 1=1 AND stock > 0 AND visibility =1";
//dynamic query
$query = " WHERE 1=1 AND stock > 0 AND visibility =1"; //1=1 shortcut to conditionally build AND clauses
$params = []; //define default params, add keys as needed and pass to execute

//apply category filter
function get_categories(){
    $db = getDB();
    $stmt = $db->prepare("SELECT DISTINCT category FROM Products");
    $cats = [];
    try{
      $stmt->execute();
      $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if($r){
        $cats = $r;
      }
    }
    catch(PDOException $e){
      error_log("Category lookup error: " . var_export($e, true));
    }
    return $cats;
  }

$category = se($_GET, "category_filter", "", false);
//apply category filter  
if (!empty($category)) {
    $query .= " AND category = :category";
    $params[":category"] = $category;
}

//apply name filter
if (!empty($name)) {
    $query .= " AND name like :name";
    $params[":name"] = "%$name%";
}

//apply column and order sort
if (!empty($col) && !empty($order)) {
    $query .= " ORDER BY $col $order"; //be sure you trust these values, I validate via the in_array checks above
}
//paginate function
$per_page = 10;
paginate($total_query . $query, $params, $per_page);
//get the total

$query .= " LIMIT :offset, :count";
$params[":offset"] = $offset;
$params[":count"] = $per_page;
//get the records
error_log($base_query . $query);
$stmt = $db->prepare($base_query . $query); //dynamically generated query
//we'll want to convert this to use bindValue so ensure they're integers so lets map our array
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$params = null; //set it to null to avoid issues


//$stmt = $db->prepare("SELECT id, name, description, cost, stock, image FROM Products WHERE stock > 0 LIMIT 50");
try {
    $stmt->execute($params); //dynamically populated params to bind
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("results: " . var_export($r, true));
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
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
<form class="row row-cols-auto g-4 align-items-center">
        <div class="col">
            <div class="input-group">
                <div class="input-group-text">Name</div>
                <input class="form-control" name="name" value="<?php se($name); ?>" />
            </div>
        </div>
        <div class="col">
            <div class="input-group">
                <div class="input-group-text">Sort</div>
                <!-- make sure these match the in_array filter above-->
                <select class="form-control" name="col" value="<?php se($col); ?>">
                    <option value="cost">Cost</option>
                    <option value="stock">Stock</option>
                    <option value="name">Name</option>
                    <option value="created">Created</option>
                    <option value="rating">Rating</option>
                </select>
                <select class="form-control" name="category_filter">
                    <option value="">All Categories</option>
                    <?php foreach (get_categories() as $cat): ?>
                        <option require value="<?php se($cat,'category');?>"><?php  se($cat, "category") ?></option>
                        
                    <?php endforeach;?>
                </select>

                <script>
                    document.forms[0].col.value = "<?php se($col); ?>";
                    document.forms[0].category_filter.value = "<?php se($category, "category"); ?>";
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
    <h1>Shop</h1>
    <br>
    <div class="row row-cols-1 row-cols-md-5 g-4">
        <?php foreach ($results as $item): ?>
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Product: <?php se($item, "name"); ?></h5>
                        </div>
                        <?php if (se($item, "image", "", false)) : ?>
                            <img src="<?php se($item, "image"); ?>" class="card-img-top" alt="...">
                        <?php endif; ?>

                        <div class="card-body">
                            <p class="card-text">Category: <?php se($item, "category"); ?></p>
                            <p class="card-text">Description: <?php se($item, "description"); ?></p>
                            <a href="product_info.php?id=<?php se($item, "id"); ?>">Detail</a>
                            &ensp;&ensp;
                            <?php if (has_role("Admin")) : ?>
                                <a href="admin/edit_item.php?id=<?php se($item, "id"); ?>">Edit</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            Cost: <?php se($item, "cost"); ?> &ensp; Stock: <?php se($item, "stock"); ?>
                            <button onclick="addToCart('<?php se($item, 'id'); ?> ',' <?php se($item, 'cost'); ?>')" class="btn btn-primary">Add to Cart</button>
                        </div>
                    </div>
                </div>       
        <?php endforeach; ?>
    </div>
    <br>
    <?php include(__DIR__ . "/../../partials/pagination.php"); ?>
</div>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>