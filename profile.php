<?php

include "./php/config.php";

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit; // Added exit for security
}

// No need to redefine constants - they're already defined in config.php
// For reference, the order status values are:
// ORDER_NOT_ACCEPTED = 0, ORDER_ACCEPTED = 1, ORDER_DISPATCHED = 2
// ORDER_IN_TRANSIT = 3, ORDER_DELIVERED = 4, ORDER_RETURN_PENDING = 5
// ORDER_RETURNED = 6, ORDER_CANCELLED = 7

// Status arrays moved to top for better organization
$status_array = array(
    0 => 'Not Accepted',
    1 => 'Accepted',
    2 => 'Dispatched',
    3 => 'In Transit',
    4 => 'Delivered',
    5 => 'Return Pending',    // Add back return statuses to prevent undefined offset
    6 => 'Returned',
    7 => 'Cancelled'
);

// Remove return status array since we're removing return feature
// Get user information using prepared statement
$uname = $_SESSION["username"];
$stmt = $conn->prepare("SELECT userid, name, gender FROM users WHERE username = ?");
$stmt->bind_param("s", $uname);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Store user ID for later use
$cid = $user_data["userid"];
$user_name = $user_data["name"];
$gender = $user_data["gender"];

?>

<!DOCTYPE html>
<html>

<head>
  <title>Customer Profile</title>
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
  <link rel="stylesheet" type="text/css" href="./css/profilestyle.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Remove return-related styles */
    .return-btn {
        margin-top: 10px;
    }
    .alert-container {
        margin-top: 20px;
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
          <li class="nav-item"><a class="nav-link" href="#myorders">My Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="./php/logout.php">Log Out</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <?php
  // Display feedback messages
  if (isset($_SESSION["feedback_success"])) {
      echo '<div class="container mt-4"><div class="alert alert-success alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          ' . htmlspecialchars($_SESSION["feedback_success"]) . '
      </div></div>';
      unset($_SESSION["feedback_success"]);
  }
  
  if (isset($_SESSION["feedback_error"])) {
      echo '<div class="container mt-4"><div class="alert alert-danger alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          ' . htmlspecialchars($_SESSION["feedback_error"]) . '
      </div></div>';
      unset($_SESSION["feedback_error"]);
  }
  ?>

  <div class="image">
    <div class="heading text-center">
      <img src="./assets/<?php echo (strcmp($gender, "Female") == 0) ? "woman" : "man"; ?>.png" class="img-responsive" width="10%">
      <h1>Hello
        <?php echo htmlspecialchars($user_name); ?></h1>
    </div>
    <div class="subhead">
      <p>Happy Shopping!</p>
    </div>
  </div>

  <div class="container-fluid" id="myorders" style="padding-top: 7%;">
    <div class="text-center">
      <h1 style="font-weight: 600; font-size: 3.5em; letter-spacing: 2px; color: #34626c;">YOUR ORDERS</h1>
      <hr width="40%" align="text-center" style="border-width: 4px; background-color: #999;">
    </div>
    
    <!-- Start grid container -->
    <div class="container mt-4">
      <div class="row">
        <?php
        // Get all order IDs for this user - using prepared statement
        $order_stmt = $conn->prepare("SELECT orderid from myorder WHERE userid = ? GROUP BY orderid HAVING COUNT(*) >= 1 ORDER BY orderid DESC");
        $order_stmt->bind_param("i", $cid);
        $order_stmt->execute();
        $orders_result = $order_stmt->get_result();
        $order_stmt->close();
        
        while ($order_row = $orders_result->fetch_assoc()) {
            $oid = $order_row['orderid'];
            
            // Get order date
            $date_stmt = $conn->prepare("SELECT orderdate FROM myorder WHERE userid = ? AND orderid = ? LIMIT 1");
            $date_stmt->bind_param("ii", $cid, $oid);
            $date_stmt->execute();
            $date_result = $date_stmt->get_result();
            $date_row = $date_result->fetch_assoc();
            $date_stmt->close();
            
            $order_date = date_create($date_row['orderdate']);
            $formatted_date = date_format($order_date, "d/m/Y");
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="badge badge-pill badge-warning px-3 py-2">Order #<?php echo htmlspecialchars($oid); ?></span>
                            </h5>
                            <small class="text-muted"><?php echo $formatted_date; ?></small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <?php
                        // Get all farmers for this order
                        $farmer_stmt = $conn->prepare(
                            "SELECT DISTINCT m.farmerid, f.name, m.status 
                             FROM myorder m
                             JOIN farmer f ON m.farmerid = f.farmerid
                             WHERE m.userid = ? AND m.orderid = ?
                             GROUP BY m.farmerid HAVING COUNT(*) >= 1"
                        );
                        $farmer_stmt->bind_param("ii", $cid, $oid);
                        $farmer_stmt->execute();
                        $farmers_result = $farmer_stmt->get_result();
                        $farmer_stmt->close();
                        
                        while ($farmer_row = $farmers_result->fetch_assoc()) {
                            $fid = $farmer_row["farmerid"];
                            $fname = $farmer_row["name"];
                            $status = $farmer_row["status"];
                            ?>
                            <div class="farmer-info mb-3 border-bottom pb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-2"><i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($fname); ?></h6>
                                    <span class="<?php 
                                        switch ($status) {
                                            case 0: echo "badge badge-secondary"; break;
                                            case 1: echo "badge badge-info"; break;
                                            case 2: echo "badge badge-dark"; break;
                                            case 3: echo "badge badge-warning"; break;
                                            case 4: echo "badge badge-success"; break;
                                            case 5: echo "badge badge-warning"; break;  // Return Pending
                                            case 6: echo "badge badge-info"; break;     // Returned
                                            case 7: echo "badge badge-danger"; break;
                                            default: echo "badge badge-secondary";
                                        }
                                    ?>"><?php echo isset($status_array[$status]) ? htmlspecialchars($status_array[$status]) : 'Unknown'; ?></span>
                                </div>
                                <div class="items-list">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <?php
                                                // Get products for this order and farmer
                                                $products_stmt = $conn->prepare(
                                                    "SELECT m.prodid, m.quantity, m.amount, m.status, p.prodname 
                                                     FROM myorder m
                                                     JOIN product p ON m.prodid = p.prodid
                                                     WHERE m.userid = ? AND m.orderid = ? AND m.farmerid = ?"
                                                );
                                                $products_stmt->bind_param("iii", $cid, $oid, $fid);
                                                $products_stmt->execute();
                                                $products_result = $products_stmt->get_result();
                                                $products_stmt->close();
                                                
                                                while ($product = $products_result->fetch_assoc()) {
                                                    ?>
                                                    <tr>
                                                        <td class="text-truncate" style="max-width: 120px;" title="<?php echo htmlspecialchars($product["prodname"]); ?>"><?php echo htmlspecialchars($product["prodname"]); ?></td>
                                                        <td class="text-nowrap"><small>x<?php echo htmlspecialchars($product["quantity"]); ?> kg</small></td>
                                                        <td class="text-right">₹<?php echo htmlspecialchars($product["amount"]); ?></td>
                                                        <?php if ($status == 4) { // Only show feedback button for delivered orders ?>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    data-toggle="modal" 
                                                                    data-target="#feedbackModal<?php echo $product['prodid']; ?>">
                                                                    <i class="fa fa-comment"></i> Feedback
                                                                </button>
                                                            </td>
                                                        <?php } ?>
                                                    </tr>
                                                    
                                                    <!-- Feedback Modal -->
                                                    <div class="modal fade" id="feedbackModal<?php echo $product['prodid']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Product Feedback</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="./php/submit_feedback.php" method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                                                        <input type="hidden" name="product_id" value="<?php echo $product['prodid']; ?>">
                                                                        <input type="hidden" name="farmer_id" value="<?php echo $fid; ?>">
                                                                        
                                                                        <div class="form-group">
                                                                            <label>Rating</label>
                                                                            <select class="form-control" name="rating" required>
                                                                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                                                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                                                                <option value="3">⭐⭐⭐ Good</option>
                                                                                <option value="2">⭐⭐ Fair</option>
                                                                                <option value="1">⭐ Poor</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Comments</label>
                                                                            <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience with this product..." required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
      </div>
    </div>
  </div>

  <div class="container-fluid py-2 mt-3" style="background-color: #3797a4; font-weight: 600;">
    <div class="col-lg-12 text-center">
      © 2025 Copyright <a href="#" style="text-decoration: none; color: inherit">KrishiMitra</a>
    </div>
  </div>

  <script type="text/javascript" src="./scripts/cart.js"></script>
  
  <script>
    // Auto-dismiss alert messages after 5 seconds
    $(document).ready(function() {
      setTimeout(function() {
        $('.auto-dismiss').alert('close');
      }, 5000);
    });
  </script>
</body>
</html>