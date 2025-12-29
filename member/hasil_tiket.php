<?php
require "../config/database.php";

$asal    = $_GET['asal'] ?? '';
$tujuan  = $_GET['tujuan'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$sql = "
SELECT 
    j.jadwal_id,
    j.tanggal,
    j.jam_berangkat,
    j.jam_tiba,
    j.harga,
    ba.nama_bus,
    ba.deskripsi AS deskripsi_bus,
    at.nama_tipe,
    at.deskripsi AS deskripsi_tipe
FROM jadwal j
JOIN rute r ON j.rute_id = r.rute_id
JOIN bus_armada ba ON j.armada_id = ba.armada_id
JOIN armada_tipe at ON ba.tipe_id = at.tipe_id
WHERE r.asal_id = ?
AND r.tujuan_id = ?
AND j.tanggal = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$asal, $tujuan, $tanggal]);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hasil Pencarian Tiket</title>
<link rel="stylesheet" href="../aset/css/dashboard.css">
<style>
body { 
    background: #2f405a; 
    color: #333; 
}
.container { 
    max-width: 900px; 
    margin: auto; }
.card { 
    background: #fff; 
    border-radius: 20px; 
    padding: 20px; 
    margin-bottom: 20px; 
    box-shadow: 0 10px 25px rgba(0,0,0,.15); 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.card-body p { 
    margin: 6px 0; 
}
.harga { 
    font-weight: bold; 
    color: #2c3e50; 
}
.btn { 
    text-decoration: none; 
    background: #27ae60; 
    color: #fff; 
    padding: 8px 16px; 
    border-radius: 10px; 
    font-weight: bold; 
    transition: 0.2s; 
    cursor:pointer; 
    border: none;
}
.btn:hover { 
    background: #219150; 
}
.empty { 
    background: #fff; 
    padding: 15px;
     border-radius: 10px; 
     text-align: center; 
     color: #888; 
}

/* Modal */
.modal {
    display: none; 
    position: fixed; 
    z-index: 999; 
    left: 0; top: 0; 
    width: 100%; height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.5);
    animation: fadeIn 0.2s;
}

.modal-content {
    background-color: #fff;
    margin: 80px auto; 
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    background-color: #4e73df;
    color: #fff;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.modal-close {
    font-size: 24px;
    cursor: pointer;
}

.modal-close:hover { color: #ddd; }

.modal-body {
    padding: 20px;
    font-size: 14px;
    color: #333;
    max-height: 300px;
    overflow-y: auto;
    line-height: 1.5;
}

.modal-body p {
    margin-bottom: 12px;
}

.modal-footer {
    padding: 15px 20px;
    background-color: #f1f1f1;
    text-align: center;
}

.btn-lanjut {
    display: block;
    width: 100%;
    padding: 12px 0;
    background-color: #27ae60;
    color: #fff;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
}

.btn-lanjut:hover { background-color: #219150; }

/* Animasi fade in */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (max-width:768px){
    h2{
        text-align: center;
    }
    .card { 
        margin: auto 50px 20px;   
    }
}

@media (max-width:600px){
    .card { flex-direction: column; align-items: flex-start; gap: 10px; }
    .btn { width: 100%; text-align: center; }
}
</style>
</head>
<body>

<?php include __DIR__ . "/nav.php"; ?>

<div class="container">
<h2 style="color:white;">Hasil Pencarian Tiket</h2>

<?php if(!$data): ?>
    <div class="empty">Tidak ada jadwal tersedia.</div>
<?php endif; ?>

<?php foreach($data as $row): ?>
    <div class="card">
        <div class="card-body">
            <p><b>Bus:</b> <?= htmlspecialchars($row['nama_bus']) ?> (<?= htmlspecialchars($row['nama_tipe']) ?>)</p>
            <p><b>Jam:</b> <?= $row['jam_berangkat'] ?> - <?= $row['jam_tiba'] ?></p>
            <p class="harga">Rp<?= number_format($row['harga'],0,',','.') ?></p>
        </div>

        <div>
            <button class="btn show-modal" 
                data-deskripsi-bus="<?= htmlspecialchars($row['deskripsi_bus']) ?>" 
                data-deskripsi-tipe="<?= htmlspecialchars($row['deskripsi_tipe']) ?>" 
                data-href="pilih_kursi.php?jadwal_id=<?= $row['jadwal_id'] ?>">
                Pesan
            </button>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- Modal -->
<!-- Modal -->
<div class="modal" id="modalPopup">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Armada</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>Kelas:</strong> <span id="deskripsiTipe"></span></p>
            <p><strong>Deskripsi Bus:</strong> <span id="deskripsiBus"></span></p>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn btn-lanjut" id="btnLanjut">Lanjut Pesan</a>
        </div>
    </div>
</div>


<?php include __DIR__ . "/footer.php"; ?>

<script>
const modal = document.getElementById('modalPopup');
const closeBtn = document.querySelector('.modal-close');
const deskripsiTipe = document.getElementById('deskripsiTipe');
const deskripsiBus = document.getElementById('deskripsiBus');
const btnLanjut = document.getElementById('btnLanjut');

document.querySelectorAll('.show-modal').forEach(btn => {
    btn.addEventListener('click', () => {
        deskripsiTipe.textContent = btn.dataset.deskripsiTipe;
        deskripsiBus.textContent = btn.dataset.deskripsiBus;
        btnLanjut.href = btn.dataset.href;
        modal.style.display = 'block';
    });
});

closeBtn.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', e => { if(e.target==modal) modal.style.display='none'; });
</script>

</body>
</html>
