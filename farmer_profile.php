<div class="container-fluid" id="feedback" style="padding-top: 7%;">
    <div class="text-center">
        <h1 style="font-weight: 600; font-size: 3.5em; letter-spacing: 2px; color: #34626c;">CUSTOMER FEEDBACK</h1>
        <hr width="40%" align="text-center" style="border-width: 4px; background-color: #999;">
    </div>
    
    <div class="container mt-4">
        <div class="row">
            <?php
            // Get all feedback for this farmer
            $feedback_stmt = $conn->prepare(
                "SELECT f.*, p.prodname, u.name as customer_name, m.orderdate 
                FROM feedback f 
                JOIN product p ON f.prodid = p.prodid 
                JOIN users u ON f.userid = u.userid 
                JOIN myorder m ON f.orderid = m.orderid 
                WHERE f.farmerid = ? 
                ORDER BY f.feedback_date DESC"
            );

            if (!$feedback_stmt) {
                die("Error in preparing feedback statement: " . $conn->error);
            }

            $feedback_stmt->bind_param("i", $fid);
            if (!$feedback_stmt->execute()) {
                die("Error executing feedback query: " . $feedback_stmt->error);
            }
            $feedback_result = $feedback_stmt->get_result();
            $feedback_stmt->close();

            if ($feedback_result->num_rows > 0) {
                while ($feedback = $feedback_result->fetch_assoc()) {
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($feedback["prodname"]); ?></h6>
                                    <small class="text-muted">
                                        <?php 
                                        $date = date_create($feedback["feedback_date"]);
                                        echo date_format($date, "d/m/Y"); 
                                        ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <?php
                                    // Display stars based on rating
                                    for ($i = 0; $i < $feedback["rating"]; $i++) {
                                        echo '<i class="fa fa-star text-warning"></i>';
                                    }
                                    for ($i = $feedback["rating"]; $i < 5; $i++) {
                                        echo '<i class="fa fa-star-o text-warning"></i>';
                                    }
                                    ?>
                                </div>
                                <p class="card-text"><?php echo htmlspecialchars($feedback["comment"]); ?></p>
                                <footer class="blockquote-footer mt-2">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($feedback["customer_name"]); ?>
                                        <br>Order #<?php echo htmlspecialchars($feedback["orderid"]); ?>
                                    </small>
                                </footer>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No feedback received yet.</p>
                </div>
                <?php
            }
            ?>
        </div>
        
        <!-- Feedback Statistics -->
        <div class="row mt-4">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Feedback Overview</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get rating statistics
                        $stats_stmt = $conn->prepare(
                            "SELECT 
                                COUNT(*) as total_feedback,
                                AVG(rating) as avg_rating,
                                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                            FROM feedback 
                            WHERE farmerid = ?"
                        );
                        $stats_stmt->bind_param("i", $fid);
                        $stats_stmt->execute();
                        $stats = $stats_stmt->get_result()->fetch_assoc();
                        $stats_stmt->close();

                        if ($stats["total_feedback"] > 0) {
                            // Add this line for debugging
                            echo "<!-- Debug: Total feedback: " . $stats["total_feedback"] . " -->";
                            $avg_rating = round($stats["avg_rating"], 1);
                            ?>
                            <div class="text-center mb-4">
                                <h2><?php echo $avg_rating; ?> <small class="text-muted">/ 5</small></h2>
                                <div>
                                    <?php
                                    $full_stars = floor($avg_rating);
                                    $half_star = $avg_rating - $full_stars >= 0.5;
                                    
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fa fa-star text-warning fa-lg"></i>';
                                    }
                                    if ($half_star) {
                                        echo '<i class="fa fa-star-half-o text-warning fa-lg"></i>';
                                        $i++;
                                    }
                                    for (; $i < 5; $i++) {
                                        echo '<i class="fa fa-star-o text-warning fa-lg"></i>';
                                    }
                                    ?>
                                </div>
                                <small class="text-muted">Based on <?php echo $stats["total_feedback"]; ?> reviews</small>
                            </div>
                            
                            <!-- Rating distribution -->
                            <?php
                            $ratings = [
                                5 => $stats["five_star"],
                                4 => $stats["four_star"],
                                3 => $stats["three_star"],
                                2 => $stats["two_star"],
                                1 => $stats["one_star"]
                            ];
                            
                            foreach ($ratings as $stars => $count) {
                                $percentage = ($count / $stats["total_feedback"]) * 100;
                                ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-muted" style="width: 50px;">
                                        <?php echo $stars; ?> <i class="fa fa-star text-warning"></i>
                                    </div>
                                    <div class="progress flex-grow-1" style="height: 10px;">
                                        <div class="progress-bar bg-warning" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="text-muted ml-2" style="width: 50px;">
                                        <?php echo $count; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            // Add this line for debugging
                            echo "<!-- Debug: No feedback found for farmer ID: " . $fid . " -->";
                            echo '<p class="text-center text-muted">No ratings yet</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>