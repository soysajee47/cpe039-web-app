<?php
require_once 'ConnDB.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$product = null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM tb_products WHERE i_ProductID = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    header("Location: index.php");
    exit();
}

// เมื่อกดบันทึกการแก้ไข
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['c_ProductName'];
    $price = $_POST['i_Price'];
    $unit  = $_POST['c_Unit'];

    try {
        $stmt = $conn->prepare("UPDATE tb_products SET c_ProductName = :name, i_Price = :price, c_Unit = :unit WHERE i_ProductID = :id");
        $stmt->execute([
            'name'  => $name,
            'price' => $price,
            'unit'  => $unit,
            'id'    => $id
        ]);
        header("Location: index.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        $error = "แก้ไขไม่สำเร็จ: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5" style="max-width: 600px;">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h4 class="m-0">✏️ แก้ไขสินค้า (ID: <?= $product['i_ProductID'] ?>)</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">ชื่อสินค้า</label>
                    <input type="text" name="c_ProductName" class="form-control" value="<?= htmlspecialchars($product['c_ProductName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">หน่วยนับ</label>
                    <input type="text" name="c_Unit" class="form-control" value="<?= htmlspecialchars($product['c_Unit']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ราคา</label>
                    <input type="number" step="0.01" name="i_Price" class="form-control" value="<?= $product['i_Price'] ?>" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>