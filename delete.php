<?php
require_once 'ConnDB.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $conn->prepare("DELETE FROM tb_products WHERE i_ProductID = :id");
        $stmt->execute(['id' => $id]);
        
        header("Location: index.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการลบ: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>