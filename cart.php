<?php

include "./php/config.php";

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
}

if (isset($_POST["plus"])) {
    $key = $_POST["plus"];
    $array = explode(",", $key, 2);
    $pid = $array[0];
    $fid = $array[1];

    $cname = $_SESSION["username"];
    $sql2 = mysqli_query($conn, "SELECT * FROM users WHERE username='$cname'");
    $row = mysqli_fetch_array($sql2);
    $cid = $row["userid"];

    $sql3 = mysqli_query(
        $conn,
        "SELECT * FROM cart WHERE userid='$cid' and farmerid='$fid' and prodid='$pid' and flag=1"
    );
    $row3 = mysqli_fetch_array($sql3);

    $sql4 = mysqli_query(
        $conn,
        "SELECT * FROM myshop WHERE farmerid='$fid' and prodid='$pid'"
    );
    $row4 = mysqli_fetch_array($sql4);

    if ($row4["quantity"] > $row3["cart_quantity"]) {
        $quantity = $row3["cart_quantity"] + 1;
        $sql1 = "UPDATE cart SET cart_quantity='$quantity' WHERE userid='$cid' and farmerid='$fid' and prodid='$pid'";
        mysqli_query($conn, $sql1);
        if (mysqli_error($conn)) {
            echo "Errorcode: " . mysqli_errno($conn);
        }

        header("location: #products");
    } else {
        echo '<script>alert("Sorry! Cannot add more items. Maximum limit reached.")</script>';
    }
}

if (isset($_POST["minus"])) {
    $key = $_POST["minus"];
    $array = explode(",", $key, 2);
    $pid = $array[0];
    $fid = $array[1];
    $cname = $_SESSION["username"];
    $cname = $_SESSION["username"];
    $sql2 = mysqli_query($conn, "SELECT * FROM users WHERE username='$cname'");
    $row = mysqli_fetch_array($sql2);
    $cid = $row["userid"];

    $sql3 = mysqli_query(
        $conn,
        "SELECT * FROM cart WHERE userid='$cid' and farmerid='$fid' and prodid='$pid' and flag=1"
    );
    $row1 = mysqli_fetch_array($sql3);

    $quantity = $row1["cart_quantity"] - 1;

    if ($quantity > 0) {
        $sql1 = "UPDATE cart SET cart_quantity='$quantity' WHERE userid='$cid' and farmerid='$fid' and prodid='$pid'";
        mysqli_query($conn, $sql1);
        if (mysqli_error($conn)) {
            echo "Errorcode: " . mysqli_errno($conn);
        }
    } else {
        $sql1 = "UPDATE cart SET cart_quantity=0,flag=0 WHERE userid='$cid' and farmerid='$fid' and prodid='$pid'";
        mysqli_query($conn, $sql1);
        if (mysqli_error($conn)) {
            echo "Errorcode: " . mysqli_errno($conn);
        }
    }

    header("location: #products");
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Cart Page</title>
  <!-- Meta tags for responsiveness-->
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="HandheldFriendly" content="true">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <!-- Latest compiled JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Popper JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <!-- Stylesheet -->
  <link rel="stylesheet" type="text/css" href="./css/cartstyles1.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    .coupon-badge {
      margin: 5px;
      font-weight: bold;
    }
    .apply-coupon {
      margin-top: 10px;
    }
    .alert {
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a href="customer.php#shop"><button class="btn mr-5" style="background-color: white;"><i
              class="fa fa-chevron-left" aria-hidden="true"></i></button></a>
        <a class="navbar-brand" href="#"><img src="./assets/Logokrishi.png" class="img-responsive"></a>
      </div>
      <div class="navtoggle">
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar" aria-expanded="false"
          aria-controls="navbar">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <div id="navbar" class="collapse navbar-collapse stroke">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link" href="customer.php#shop">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php#myorders">My Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="./php/logout.php">Log Out</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="image">
    <div class="heading">
      <h1 align="center">YOUR CART</h1>
    </div>
    <div class="subhead">
      <p>Order Now!</p>
    </div>
  </div>

  <div id="products" class="table-responsive">
    <table class="table">
      <thead class="table-light">
        <tr>
          <th scope="col">PRODUCTS</th>
          <th scope="col" style="text-align: center;">PRICE (per kg)</th>
          <th scope="col" style="text-align: center;">QUANTITY (kg)</th>
          <th scope="col" style="text-align: center;">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once "./php/config.php";
        $cname = $_SESSION["username"];
        $sql = mysqli_query(
            $conn,
            "SELECT * FROM users WHERE username='$cname'"
        );
        $row = mysqli_fetch_array($sql);
        $cid = $row["userid"];

        // Add error handling and fix the query
        $results = mysqli_query(
            $conn,
            "SELECT DISTINCT c.*, m.price 
             FROM cart c 
             INNER JOIN myshop m ON c.prodid = m.prodid 
             WHERE c.userid = $cid AND c.flag = 1"
        );

        if (!$results) {
            die("Query failed: " . mysqli_error($conn));
        }

        $amount = 0;

        if (mysqli_num_rows($results) == 0) { ?>
        <tr>
          <td class="align-middle" style="text-align: center; width: 190px;"></td>
          <td class="align-middle" colspan="2" style="text-align: center; font-size: 1.3em; font-weight: 600;">Your cart
            is empty, add more items!🛒</td>
          <td class="align-middle" style="text-align: center;"></td>
        </tr>
        <?php }

        $results = mysqli_query(
            $conn,
            "SELECT c.*, m.price as original_price
             FROM cart c 
             JOIN myshop m ON c.prodid = m.prodid AND c.farmerid = m.farmerid 
             WHERE c.userid=$cid AND c.flag=1"
        );

        // Add error handling
        if (!$results) {
            die("Query failed: " . mysqli_error($conn));
        }

        while ($row = mysqli_fetch_array($results)) { ?>
        <tr>
          <?php
          $pid = $row["prodid"];
          $sql1 = mysqli_query(
              $conn,
              "SELECT * FROM product WHERE prodid='$pid'"
          );
          $row1 = mysqli_fetch_array($sql1);
          $prodname = $row1["prodname"];

          $fid = $row["farmerid"];
          $sql2 = mysqli_query(
              $conn,
              "SELECT * FROM myshop WHERE prodid='$pid' and farmerid='$fid'"
          );
          $row2 = mysqli_fetch_array($sql2);
          $price = $row2["price"];

          $total = $price * $row["cart_quantity"];
          $amount = $amount + $total;
          ?>
          <td class="align-middle" width="auto"><img src="./assets/<?php echo $prodname; ?>.png" height="150px"
              width="175px" style="padding: 10px;"><span class="pl-5"
              style="font-size: 1.3em;"><?php echo $prodname; ?></span></td>
          <td class="align-middle" style="text-align: center;">₹<?php echo $price; ?></td>
          <td class="align-middle" style="text-align: center;">
            <form method="post" action="#">
              <span class="minus"><button class="btn btn-secondary modifybutton" name="minus"
                  value='<?php echo $pid, ",", $fid; ?>'>-</button></span>
              <input id="quant" type="text" value="<?php echo $row[
                  "cart_quantity"
              ]; ?>" readonly style="width: 50px; text-align: center; padding: 5px 0px; " />
              <span class="plus"><button class="btn btn-secondary modifybutton" name="plus"
                  value='<?php echo $pid, ",", $fid; ?>'>+</button></span>
            </form>
          </td>
          <td class="align-middle" style="text-align: center;">₹<?php echo $total; ?></td>
        </tr>
        <?php }
        ?>
      </tbody>
      <tfoot>
        <tr>
          <th scope="col" style="height: 60px;"></th>
          <th scope="col" style="text-align: center;"></th>
          <th scope="col" style="text-align: center;"></th>
          <th scope="col" style="text-align: center;"></th>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="container-fluid text-center py-3">
    <div class="row m-0">
      <div class="col-lg-6 pl-5 left">
        <div class="row coupon">
          <div class="col-lg-3 couponi" id="couponicon">
            <img src="./assets/coupon.png" class="img-responsive pl-2" width="100%">
          </div>
          <div class="col-lg-9">
            <div class="available-coupons mb-2">
              <h5>Available Coupons:</h5>
            <?php if ($amount >= 200 && $amount < 300) {
                $sql0 = mysqli_query(
                    $conn,
                    "SELECT * FROM coupon WHERE pricelimit=200"
                );
                $row = mysqli_fetch_array($sql0);
                  echo '<div class="coupon-badge bg-info text-white p-2 d-inline-block rounded">' . $row["couponcode"] . ' - ' . $row["discount"] . '% off</div>';
            } elseif ($amount >= 300 && $amount < 500) {
                $sql0 = mysqli_query(
                    $conn,
                    "SELECT * FROM coupon WHERE pricelimit=300"
                );
                $row = mysqli_fetch_array($sql0);
                  echo '<div class="coupon-badge bg-primary text-white p-2 d-inline-block rounded">' . $row["couponcode"] . ' - ' . $row["discount"] . '% off</div>';
            } elseif ($amount >= 500) {
                $sql0 = mysqli_query(
                    $conn,
                    "SELECT * FROM coupon WHERE pricelimit=500"
                );
                $row = mysqli_fetch_array($sql0);
                  echo '<div class="coupon-badge bg-success text-white p-2 d-inline-block rounded">' . $row["couponcode"] . ' - ' . $row["discount"] . '% off</div>';
            } else {
                  echo "<p class='text-muted'>Spend ₹200 or more to unlock coupons!</p>";
              } ?>
            </div>
            
            <div class="apply-coupon">
              <form method="post" action="#discount" class="form-inline">
                <input type="text" name="coupon_code" class="form-control mr-2" placeholder="Enter coupon code" 
                  <?php if ($amount < 200) echo "disabled"; ?>>
                <button type="submit" name="applycoupon" value="<?php echo $amount; ?>" class="btn btn-dark"
                  <?php if ($amount < 200) echo "disabled"; ?>>
                  Apply
                </button>
              </form>
            </div>
          </div>
        </div>

        <?php if ($amount >= 200 && !isset($_POST["applycoupon"])) { ?>
        <div class="row mt-2">
          <div class="col-lg-12">
            <div class="alert alert-info">
              <small><i class="fa fa-info-circle"></i> Use coupon codes for discounts: KRISHI200 (10%), KRISHI300 (15%), KRISHI500 (20%)</small>
            </div>
          </div>
        </div>
        <?php } elseif (isset($_POST["applycoupon"]) && isset($_POST["coupon_code"])) {
            $entered_code = $_POST["coupon_code"];
            $valid_coupon = false;
            $discount_amount = 0;
            $original_amount = $amount; // Store original amount
            
            // Check if entered coupon is valid
            $coupon_query = mysqli_query($conn, "SELECT * FROM coupon WHERE couponcode='$entered_code'");
            if ($coupon_row = mysqli_fetch_array($coupon_query)) {
                // Check if amount meets the coupon criteria
                if ($amount >= $coupon_row["pricelimit"] && 
                    (($amount >= 200 && $amount < 300 && $coupon_row["pricelimit"] == 200) ||
                     ($amount >= 300 && $amount < 500 && $coupon_row["pricelimit"] == 300) ||
                     ($amount >= 500 && $coupon_row["pricelimit"] == 500))) {
                    
                    $valid_coupon = true;
                    $discount_amount = $coupon_row["discount"];
                    $_SESSION['coupon_discount'] = $discount_amount;
                    $_SESSION['original_amount'] = $original_amount;
                    $_SESSION['applied_coupon'] = $entered_code;
                    
                    // Calculate discounted amount
                    $discount = ($original_amount * $discount_amount) / 100;
                    $discounted_amount = $original_amount - $discount;
                    
                    echo "<div class='alert alert-success'>
                            <i class='fa fa-check-circle'></i> Coupon applied successfully! You saved ₹" . number_format($discount, 2) . "
                          </div>";
                    
                    // Store individual item discounts
                    $results = mysqli_query($conn, "SELECT * from cart where userid=$cid and flag=1");
                    while ($cart_item = mysqli_fetch_array($results)) {
                        $item_total = $cart_item['cart_quantity'] * $price;
                        $item_discount = $item_total * ($discount_amount / 100);
                        $_SESSION['item_discounts'][$cart_item['prodid']] = $item_discount;
                    }
                    
                    $amount = $discounted_amount; // Update the total amount
                } else {
                    echo "<div class='alert alert-danger'>
                            <i class='fa fa-times-circle'></i> This coupon is not valid for your cart total
                          </div>";
                }
            } else {
                echo "<div class='alert alert-danger'>
                        <i class='fa fa-times-circle'></i> Invalid coupon code
                      </div>";
            }
        } ?>
      </div>
      <div class="col-lg-5" style="text-align: left;">
        <div class="row total pt-4">
          <div class="col-lg-3 pt-2">
            <h3 style="color: #707070;">Total</h3>
          </div>
          <div class="col-lg-9" id="discount">
            <h1>
              <?php
              if (isset($_SESSION['coupon_discount']) && isset($_SESSION['original_amount'])) {
                  // Use the stored original amount and discount
                  $disc = $_SESSION['coupon_discount'];
                  $original_amount = $_SESSION['original_amount'];
                  
                  echo '<span style="text-decoration: line-through;">₹' . number_format($original_amount, 2) . '</span> ';
                  $discounted_amount = $original_amount - ($original_amount * ($disc / 100));
                  echo '₹' . number_format($discounted_amount, 2);
                  $amount = $discounted_amount;
              } else {
                  echo '₹' . number_format($amount, 2);
              }
              ?>
            </h1>
          </div>
        </div>
        <div class="row pt-4">
          <div class="col-lg-12">
            <button class="btn btn-success p-3 checkout" data-toggle="modal" data-target="#final-order"
              style=" color: white; font-weight: 600;">PROCEED TO CHECKOUT</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="at-login-form">
    <div class="modal fade" id="final-order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content wrapper">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
          </div>
          <div class="modal-body">
            <div class="title-text">
              <div class="title">
                CHECKOUT
              </div>
            </div>

            <div id="final table-responsive">
              <table class="table">
                <thead class="table-light" style="font-size: 1em;">
                  <tr>
                    <th scope="col">Product</th>
                    <th scope="col" style="text-align: center;">Quantity</th>
                    <th scope="col" style="text-align: center;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $results = mysqli_query(
                      $conn,
                      "SELECT * from cart where userid=$cid and flag=1"
                  );
                  $a = 0;
                  while ($row = mysqli_fetch_array($results)) { ?>
                  <tr>
                    <?php
                    $pid = $row["prodid"];
                    $sql1 = mysqli_query(
                        $conn,
                        "SELECT * FROM product WHERE prodid='$pid'"
                    );
                    $row1 = mysqli_fetch_array($sql1);
                    $prodname = $row1["prodname"];

                    $fid = $row["farmerid"];
                    $sql2 = mysqli_query(
                        $conn,
                        "SELECT * FROM myshop WHERE prodid='$pid' and farmerid='$fid'"
                    );
                    $row2 = mysqli_fetch_array($sql2);
                    $price = $row2["price"];

                    $total = $price * $row["cart_quantity"];
                    $a = $a + $total;
                    ?>
                    <td class="align-middle" width="auto"><span
                        style="font-size: 1.3em;"><?php echo $prodname; ?></span></td>
                    <td class="align-middle" style="text-align: center;">
                      <input type="text" value="<?php echo $row[
                          "cart_quantity"
                      ]; ?>" readonly style="width: 50px; text-align: center; padding: 5px 0px; " />
                    </td>
                    <td class="align-middle" style="text-align: center;">₹<?php echo $total; ?></td>
                  </tr>
                  <?php }
                  ?>
                </tbody>
                <tfoot>
                  <th></th>
                  <th></th>
                  <th style="text-align: center;"><?php 
                    if (isset($_SESSION['coupon_discount']) && $_SESSION['coupon_discount'] > 0) {
                      $disc = $_SESSION['coupon_discount'];
                      $original = $a;
                      $discounted = $a - ($a * ($disc / 100));
                      echo '<span style="text-decoration: line-through;">₹' . number_format($original, 2) . '</span><br>₹' . number_format($discounted, 2);
                      $a = $discounted; // Update final amount
                    } else {
                      echo "₹" . number_format($a, 2);
                    }
                  ?></th>
                </tfoot>
              </table>
            </div>
            <div class="amount">
              <h6 align="right" style="color: #519872;">
                <?php 
                  $discount_percentage = isset($_SESSION['coupon_discount']) ? $_SESSION['coupon_discount'] : 0;
                  echo "Discount : -" . $discount_percentage . "%";
                ?>
              </h6>
            </div>
            <div class="amount">
              <h4 align="right"><?php echo "Total : ₹" . number_format($amount, 2); ?></h4>
            </div>
          </div>
          <div class="modal-footer">
            <form method="post" action="./php/addtoorder.php" class="mr-2">
              <button class="btn btn-success" name="confirm">CONFIRM</button>
            </form>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container-fluid py-2 mt-5" style="background-color: #707070; font-weight: 600;">
    <div class="col-lg-12 text-center">
      © 2025 Copyright <a href="#" style="text-decoration: none; color: inherit">KrishiMitra</a>
    </div>
  </div>
  <script type="text/javascript" src="./scripts/cart.js"></script>
</body>

</html>
$disc = 0; // Initialize the discount variable with a default value of 0
?>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="post" action="./php/cancel_order.php" id="cancelOrderForm">
              <div class="form-group">
                <label>Please select the reason for cancellation:</label>
                <div class="custom-control custom-radio mt-2">
                  <input type="radio" id="reason1" name="cancel_reason" class="custom-control-input" value="delivery_issue" required>
                  <label class="custom-control-label" for="reason1">Cannot deliver to this address at this time</label>
                </div>
                <div class="custom-control custom-radio mt-2">
                  <input type="radio" id="reason2" name="cancel_reason" class="custom-control-input" value="no_answer" required>
                  <label class="custom-control-label" for="reason2">Customer not answering phone calls during delivery</label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger" name="cancel_order">Confirm Cancellation</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container-fluid py-2 mt-5" style="background-color: #707070; font-weight: 600;">
    <div class="col-lg-12 text-center">
      © 2025 Copyright <a href="#" style="text-decoration: none; color: inherit">KrishiMitra</a>
    </div>
  </div>
  <script type="text/javascript" src="./scripts/cart.js"></script>
</body>

</html>
$disc = 0; // Initialize the discount variable with a default value of 0
?>
$status_array = [
    "Not Accepted",
    "Accepted",
    "Dispatched",
    "In-Transit",
    "Delivered",
    "Cancelled by farmer"
];