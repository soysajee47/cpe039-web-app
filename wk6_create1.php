<?php
// เปิดการแสดง Error ทั้งหมดเพื่อตรวจสอบปัญหาที่แท้จริง
error_reporting(E_ALL);
ini_set('display_errors', 1);

$alertMessage = "";
$alertType = "";

try {
    require_once "ConnDB.php";
} catch (PDOException $e) {
    $alertType = "danger";
    $alertMessage = "เชื่อมต่อ Railway ไม่สำเร็จ กรุณาตรวจสอบ MYSQLPASSWORD และค่าการเชื่อมต่อใน ConnDB.php";
}

// ---------------------------------------------------------------------
// 1. กำหนดค่าคงที่สำหรับ SupplierID และ CategoryID 
// (ตั้งเป็น 23 และ 3 ซึ่งเป็น ID ที่มีอยู่จริงในรูปภาพ phpMyAdmin)
// ---------------------------------------------------------------------
define('FIXED_SUPPLIER_ID', 23); // ล็อกรหัสผู้จัดจำหน่าย
define('FIXED_CAT_ID', 3);       // ล็อกรหัสหมวดหมู่สินค้า

// ---------------------------------------------------------------------
// 2. ส่วนประมวลผลการบันทึกข้อมูล (POST Method)
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_submit']) && $alertMessage === "") {
    
    $productName = trim($_POST['ProductName'] ?? '');
    $unit        = trim($_POST['Unit'] ?? '');
    $price       = floatval($_POST['Price'] ?? 0);
    $supplierID  = FIXED_SUPPLIER_ID;
    $catID       = FIXED_CAT_ID;

    if (empty($productName) || empty($unit) || $price <= 0) {
        $alertType = "danger";
        $alertMessage = "กรุณากรอกข้อมูล ชื่อสินค้า, หน่วยนับ และราคา ให้ครบถ้วน";
    } else {
        try {
            // ตรวจสอบว่าตัวแปร $conn เป็น PDO หรือ MySQLi
            if (isset($conn) && $conn instanceof PDO) {
                // *** กรณี ConnDB.php เป็น PDO ***
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = "INSERT INTO `tb_products` (`c_ProductName`, `i_SupplierID`, `i_CategoryID`, `c_Unit`, `i_Price`) 
                        VALUES (:productName, :supplierID, :catID, :unit, :price)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':productName' => $productName,
                    ':supplierID'  => $supplierID,
                    ':catID'       => $catID,
                    ':unit'        => $unit,
                    ':price'       => $price
                ]);
                
                $insertedID = $conn->lastInsertId();
                $alertType = "success";
                $alertMessage = "บันทึกข้อมูลสำเร็จ! เพิ่มสินค้าลงใน tb_products เรียบร้อยแล้ว (รหัสสินค้าใหม่: " . $insertedID . ")";

            } elseif (isset($conn) && $conn instanceof mysqli) {
                // *** กรณี ConnDB.php เป็น MySQLi ***
                $sql = "INSERT INTO `tb_products` (`c_ProductName`, `i_SupplierID`, `i_CategoryID`, `c_Unit`, `i_Price`) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("MySQLi Prepare Error: " . $conn->error);
                }
                
                $stmt->bind_param("siisd", $productName, $supplierID, $catID, $unit, $price);
                
                if ($stmt->execute()) {
                    $insertedID = $conn->insert_id;
                    $alertType = "success";
                    $alertMessage = "บันทึกข้อมูลสำเร็จ! เพิ่มสินค้าลงใน tb_products เรียบร้อยแล้ว (รหัสสินค้าใหม่: " . $insertedID . ")";
                } else {
                    throw new Exception("MySQLi Execute Error: " . $stmt->error);
                }
            } else {
                throw new Exception("ไม่พบตัวแปรเชื่อมต่อฐานข้อมูล \$conn หรือการเชื่อมต่อไม่ถูกต้อง กรุณาตรวจสอบไฟล์ ConnDB.php");
            }

        } catch (PDOException $e) {
            $alertType = "danger";
            $alertMessage = "<strong>PDO Database Error:</strong> " . htmlspecialchars($e->getMessage());
        } catch (Exception $e) {
            $alertType = "danger";
            $alertMessage = "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลสินค้า - db_northwind</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; }
        .card-main { border: none; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); background: #ffffff; }
        .card-header-custom { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; border-radius: 12px 12px 0 0 !important; }
        .form-control[readonly] { background-color: #e9ecef; font-weight: 600; color: #495057; }
    </style>
</head>
<body class="py-4 py-md-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">

            <!-- ข้อความแจ้งเตือนสถานะ / Error -->
            <?php if (!empty($alertMessage)): ?>
                <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid <?php echo ($alertType === 'success') ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger'; ?> fs-3 me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1 fw-bold"><?php echo ($alertType === 'success') ? 'สำเร็จ!' : 'พบปัญหาในการบันทึกข้อมูล!'; ?></h5>
                            <div><?php echo $alertMessage; ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- ฟอร์มเพิ่มข้อมูล -->
            <div class="card card-main">
                <div class="card-header-custom p-4">
                    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-box-archive me-2"></i>ระบบเพิ่มสินค้า (tb_products)</h4>
                    <small class="opacity-75">ฐานข้อมูล: db_northwind</small>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="" method="POST">
                        <input type="hidden" name="action_submit" value="1">

                        <!-- ชื่อสินค้า -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อสินค้า (c_ProductName) <span class="text-danger">*</span></label>
                            <input type="text" name="ProductName" class="form-control" placeholder="เช่น เทสสินค้าชนิดใหม่" required value="<?php echo htmlspecialchars($_POST['ProductName'] ?? ''); ?>">
                        </div>

                        <div class="row">
                            <!-- SupplierID (Locked) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">รหัสผู้จัดจำหน่าย (i_SupplierID) <span class="badge bg-secondary">ล็อกค่า</span></label>
                                <input type="text" class="form-control" value="<?php echo FIXED_SUPPLIER_ID; ?>" readonly>
                                <div class="form-text text-muted">* กำหนดค่าคงที่ไว้ที่ ID: <?php echo FIXED_SUPPLIER_ID; ?></div>
                            </div>

                            <!-- CategoryID (Locked) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">หมวดหมู่สินค้า (i_CategoryID) <span class="badge bg-secondary">ล็อกค่า</span></label>
                                <input type="text" class="form-control" value="<?php echo FIXED_CAT_ID; ?>" readonly>
                                <div class="form-text text-muted">* กำหนดค่าคงที่ไว้ที่ ID: <?php echo FIXED_CAT_ID; ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Unit -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">หน่วยนับ (c_Unit) <span class="text-danger">*</span></label>
                                <input type="text" name="Unit" class="form-control" placeholder="เช่น ชิ้น, กล่อง, ขวด" required value="<?php echo htmlspecialchars($_POST['Unit'] ?? ''); ?>">
                            </div>

                            <!-- Price -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ราคาต่อหน่วย (i_Price) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" step="0.01" min="0.01" name="Price" class="form-control" placeholder="0.00" required value="<?php echo htmlspecialchars($_POST['Price'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-light border"><i class="fa-solid fa-rotate-left me-1"></i> ล้างค่า</button>
                            <button type="submit" class="btn btn-primary px-4 py-2"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกข้อมูลลงฐานข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>