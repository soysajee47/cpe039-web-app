<?php
// เปิดการแสดงข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'ConnDB.php';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($search != '') {
        $stmt = $conn->prepare("SELECT * FROM tb_products WHERE c_ProductName LIKE :search ORDER BY i_ProductID DESC");
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM tb_products ORDER BY i_ProductID DESC LIMIT 50");
        $stmt->execute();
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการสินค้า Northwind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ระบบจัดการสินค้า (tb_products)</h2>
        <a href="wk6_create1.php" class="btn btn-primary">เพิ่มสินค้าใหม่</a>
    </div>

    <?php if ($msg == 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
             <strong>สำเร็จ!</strong> ลบข้อมูลสินค้าเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($msg == 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
             <strong>สำเร็จ!</strong> แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary" type="submit">ค้นหา</button>
            <?php if ($search != ''): ?>
                <a href="index.php" class="btn btn-outline-danger">ล้างการค้นหา</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>ชื่อสินค้า</th>
                        <th style="width: 130px;">ราคา</th>
                        <th style="width: 150px;">หน่วยนับ</th>
                        <th style="width: 170px;" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $row): ?>
                            <tr>
                                <td><?= $row['i_ProductID'] ?></td>
                                <td><?= htmlspecialchars($row['c_ProductName']) ?></td>
                                <td>฿<?= number_format($row['i_Price'], 2) ?></td>
                                <td><?= htmlspecialchars($row['c_Unit']) ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $row['i_ProductID'] ?>" class="btn btn-sm btn-warning me-1">แก้ไข</a>
                                    <a href="delete.php?id=<?= $row['i_ProductID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณต้องการลบสินค้านี้ใช่หรือไม่?')">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลสินค้า</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>