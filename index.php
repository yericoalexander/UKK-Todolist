<?php
$koneksi = mysqli_connect("localhost", "root", "", "ukk2025_todolist");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Kategori task
$categories = ['Pekerjaan', 'Pribadi', 'Sekolah', 'Belanja', 'Lainnya'];    

// tambah task ke database
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    if (!empty($task) && !empty($priority) && !empty($due_date) && !empty($description) && !empty($category)) {
        $query = "INSERT INTO task (task, priority, due_date, description, status, category) VALUES ('$task', '$priority', '$due_date', '$description', '0', '$category')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Task berhasil ditambahkan'); window.location.href='index.php?page=1';</script>";
        } else {
            echo "<script>alert('Task gagal ditambahkan: " . mysqli_error($koneksi) . "');</script>";
        }
    } else {
        echo "<script>alert('Semua field harus diisi');</script>";
    }
}

// edit task
if (isset($_POST['edit_task'])) {
    $id = $_POST['id'];
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    if (!empty($task) && !empty($priority) && !empty($due_date) && !empty($description) && !empty($category)) {
        $query = "UPDATE task SET task = '$task', priority = '$priority', due_date = '$due_date', description = '$description', category = '$category' WHERE id = '$id'";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Task berhasil diperbarui'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Task gagal diperbarui: " . mysqli_error($koneksi) . "');</script>";
        }
    } else {
        echo "<script>alert('Semua field harus diisi');</script>";
    }
}

// task selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    if (mysqli_query($koneksi, "UPDATE task SET status = '1' WHERE id = '$id'")) {
        echo "<script>alert('Task berhasil diselesaikan'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menyelesaikan task: " . mysqli_error($koneksi) . "');</script>";
    }
}

// undo status
if (isset($_GET['undo'])) {
    $id = $_GET['undo'];
    if (mysqli_query($koneksi, "UPDATE task SET status = '0' WHERE id = '$id'")) {
        echo "<script>alert('Status task berhasil diubah menjadi Belum Selesai'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah status task: " . mysqli_error($koneksi) . "');</script>";
    }
}

// hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (mysqli_query($koneksi, "DELETE FROM task WHERE id = '$id'")) {
        echo "<script>alert('Task berhasil dihapus'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus task: " . mysqli_error($koneksi) . "');</script>";
    }
}

// handler untuk pin/unpin task
if (isset($_GET['pin'])) {
    $id = $_GET['pin'];
    mysqli_query($koneksi, "UPDATE task SET pinned = IF(pinned=1, 0, 1) WHERE id = '$id'");
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Filter dan search
$filter_priority = isset($_GET['filter_priority']) ? $_GET['filter_priority'] : '';
$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

$limit = 3; // Jumlah task per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter query building
$where = "WHERE 1=1";
if (!empty($filter_priority)) {
    $where .= " AND priority = '$filter_priority'";
}
if (!empty($filter_category)) {
    $where .= " AND category = '$filter_category'";
}
if (!empty($filter_date)) {
    $where .= " AND DATE(due_date) = '$filter_date'"; // Pastikan format tanggal sesuai
}
if (!empty($search)) {
    // Tambahkan pencarian di multiple kolom
    $where .= " AND (task LIKE '%$search%' OR description LIKE '%$search%' OR category LIKE '%$search%')";
}

// Perbaiki logika filter status
if (!empty($filter_status)) {
    if ($filter_status === '0' || $filter_status === '1') {
        $where .= " AND status = '$filter_status'";
    }
}

// Inisialisasi variabel $order_by
$order_by = "ORDER BY pinned DESC, status ASC, priority DESC, due_date ASC";

// Update query untuk mengurutkan task yang di-pin ke atas
if ($sort == 'due_date_asc') {
    $order_by = "ORDER BY due_date ASC";
} elseif ($sort == 'due_date_desc') {
    $order_by = "ORDER BY due_date DESC";
} elseif ($sort == 'priority_desc') {
    $order_by = "ORDER BY priority DESC";
} elseif ($sort == 'priority_asc') {
    $order_by = "ORDER BY priority ASC";
}

// Debug query
error_log("Filter Query: SELECT * FROM task $where $order_by LIMIT $limit OFFSET $offset");

$result = mysqli_query($koneksi, "SELECT * FROM task $where $order_by LIMIT $limit OFFSET $offset");
$total_tasks = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM task $where"))['total'];
$total_pages = ceil($total_tasks / $limit);

// Hitung jumlah task selesai dan total task
$total_completed = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM task WHERE status = '1'"))['total'];
$total_tasks = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM task"))['total'];
$progress = $total_tasks > 0 ? ($total_completed / $total_tasks) * 100 : 0;

// Debugging: Tambahkan log untuk memeriksa nilai
error_log("Total Tasks: $total_tasks, Completed: $total_completed, Progress: $progress");



?>

<!DOCTYPE html>
<html lang="en">

<head></head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aplikasi To Do List</title>
<link rel="stylesheet" href="./assets/css/style.css">
<link rel="stylesheet" href="./assets/css/pagination.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Banner Website -->
    <div class="site-banner">
        <div class="container">
            <div class="logo-text">
                <i class="fas fa-check-circle logo-icon"></i>
                <h1 class="site-title">TodoNow</h1>
            </div>
            <p class="site-subtitle">Manage your tasks efficiently and effectively</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistik Cards -->
        <div class="stats-container">
            <div class="stat-card total">
                <i class="fas fa-tasks stat-icon"></i>
                <div class="stat-label">Total Task</div>
                <div class="stat-number"><?php echo $total_tasks; ?></div>
                <div class="stat-label">Tasks</div>
            </div>
            <div class="stat-card pending">
                <i class="fas fa-clock stat-icon"></i>
                <div class="stat-label">Task Belum Selesai</div>
                <div class="stat-number"><?php echo $total_tasks - $total_completed; ?></div>
                <div class="stat-label">Tasks</div>
            </div>
            <div class="stat-card completed">
                <i class="fas fa-check-circle stat-icon"></i>
                <div class="stat-label">Task Selesai</div>
                <div class="stat-number"><?php echo $total_completed; ?></div>
                <div class="stat-label">Tasks</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-section">
            <div class="progress-header">
                <div class="progress-title">
                    <i class="fas fa-chart-line progress-icon"></i>
                    Progress Overview
                </div>
                <div class="progress-stats">
                    <?php echo $total_completed; ?> dari <?php echo $total_tasks; ?> task selesai
                </div>
            </div>

            <div class="progress">
                <div class="progress-bar"
                    role="progressbar"
                    style="width: <?php echo $progress; ?>%"
                    data-width="<?php echo $progress; ?>"
                    aria-valuenow="<?php echo $progress; ?>"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="progress-percentage">
                        <?php echo round($progress); ?>%
                    </div>
                </div>
            </div>

            <div class="progress-detail">
                <div class="progress-status">
                    <div class="status-item">
                        <span class="status-dot dot-completed"></span>
                        Selesai (<?php echo $total_completed; ?>)
                    </div>
                    <div class="status-item">
                        <span class="status-dot dot-pending"></span>
                        Pending (<?php echo $total_tasks - $total_completed; ?>)
                    </div>
                </div>
                <div class="progress-date">
                    Last updated: <?php echo date('d M Y'); ?>
                </div>
            </div>
        </div>



        <!-- Form Tambah Task - Design Baru -->
        <div class="add-task-section">
            <div class="card add-task-card">
                <div class="card-body">
                    <div class="add-task-header">
                        <h5><i class="fas fa-plus-circle"></i> Tambah Task Baru</h5>
                        <span class="task-subtitle">Tambahkan task baru ke dalam daftar</span>
                    </div>

                    <form action="" method="post" class="add-task-form">
                        <div class="form-floating mb-3">
                            <input type="text"
                                name="task"
                                class="form-control custom-input"
                                id="taskName"
                                placeholder="Nama Task"
                                required>
                            <label for="taskName">Nama Task</label>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="priority" class="form-select custom-select" id="taskPriority" required>
                                        <option value="">Pilih Prioritas</option>
                                        <option value="1">Low</option>
                                        <option value="2">Medium</option>
                                        <option value="3">High</option>
                                    </select>
                                    <label for="taskPriority">Prioritas</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date"
                                        name="due_date"
                                        class="form-control custom-input"
                                        id="taskDate"
                                        value="<?php echo date('Y-m-d'); ?>"
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                    <label for="taskDate">Tanggal</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea name="description"
                                class="form-control custom-textarea"
                                id="taskDescription"
                                style="height: 100px"
                                placeholder="Deskripsi Task"
                                required></textarea>
                            <label for="taskDescription">Deskripsi</label>
                        </div>

                        <div class="form-floating mb-4">
                            <select name="category" class="form-select custom-select" id="taskCategory" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category) { ?>
                                    <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                <?php } ?>
                            </select>
                            <label for="taskCategory">Kategori</label>
                        </div>

                        <button type="submit" name="add_task" class="btn-submit">
                            <i class="fas fa-plus"></i> Tambah Task
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Update tampilan menu filter -->
        <div class="menu-filter">
            <div class="menu-buttons">
                <a href="?filterV_status="
                    class="menu-btn <?php echo $filter_status === '' ? 'active' : ''; ?>">
                    Semua
                    <span class="menu-count"><?php echo $total_tasks; ?></span>
                </a>
                <a href="?filter_status=0"
                    class="menu-btn <?php echo $filter_status === '0' ? 'active' : ''; ?>">
                    Belum Selesai
                    <span class="menu-count">
                        <?php
                        // Hitung jumlah task yang belum selesai
                        $total_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM task WHERE status = '0'"))['total'];
                        echo $total_pending;
                        ?>
                    </span>
                </a>
                <a href="?filter_status=1"
                    class="menu-btn <?php echo $filter_status === '1' ? 'active' : ''; ?>">
                    Selesai
                    <span class="menu-count"><?php echo $total_completed; ?></span>
                </a>
            </div>
        </div>

        <!-- Filter dan Search -->
        <form method="get" class="d-flex mb-4 gap-2">
            <input type="hidden" name="filter_status" value="<?php echo $filter_status; ?>">
            <select name="filter_priority" class="form-select">
                <option value="">Semua Prioritas</option>
                <option value="1" <?php echo $filter_priority == '1' ? 'selected' : ''; ?>>Low</option>
                <option value="2" <?php echo $filter_priority == '2' ? 'selected' : ''; ?>>Medium</option>
                <option value="3" <?php echo $filter_priority == '3' ? 'selected' : ''; ?>>High</option>
            </select>

            <select name="filter_category" class="form-select">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category; ?>" <?php echo $filter_category == $category ? 'selected' : ''; ?>><?php echo $category; ?></option>
                <?php } ?>
            </select>

            <input type="date"
                name="filter_date"
                class="form-control"
                value="<?php echo $filter_date; ?>"
                placeholder="Pilih Tanggal">

            <input type="text"
                name="search"
                class="form-control"
                placeholder="Cari task..."
                value="<?php echo $search; ?>">

            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="button"
                onclick="window.location.href='index.php'"
                class="btn btn-secondary">
                Reset
            </button>
        </form>
        <hr class="my-4">

        <!-- Daftar Task -->
        <div class="row">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Tampilkan hanya task yang sesuai dengan filter status
                    if ($filter_status === '0' && $row['status'] != '0') {
                        continue; // Skip task yang sudah selesai
                    }
                    if ($filter_status === '1' && $row['status'] != '1') {
                        continue; // Skip task yang belum selesai
                    }
            ?>
                    <div class="col-md-4">
                        <div class="task-card <?php echo $row['priority'] == 3 ? 'high-priority' : ($row['priority'] == 2 ? 'medium-priority' : 'low-priority'); ?> <?php echo $row['pinned'] ? 'pinned' : ''; ?>">
                            <div class="task-header">
                                <div class="d-flex align-items-center gap-2">
                                    <button onclick="window.location.href='?pin=<?php echo $row['id'] ?>'"
                                        class="pin-btn <?php echo $row['pinned'] ? 'pinned' : ''; ?>"
                                        title="<?php echo $row['pinned'] ? 'Unpin' : 'Pin'; ?>">
                                        <i class="fas <?php echo $row['pinned'] ? 'fa-thumbtack' : 'fa-thumbtack'; ?>"></i>
                                    </button>
                                    <h5 class="task-title">
                                        <?php echo $row['task']; ?>
                                    </h5>
                                </div>
                                <span class="task-priority <?php echo 'priority-' . strtolower($row['priority'] == 1 ? 'low' : ($row['priority'] == 2 ? 'medium' : 'high')); ?>">
                                    <?php echo $row['priority'] == 1 ? 'Low' : ($row['priority'] == 2 ? 'Medium' : 'High'); ?>
                                </span>
                            </div>

                            <div class="task-meta">
                                <div class="task-meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('d M Y', strtotime($row['due_date'])); ?>
                                </div>
                                <div class="task-meta-item">
                                    <i class="fas fa-folder"></i>
                                    <?php echo $row['category']; ?>
                                </div>
                                <div class="task-meta-item">
                                    <i class="fas <?php echo $row['status'] == 0 ? 'fa-hourglass-half' : 'fa-check-circle'; ?>"></i>
                                    <?php echo $row['status'] == 0 ? 'In Progress' : 'Completed'; ?>
                                </div>
                            </div>

                            <div class="task-description">
                                <div class="task-description-content">
                                    <?php echo nl2br($row['description']); ?>
                                </div>
                            </div>

                            <div class="task-footer">
                                <span class="task-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo $row['category']; ?>
                                </span>

                                <div class="task-actions">
                                    <?php if ($row['status'] == 0) { ?>
                                        <button type="button" onclick="window.location.href='?complete=<?php echo $row['id'] ?>'" class="action-btn complete" title="Complete">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" onclick="window.location.href='?undo=<?php echo $row['id'] ?>'" class="action-btn" title="Undo">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                    <?php } ?>

                                    <!-- Tombol edit yang membuka modal -->
                                    <button type="button"
                                        class="action-btn edit"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?php echo $row['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" onclick="if(confirm('Are you sure you want to delete this task?')) window.location.href='?delete=<?php echo $row['id'] ?>'" class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="" method="post">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $row['id']; ?>">Edit Task</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Task</label>
                                            <input type="text" name="task" class="form-control" value="<?php echo $row['task']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Prioritas</label>
                                            <select name="priority" class="form-select" required>
                                                <option value="1" <?php echo $row['priority'] == 1 ? 'selected' : ''; ?>>Low</option>
                                                <option value="2" <?php echo $row['priority'] == 2 ? 'selected' : ''; ?>>Medium</option>
                                                <option value="3" <?php echo $row['priority'] == 3 ? 'selected' : ''; ?>>High</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal</label>
                                            <input type="date"
                                                name="due_date"
                                                class="form-control edit-date"
                                                value="<?php echo $row['due_date']; ?>"
                                                min="<?php echo date('Y-m-d'); ?>"
                                                onchange="validateDate(this)"
                                                required>
                                            <small class="text-danger date-error"></small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea name="description"
                                                class="form-control"
                                                style="min-height: 100px; resize: vertical;"
                                                required><?php echo $row['description']; ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kategori</label>
                                            <select name="category" class="form-select" required>
                                                <?php foreach ($categories as $category) { ?>
                                                    <option value="<?php echo $category; ?>" <?php echo $row['category'] == $category ? 'selected' : ''; ?>><?php echo $category; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_task" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p class='text-center text-secondary'>Tidak ada task ditemukan.</p>";
            }
            ?>
        </div>

        <!-- Pagination Design -->
        <nav aria-label="Task pagination" class="modern-pagination">
            <ul class="pagination-list">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=<?php echo ($page - 1); ?>&filter_priority=<?php echo $filter_priority; ?>&filter_category=<?php echo $filter_category; ?>&filter_date=<?php echo $filter_date; ?>&filter_status=<?php echo $filter_status; ?>&sort=<?php echo $sort; ?>&search=<?php echo $search; ?>"
                            class="pagination-arrow"
                            aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                // Tampilkan maksimal 5 halaman dengan halaman aktif di tengah
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $start_page + 4);

                if ($end_page - $start_page < 4) {
                    $start_page = max(1, $end_page - 4);
                }

                if ($start_page > 1): ?>
                    <li>
                        <a href="?page=1" class="pagination-link">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li><span class="pagination-ellipsis">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li>
                        <a href="?page=<?php echo $i; ?>&filter_priority=<?php echo $filter_priority; ?>&filter_category=<?php echo $filter_category; ?>&filter_date=<?php echo $filter_date; ?>&filter_status=<?php echo $filter_status; ?>&sort=<?php echo $sort; ?>&search=<?php echo $search; ?>"
                            class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li><span class="pagination-ellipsis">...</span></li>
                    <?php endif; ?>
                    <li>
                        <a href="?page=<?php echo $total_pages; ?>" class="pagination-link">
                            <?php echo $total_pages; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="?page=<?php echo ($page + 1); ?>&filter_priority=<?php echo $filter_priority; ?>&filter_category=<?php echo $filter_category; ?>&filter_date=<?php echo $filter_date; ?>&filter_status=<?php echo $filter_status; ?>&sort=<?php echo $sort; ?>&search=<?php echo $search; ?>"
                            class="pagination-arrow"
                            aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>

    <script src="..\assets\js\main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>

</html>