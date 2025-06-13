<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$notificationMessage = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
unset($_SESSION['message']);
unset($_SESSION['message_type']);

$sql = "SELECT plants.*, category.category_name,
        COALESCE(SUM(inventory.inv_quantity), 0) AS total_quantity
        FROM plants
        LEFT JOIN category ON plants.category_id = category.category_id
        LEFT JOIN inventory ON plants.PlantID = inventory.PlantID
        GROUP BY plants.PlantID
        ORDER BY plants.PlantID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plant Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-dark-green: #537D5D;
            --primary-green: #73946B;
            --secondary-light-green: #9EBC8A;
            --accent-beige: #D2D0A0;
        }

        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        body {
            background-color: var(--accent-beige);
            font-family: 'Segoe UI', sans-serif;
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

        .table thead {
            background-color: var(--primary-green);
            color: white;
        }

        .card-header {
            background-color: var(--primary-dark-green);
            color: white;
        }

        .plant-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #ddd;
        }

        .no-image-placeholder,
        .image-error {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            text-align: center;
        }

        .no-image-placeholder {
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            color: #6c757d;
        }

        .image-error {
            background-color: #ffebee;
            border: 2px solid #ffcdd2;
            color: #d32f2f;
        }

        .btn-add-plant {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
        }

        .btn-add-plant:hover {
            background-color: var(--secondary-light-green);
            border-color: var(--secondary-light-green);
            color: var(--primary-dark-green);
        }

        footer {
            background-color: var(--primary-dark-green);
            color: white;
            text-align: center;
            padding: 12px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

<div class="wrapper">
<main>
    <?php if (!empty($notificationMessage)): ?>
        <div class="container mt-3">
            <div id="notification-message" class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?php if ($messageType === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i>
                <?php elseif ($messageType === 'error' || $messageType === 'danger'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i>
                <?php elseif ($messageType === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle me-2"></i>
                <?php endif; ?>
                <?= htmlspecialchars($notificationMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this plant from the inventory?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="delete_plants.php" method="POST">
                        <input type="hidden" name="delete_id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">🌱 Plant Inventory System</h3>
                <a href="add_plants.php" class="btn btn-add-plant">
                    <i class="fa-solid fa-plus"></i> Add New Plant
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between">
                    <input type="text" id="searchInput" class="form-control w-100" placeholder="🔍 Search plants...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Plant ID</th>
                                <th>Plant Name</th>
                                <th>Scientific Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Location</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php
                                                $imagePath = $row['ImagePath'];
                                                if (!empty($imagePath) && file_exists($imagePath)) {
                                                    $imageSrc = htmlspecialchars($imagePath);
                                                    echo "<img src='$imageSrc' alt='Plant Image' class='plant-image'>";
                                                } elseif (!empty($imagePath)) {
                                                    echo "<div class='image-error'>Missing File</div>";
                                                } else {
                                                    echo "<div class='no-image-placeholder'>No Image</div>";
                                                }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['PlantID']) ?></td>
                                        <td><?= htmlspecialchars($row['Name']) ?></td>
                                        <td><?= htmlspecialchars($row['ScientificName']) ?></td>
                                        <td><?= htmlspecialchars($row['category_name'] ?? 'Unknown') ?></td>
                                        <td><?= htmlspecialchars($row['total_quantity']) ?></td>
                                        <td><?= htmlspecialchars($row['Location']) ?></td>
                                        <td><?= htmlspecialchars($row['LastUpdated']) ?></td>
                                        <td>
                                            <a href="view_plants.php?id=<?= $row['PlantID'] ?>" class="btn btn-info btn-sm">View</a>
                                            <a href="edit_plants.php?id=<?= $row['PlantID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['PlantID'] ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">No plants found in the inventory.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<footer>
    <div class="container">
        <small>&copy; <?= date('Y') ?> Plant Inventory System. All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- The only real change is within the script tag at the bottom -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(plantID) {
        document.getElementById("delete_id").value = plantID;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const notif = document.getElementById('notification-message');
        if (notif) {
            setTimeout(() => {
                notif.style.display = 'none';
            }, 2000);
        }

        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('table tbody tr');

        searchInput.addEventListener('keyup', function () {
            const filter = searchInput.value.toLowerCase();

            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                row.style.display = rowText.includes(filter) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>
