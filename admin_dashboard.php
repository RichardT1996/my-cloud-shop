<?php
// admin_dashboard.php - Admin panel for managing watches
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
if ($_SESSION['user_email'] !== 'admin@gmail.com') {
    header('Location: catalog.php');
    exit();
}

// DB connection
$serverName = "tcp:mycardiffmet1.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin",
    "PWD" => "password123!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    $errors = sqlsrv_errors();
    $msg = "Database connection failed";
    if ($errors != null) {
        foreach ($errors as $error) {
            $msg .= ": " . $error['message'];
        }
    }
    die("<p style='color:red;'>" . htmlspecialchars($msg) . "</p>");
}

// Fetch watches from database
$sql = "SELECT id, name, brand, price, description, image_url FROM watches ORDER BY brand, name";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    $errors = sqlsrv_errors();
    $msg = "Query failed";
    if ($errors != null) {
        foreach ($errors as $error) {
            $msg .= ": " . $error['message'];
        }
    }
    sqlsrv_close($conn);
    die("<p style='color:red;'>" . htmlspecialchars($msg) . "</p>");
}

$watches = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $watches[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; color: #f5f5f5; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; position: relative; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header .tagline { font-size: 12px; color: #e74c3c; letter-spacing: 2px; text-transform: uppercase; }
    .welcome { position: absolute; top: 30px; right: 40px; color: #888; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
    .welcome span { color: #fff; margin-left: 5px; }
    .welcome a { color: #fff; text-decoration: none; margin-left: 15px; padding: 8px 16px; border: 1px solid #333; transition: all 0.3s ease; }
    .welcome a:hover { background: #fff; color: #000; border-color: #fff; }
    .nav { background: #111; border-bottom: 1px solid #222; padding: 0; }
    .nav ul { list-style: none; display: flex; justify-content: center; max-width: 1200px; margin: 0 auto; }
    .nav li { margin: 0; }
    .nav a { display: block; padding: 18px 30px; color: #888; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; border-bottom: 2px solid transparent; }
    .nav a:hover, .nav a.active { color: #fff; background: rgba(255,255,255,0.05); border-bottom-color: #fff; }
    .container { max-width: 1400px; margin: 40px auto; padding: 0 40px; }
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid #222; }
    .dashboard-header h2 { color: #fff; font-size: 1.8em; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; }
    .btn-add { background: transparent; border: 1px solid #444; color: #fff; padding: 12px 30px; text-decoration: none; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; transition: all 0.3s ease; font-weight: 400; display: inline-block; }
    .btn-add:hover { background: #fff; color: #000; border-color: #fff; }
    .success-msg, .error-msg { padding: 15px; margin-bottom: 20px; border: 1px solid; font-size: 13px; }
    .success-msg { background: #001a00; border-color: #003300; color: #4ade80; }
    .error-msg { background: #1a0000; border-color: #330000; color: #ff6b6b; }
    table { width: 100%; border-collapse: collapse; background: #111; border: 1px solid #222; }
    thead { background: #000; border-bottom: 1px solid #222; }
    th { padding: 15px; text-align: left; font-weight: 400; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #999; border-bottom: 1px solid #222; }
    td { padding: 15px; border-bottom: 1px solid #222; font-size: 14px; color: #ccc; font-weight: 300; }
    tr:hover { background: #1a1a1a; }
    .watch-img { width: 60px; height: 60px; object-fit: contain; background: #1a1a1a; padding: 5px; }
    .actions { display: flex; gap: 10px; }
    .btn-edit, .btn-delete { padding: 6px 15px; border: 1px solid #444; background: transparent; color: #fff; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
    .btn-edit:hover { border-color: #4ade80; color: #4ade80; }
    .btn-delete:hover { border-color: #ff6b6b; color: #ff6b6b; }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; }
    .modal-content { background: #111; border: 1px solid #333; max-width: 600px; margin: 80px auto; padding: 40px; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #222; }
    .modal-header h3 { font-size: 1.5em; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; color: #fff; }
    .close { color: #999; font-size: 28px; cursor: pointer; transition: color 0.3s; }
    .close:hover { color: #fff; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #999; font-weight: 300; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; }
    .form-group input, .form-group textarea { width: 100%; padding: 12px 0; border: none; border-bottom: 1px solid #333; background: transparent; color: #fff; font-size: 14px; font-weight: 300; transition: all 0.3s ease; }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-bottom-color: #666; }
    .form-group textarea { resize: vertical; min-height: 80px; font-family: inherit; }
    .btn-submit { background: transparent; border: 1px solid #444; color: #fff; padding: 12px 30px; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: all 0.3s ease; width: 100%; margin-top: 10px; }
    .btn-submit:hover { background: #fff; color: #000; border-color: #fff; }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <div class="tagline">Admin Dashboard</div>
    <div class="welcome">
      Admin, <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="logout.php">Logout</a>
    </div>
  </div>
  
  <nav class="nav">
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="catalog.php">Catalog</a></li>
      <li><a href="admin_dashboard.php" class="active">Manage Products</a></li>
      <li><a href="view_users.php">Users</a></li>
      <li><a href="admin_orders.php">Orders</a></li>
    </ul>
  </nav>

  <div class="container">
    <div class="dashboard-header">
      <h2>Manage Products</h2>
      <a href="#" class="btn-add" onclick="openAddModal(); return false;">Add New Watch</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <div class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($watches)): ?>
      <p style="text-align:center; color:#666; padding:60px;">No watches in catalog. Add your first product.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Image</th>
            <th>Brand</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($watches as $watch): ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($watch['image_url']); ?>" class="watch-img" alt="Watch"></td>
              <td><?php echo htmlspecialchars($watch['brand']); ?></td>
              <td><?php echo htmlspecialchars($watch['name']); ?></td>
              <td>£<?php echo number_format($watch['price'], 0); ?></td>
              <td class="actions">
                <a href="#" class="btn-edit" onclick="openEditModal(<?php echo $watch['id']; ?>, '<?php echo addslashes($watch['name']); ?>', '<?php echo addslashes($watch['brand']); ?>', <?php echo $watch['price']; ?>, '<?php echo addslashes($watch['description']); ?>', '<?php echo addslashes($watch['image_url']); ?>'); return false;">Edit</a>
                <a href="#" class="btn-delete" onclick="if(confirm('Delete this watch?')) { deleteWatch(<?php echo $watch['id']; ?>); } return false;">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Add/Edit Modal -->
  <div id="watchModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Add Watch</h3>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form id="watchForm" method="post" action="admin_process.php">
        <input type="hidden" id="watchId" name="id" value="">
        <input type="hidden" id="action" name="action" value="add">
        
        <div class="form-group">
          <label for="brand">Brand</label>
          <input type="text" id="brand" name="brand" required>
        </div>
        
        <div class="form-group">
          <label for="name">Model Name</label>
          <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
          <label for="price">Price (£)</label>
          <input type="number" id="price" name="price" step="0.01" required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" required></textarea>
        </div>
        
        <div class="form-group">
          <label for="image_file">Watch Image</label>
          <input type="file" id="image_file" accept="image/*" style="color:#fff; padding:12px 0;">
          <input type="hidden" id="image_url" name="image_url" required>
          <div id="image_preview" style="margin-top:15px; display:none;">
            <img id="preview_img" style="max-width:200px; border:1px solid #333; padding:10px; background:#1a1a1a;">
          </div>
          <div id="upload_status" style="margin-top:10px; font-size:12px; color:#999;"></div>
        </div>
        
        <button type="submit" class="btn-submit" id="submitBtn">Save Watch</button>
      </form>
    </div>
  </div>

  <script>
    const UPLOAD_API = 'https://image-uploads-cdekethvcudth4hb.norwayeast-01.azurewebsites.net/api/upload_image';

    // Handle image file selection
    document.getElementById('image_file').addEventListener('change', async function(e) {
      const file = e.target.files[0];
      if (!file) return;
      
      // Show preview
      const reader = new FileReader();
      reader.onload = function(event) {
        document.getElementById('preview_img').src = event.target.result;
        document.getElementById('image_preview').style.display = 'block';
      };
      reader.readAsDataURL(file);
      
      // Upload to Azure
      document.getElementById('upload_status').textContent = 'Uploading...';
      document.getElementById('submitBtn').disabled = true;
      
      try {
        const base64 = await fileToBase64(file);
        
        const response = await fetch(UPLOAD_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            image: base64,
            filename: file.name
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          document.getElementById('image_url').value = data.url;
          document.getElementById('upload_status').textContent = '✓ Upload successful';
          document.getElementById('upload_status').style.color = '#4ade80';
          document.getElementById('submitBtn').disabled = false;
        } else {
          document.getElementById('upload_status').textContent = '✗ ' + (data.error || 'Upload failed');
          document.getElementById('upload_status').style.color = '#ff6b6b';
          document.getElementById('submitBtn').disabled = false;
        }
      } catch (error) {
        document.getElementById('upload_status').textContent = '✗ Upload error';
        document.getElementById('upload_status').style.color = '#ff6b6b';
        document.getElementById('submitBtn').disabled = false;
        console.error(error);
      }
    });
    
    function fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }
    
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'Add Watch';
      document.getElementById('action').value = 'add';
      document.getElementById('watchId').value = '';
      document.getElementById('watchForm').reset();
      document.getElementById('image_preview').style.display = 'none';
      document.getElementById('upload_status').textContent = '';
      document.getElementById('submitBtn').disabled = false;
      document.getElementById('watchModal').style.display = 'block';
    }

    function openEditModal(id, name, brand, price, description, image_url) {
      document.getElementById('modalTitle').textContent = 'Edit Watch';
      document.getElementById('action').value = 'edit';
      document.getElementById('watchId').value = id;
      document.getElementById('brand').value = brand;
      document.getElementById('name').value = name;
      document.getElementById('price').value = price;
      document.getElementById('description').value = description;
      document.getElementById('image_url').value = image_url;
      document.getElementById('preview_img').src = image_url;
      document.getElementById('image_preview').style.display = 'block';
      document.getElementById('upload_status').textContent = '';
      document.getElementById('submitBtn').disabled = false;
      document.getElementById('watchModal').style.display = 'block';
    }

    function closeModal() {
      document.getElementById('watchModal').style.display = 'none';
    }

    function deleteWatch(id) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'admin_process.php';
      
      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'action';
      actionInput.value = 'delete';
      
      const idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'id';
      idInput.value = id;
      
      form.appendChild(actionInput);
      form.appendChild(idInput);
      document.body.appendChild(form);
      form.submit();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('watchModal');
      if (event.target == modal) {
        closeModal();
      }
    }
  </script>
</body>
</html>
