<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

// Get plant ID from URL parameter
$plantID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($plantID <= 0) {
    $_SESSION['message'] = "Invalid plant ID provided.";
    $_SESSION['message_type'] = 'error';
    header('Location: plants.php');
    exit();
}

// Updated SQL to JOIN category table and get total inventory quantity
$sql = "
    SELECT 
        plants.*, 
        category.category_name,
        COALESCE(SUM(inventory.inv_quantity), 0) as total_inventory_quantity
    FROM 
        plants 
    LEFT JOIN 
        category 
    ON 
        plants.category_id = category.category_id 
    LEFT JOIN
        inventory
    ON
        plants.PlantID = inventory.PlantID
    WHERE 
        plants.PlantID = ?
    GROUP BY
        plants.PlantID
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plantID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Plant not found.";
    $_SESSION['message_type'] = 'error';
    header('Location: plants.php');
    exit();
}

$plant = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($plant['Name']) ?> - Plant Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark-green: #537D5D;
            --primary-green: #73946B;
            --secondary-light-green: #9EBC8A;
            --accent-beige: #D2D0A0;
        }

        body {
            background: linear-gradient(135deg, var(--secondary-light-green) 0%, var(--accent-beige) 100%);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .notification {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            z-index: 9999;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

        .plant-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 2rem;
        }

        .plant-header {
            background: linear-gradient(135deg, var(--primary-dark-green) 0%, var(--primary-green) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .plant-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="leaves" patternUnits="userSpaceOnUse" width="20" height="20"><path d="M10,2 Q15,10 10,18 Q5,10 10,2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23leaves)"/></svg>');
            opacity: 0.3;
        }

        .plant-header h1 {
            position: relative;
            z-index: 1;
            margin-bottom: 0.5rem;
            font-weight: 300;
        }

        .plant-header .scientific-name {
            position: relative;
            z-index: 1;
            font-style: italic;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .plant-image-container {
            position: relative;
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
        }

        .plant-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            object-fit: cover;
        }

        .no-image-placeholder, .image-error {
            width: 300px;
            height: 300px;
            margin: 0 auto;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .no-image-placeholder {
            background-color: #f8f9fa;
            border: 3px dashed #ddd;
            color: #6c757d;
        }

        .image-error {
            background-color: #ffebee;
            border: 3px solid #ffcdd2;
            color: #d32f2f;
        }

        .detail-section {
            padding: 2rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        .detail-row {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .detail-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: var(--secondary-light-green);
        }

        .detail-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-light-green));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .detail-content {
            flex-grow: 1;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-dark-green);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
        }

        .quantity-badge {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-light-green));
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-block;
        }

        .action-buttons {
            padding: 2rem;
            background: #f8f9fa;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .btn-custom {
            margin: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffc107, #ffb300);
            color: white;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, var(--primary-green), var(--primary-dark-green));
            color: white;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(115, 148, 107, 0.4);
            color: white;
        }

        .status-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .quantity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 576px) {
            .quantity-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?= htmlspecialchars($plant['Name']) ?></strong> from the inventory?</p>
                    <p class="text-muted mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form action="delete_plants.php" method="POST">
                        <input type="hidden" name="delete_id" value="<?= $plant['PlantID'] ?>">
                        <input type="hidden" name="redirect_to" value="index.php">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Plant
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="plant-card">
            <!-- Plant Header -->
            <div class="plant-header">
                <div class="status-indicator">
                    <i class="fas fa-leaf me-1"></i>
                    ID: <?= htmlspecialchars($plant['PlantID']) ?>
                </div>
                <h1><?= htmlspecialchars($plant['Name']) ?></h1>
                <div class="scientific-name"><?= htmlspecialchars($plant['ScientificName']) ?></div>
            </div>

            <!-- Plant Image -->
            <div class="plant-image-container">
                <?php
                    $imagePath = $plant['ImagePath'];
                    
                    if (!empty($imagePath) && file_exists($imagePath)) {
                        $imageSrc = htmlspecialchars($imagePath);
                        echo "<img src='$imageSrc' alt='".htmlspecialchars($plant['Name'])."' class='plant-image'>";
                    } elseif (!empty($imagePath)) {
                        echo "<div class='image-error'><i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>Image File Missing</div>";
                    } else {
                        echo "<div class='no-image-placeholder'><i class='fas fa-camera fa-2x mb-2'></i><br>No Image Available</div>";
                    }
                ?>
            </div>

            <!-- Plant Details -->
            <div class="detail-section">
                <div class="details-grid">
                    <!-- Category Name from JOIN -->
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fas fa-tag"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Category</div>
                            <div class="detail-value"><?= htmlspecialchars($plant['category_name']) ?></div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Total Inventory Stock</div>
                            <div class="detail-value">
                                <span class="quantity-badge"><?= htmlspecialchars($plant['total_inventory_quantity']) ?> units</span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?= htmlspecialchars($plant['Location']) ?></div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value">
                                <?php
                                    $date = new DateTime($plant['LastUpdated']);
                                    echo $date->format('F j, Y \a\t g:i A');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="plants.php" class="btn btn-custom btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                </a>
                <a href="edit_plants.php?id=<?= $plant['PlantID'] ?>" class="btn btn-custom btn-edit">
                    <i class="fas fa-edit me-2"></i>Edit Plant
                </a>
                <button class="btn btn-custom btn-delete" onclick="confirmDelete()">
                    <i class="fas fa-trash me-2"></i>Delete Plant
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate detail rows on load
            const detailRows = document.querySelectorAll('.detail-row');
            detailRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.6s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
    </script>

<!-- footer.php -->
<footer class="text-center py-3 mt-5" style="background-color: #537D5D; color: white;">
    <div class="container">
        <small>&copy; <?= date('Y') ?> Plant Inventory System. All rights reserved.</small>
    </div>
</footer>

</body>
</html>