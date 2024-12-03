<?php
include "db_conn.php";

// عدد السجلات في كل صفحة
$records_per_page = 5;

// الصفحة الحالية
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// حساب بداية السجلات (OFFSET)
$offset = ($current_page - 1) * $records_per_page;

// استعلام لحساب العدد الإجمالي للسجلات مع الأخذ بالاعتبار الفلاتر
$filters = "WHERE 1"; // الشرط الأساسي
if (!empty($_GET['first_name'])) {
    $first_name = mysqli_real_escape_string($conn, $_GET['first_name']);
    $filters .= " AND first_name LIKE '%$first_name%'";
}
if (!empty($_GET['last_name'])) {
    $last_name = mysqli_real_escape_string($conn, $_GET['last_name']);
    $filters .= " AND last_name LIKE '%$last_name%'";
}
if (!empty($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $filters .= " AND email LIKE '%$email%'";
}
if (!empty($_GET['gender'])) {
    $gender = mysqli_real_escape_string($conn, $_GET['gender']);
    $filters .= " AND gender = '$gender'";
}

// حساب العدد الإجمالي للسجلات
$total_records_query = "SELECT COUNT(*) AS total FROM `y_info` $filters";
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_assoc($total_records_result);
$total_records = $total_records_row['total'];

// حساب العدد الإجمالي للصفحات
$total_pages = ceil($total_records / $records_per_page);

// استعلام لجلب البيانات مع الفلاتر
$query = "SELECT * FROM `y_info` $filters LIMIT $records_per_page OFFSET $offset";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP CRUD with Filters</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom CSS -->
  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      background-color: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    table {
      border-collapse: separate;
      border-spacing: 0 15px;
    }
    .btn {
      border-radius: 25px;
    }
  </style>
</head>
<body>

  <div class="container table-container mt-5">
    <!-- عرض رسائل النظام -->
    <?php if (isset($_GET["msg"])): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= $_GET["msg"] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
        <!-- Add New Button -->
        <div class="mb-3 d-flex justify-content-end">
      <a href="add-new.php" class="btn btn-success">Add New</a>
    </div>

    <!-- إضافة فلتر -->
    <form method="GET" action="" class="mb-3">
      <div class="row g-3">
        <div class="col-md-3">
          <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?= isset($_GET['first_name']) ? $_GET['first_name'] : '' ?>">
        </div>
        <div class="col-md-3">
          <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= isset($_GET['last_name']) ? $_GET['last_name'] : '' ?>">
        </div>
        <div class="col-md-3">
          <input type="text" name="email" class="form-control" placeholder="Email" value="<?= isset($_GET['email']) ? $_GET['email'] : '' ?>">
        </div>
        <div class="col-md-3">
          <select name="gender" class="form-select">
            <option value="">Select Gender</option>
            <option value="Male" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="index.php" class="btn btn-secondary ms-2">Reset</a>
      </div>
    </form>

    <!-- جدول البيانات -->
    <div class="table-responsive">
      <table class="table table-hover text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Gender</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= $row["id"] ?></td>
                <td><?= $row["first_name"] ?></td>
                <td><?= $row["last_name"] ?></td>
                <td><?= $row["email"] ?></td>
                <td><?= $row["gender"] ?></td>
                <td>
                  <a href="edit.php?id=<?= $row["id"] ?>" class="link-dark"><i class="fa-solid fa-pen-to-square fs-5 me-3"></i></a>
                  <a href="delete.php?id=<?= $row["id"] ?>" class="link-dark"><i class="fa-solid fa-trash fs-5"></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">No records found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- روابط الباجينيشن -->
      <nav>
        <ul class="pagination justify-content-center">
          <?php if ($current_page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a></li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($current_page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
