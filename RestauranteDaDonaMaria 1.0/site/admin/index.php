<?php
error_reporting(E_ALL); ini_set('display_errors',1);
if(session_status()===PHP_SESSION_NONE){ @session_save_path(sys_get_temp_dir()); session_start(); }

require_once __DIR__.'/../db/config.php';
require_once __DIR__.'/../db/db.php';

if (!is_dir(UPLOADS_ROOT)) { @mkdir(UPLOADS_ROOT, 0755, true); }

$logged_in = isset($_SESSION['ok']) && $_SESSION['ok']===true;
if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* -------- login -------- */
if(isset($_POST['action']) && $_POST['action']==='login'){
  $pass = $_POST['password'] ?? '';
  try{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE username=?');
    $stmt->execute(['admin']);
    $row = $stmt->fetch();
    $ok  = $row && password_verify($pass, $row['password_hash']);
  }catch(Throwable $e){ $ok=false; }
  if(!$ok && password_verify($pass, ADMIN_PASSWORD_HASH)) $ok = true;

  if($ok){ $_SESSION['ok']=true; header('Location: index.php'); exit; }
  else { $error='Invalid password.'; }
}

/* -------- logout -------- */
if(isset($_GET['logout'])){ session_destroy(); header('Location: index.php'); exit; }

/* -------- ajax -------- */
if($logged_in && isset($_GET['ajax'])){
  header('Content-Type: application/json; charset=utf-8');
  $pdo = db();
  $method = $_POST['method'] ?? $_GET['method'] ?? '';

  try{
    /* list */
    if($method==='list'){
      $rows = $pdo->query('SELECT * FROM menu_items ORDER BY id DESC')->fetchAll();
      foreach($rows as &$r){
        $r['price']     = (float)$r['price'];
        $r['available'] = (bool)$r['available'];
        $r['featured']  = (bool)$r['featured'];
      }
      echo json_encode(['items'=>$rows]); exit;
    }

    /* create / update */
    if($method==='create' || $method==='update'){
      $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
      $name = trim($_POST['name'] ?? '');
      $price = floatval($_POST['price'] ?? 0);
      $img = trim($_POST['img'] ?? '');
      $tags = trim($_POST['tags'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $available = isset($_POST['available']) && $_POST['available']=='1' ? 1 : 0;
      $featured  = isset($_POST['featured'])  && $_POST['featured']=='1'  ? 1 : 0;

      if($name==='') throw new Exception('Name is required');

      if($method==='create'){
        $stmt=$pdo->prepare('INSERT INTO menu_items (name, price, img, tags, description, available, featured) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$name,$price,$img,$tags,$description,$available,$featured]);
        echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]); exit;
      } else {
        $stmt=$pdo->prepare('UPDATE menu_items SET name=?, price=?, img=?, tags=?, description=?, available=?, featured=? WHERE id=?');
        $stmt->execute([$name,$price,$img,$tags,$description,$available,$featured,$id]);
        echo json_encode(['ok'=>true,'id'=>$id]); exit;
      }
    }

    /* delete */
    if($method==='delete'){
      $id=intval($_POST['id']??0);
      $stmt=$pdo->prepare('DELETE FROM menu_items WHERE id=?');
      $stmt->execute([$id]);
      echo json_encode(['ok'=>true]); exit;
    }

    /* ------------ BULK SAVE (Save all) ------------ */
    if($method==='bulk'){
      $payload = json_decode($_POST['items'] ?? '[]', true);
      if(!is_array($payload)) { echo json_encode(['ok'=>false,'error'=>'bad_payload']); exit; }

      $pdo->beginTransaction();
      try{
        $updated=0; $created=0; $skipped=0;

        $stmtUpd = $pdo->prepare("UPDATE menu_items
          SET name=?, price=?, img=?, tags=?, description=?, available=?, featured=?
          WHERE id=?");

        $stmtIns = $pdo->prepare("INSERT INTO menu_items
          (name, price, img, tags, description, available, featured)
          VALUES (?,?,?,?,?,?,?)");

        foreach($payload as $row){
          $id    = isset($row['id']) ? intval($row['id']) : 0;
          $name  = trim((string)($row['name'] ?? ''));
          $price = (float)($row['price'] ?? 0);
          $img   = trim((string)($row['img'] ?? ''));
          $tags  = trim((string)($row['tags'] ?? ''));
          $desc  = trim((string)($row['description'] ?? ''));
          $avail = !empty($row['available']) ? 1 : 0;
          $feat  = !empty($row['featured'])  ? 1 : 0;

          if($name===''){ $skipped++; continue; }

          if($id > 0){
            $stmtUpd->execute([$name,$price,$img,$tags,$desc,$avail,$feat,$id]);
            $updated += ($stmtUpd->rowCount()?1:0);
          } else {
            $stmtIns->execute([$name,$price,$img,$tags,$desc,$avail,$feat]);
            $created++;
          }
        }

        $pdo->commit();
        echo json_encode(['ok'=>true,'updated'=>$updated,'created'=>$created,'skipped'=>$skipped]); exit;
      } catch(Throwable $e){
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>'server_error','message'=>$e->getMessage()]); exit;
      }
    }

    echo json_encode(['error'=>'unknown_method']);
  }catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['error'=>'server_error','message'=>$e->getMessage()]);
  }
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Menu Manager</title>
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/styles.css?v=<?php echo @filemtime(__DIR__.'/../assets/css/styles.css'); ?>"/>
<style>
  .admin-topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
  .upload-inline{display:flex;align-items:center;gap:.5rem}
  .upload-inline .preview{display:inline-flex;width:42px;height:42px;border-radius:8px;overflow:hidden;border:1px solid #eee}
  .upload-inline .preview img{width:100%;height:100%;object-fit:cover}
</style>
</head>
<body>
<div class="admin">
  <div class="admin-topbar">
    <a class="btn link" href="<?php echo BASE_PATH; ?>/index.php">← View site</a>
    <?php if($logged_in): ?>
      <div style="display:flex;gap:.5rem">
        <a class="btn link" href="<?php echo BASE_PATH; ?>/menu.php" target="_blank" rel="noopener">Open public menu</a>
        <a class="btn secondary" href="?logout=1">Logout</a>
      </div>
    <?php endif; ?>
  </div>

<?php if(!$logged_in): ?>
  <h1>Menu Manager</h1>
  <?php if(!empty($error)) echo '<div class="notice" style="background:#fdecea;border-color:#f5c2c7;color:#842029">',$error,'</div>'; ?>
  <form method="post">
    <input type="hidden" name="action" value="login"/>
    <label>Password <input type="password" name="password" required/></label>
    <div class="actions"><button class="btn">Log in</button></div>
  </form>

<?php else: ?>
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h1>Menu Manager</h1>
  </div>
  <div class="muted">Create, edit, delete items. Upload images, and the site will auto-generate responsive sizes.</div>

  <div class="actions" style="justify-content:flex-start">
    <button class="btn" id="add">Add item</button>
    <button class="btn" id="reload">Reload</button>
    <button class="btn" id="saveAll">Save all</button>
  </div>

  <table id="grid">
    <thead><tr>
      <th style="width:60px">ID</th><th>Name</th><th style="width:100px">Price</th>
      <th>Image</th><th>Tags (comma)</th><th>Description</th>
      <th style="width:70px">Avail</th><th style="width:80px">Featured</th><th style="width:140px"></th>
    </tr></thead>
    <tbody></tbody>
  </table>
  <div id="msg" class="muted" style="margin-top:1rem;"></div>
<?php endif; ?>
</div>

<?php if($logged_in): ?>
<script>
const CSRF = <?php echo json_encode($csrf); ?>;
const $ = s => document.querySelector(s);

function imgCell(i){
  const preview = i.img ? `<span class="preview"><img src="${i.img}" alt="" /></span>` : '';
  return `<div class="upload-inline">
    ${preview}
    <input type="text" class="img" placeholder="Image URL" value="${(i.img||'').replace(/"/g,'&quot;')}" style="flex:1">
    <input type="file" class="file" accept="image/*">
    <button class="btn link up">Upload</button>
    <button class="btn link rm">Remove</button>
  </div>`;
}

const rowHtml = (i) => `
  <tr data-id="${i.id||''}">
    <td>${i.id||''}</td>
    <td><input type="text" class="name" value="${(i.name||'').replace(/"/g,'&quot;')}"></td>
    <td><input type="number" step="0.01" class="price" value="${i.price ?? ''}"></td>
    <td>${imgCell(i)}</td>
    <td><input type="text" class="tags" value="${(i.tags||'')}"></td>
    <td><textarea class="description" rows="2">${(i.description||'')}</textarea></td>
    <td style="text-align:center"><input type="checkbox" class="available" ${i.available ? 'checked':''}></td>
    <td style="text-align:center"><input type="checkbox" class="featured" ${i.featured ? 'checked':''}></td>
    <td>
      <button class="btn save">Save</button>
      <button class="btn danger del">Delete</button>
    </td>
  </tr>`;

async function load() {
  $('#msg').textContent = 'Loading...';
  const r = await fetch('index.php?ajax=1&method=list', {cache:'no-store'});
  const j = await r.json();
  const rows = (j.items||[]).map(x => ({...x, tags:(x.tags||'')}));
  $('#grid tbody').innerHTML = rows.map(rowHtml).join('');
  $('#msg').textContent = `Loaded ${rows.length} items.`;
  DIRTY = false;
}

function readRow(tr){
  const q = sel => tr.querySelector(sel);
  return {
    id: tr.getAttribute('data-id') || null,
    name: q('.name').value.trim(),
    price: q('.price').value,
    img: q('.img').value.trim(),
    tags: q('.tags').value.trim(),
    description: q('.description').value.trim(),
    available: q('.available').checked ? '1':'0',
    featured: q('.featured').checked ? '1':'0'
  };
}

/* dirty tracking */
let DIRTY = false;
document.addEventListener('input', e => { if (e.target.closest('#grid')) DIRTY = true; });
window.addEventListener('beforeunload', e => { if (!DIRTY) return; e.preventDefault(); e.returnValue=''; });
function clearDirty(){ DIRTY = false; }

/* buttons */
document.getElementById('reload').addEventListener('click', load);
document.getElementById('add').addEventListener('click', () => {
  const empty = {id:'', name:'', price:'', img:'', tags:'', description:'', available:1, featured:0};
  document.querySelector('#grid tbody').insertAdjacentHTML('afterbegin', rowHtml(empty));
  DIRTY = true;
});
document.getElementById('saveAll').addEventListener('click', async () => {
  const rows = [...document.querySelectorAll('#grid tbody tr')].map(readRow);
  for(const r of rows){ if(!r.name){ $('#msg').textContent='Name is required on all rows.'; return; } }
  const form = new FormData();
  form.append('method','bulk');
  form.append('items', JSON.stringify(rows));
  $('#msg').textContent = 'Saving all…';
  try{
    const r = await fetch('index.php?ajax=1', { method:'POST', body: form });
    const j = await r.json();
    if(j.ok){
      $('#msg').textContent = `Saved: ${j.updated} updated, ${j.created} created, ${j.skipped} skipped.`;
      clearDirty();
      await load();
    } else {
      $('#msg').textContent = 'Bulk save failed.';
    }
  }catch(e){
    $('#msg').textContent = 'Network error during bulk save.';
  }
});

/* row actions */
document.querySelector('#grid').addEventListener('click', async e => {
  const tr = e.target.closest('tr'); if(!tr) return;

  if (e.target.classList.contains('save')){
    const data = readRow(tr);
    const method = data.id ? 'update' : 'create';
    const form = new FormData();
    Object.entries(data).forEach(([k,v])=>form.append(k,v));
    form.append('method', method);
    const r = await fetch('index.php?ajax=1', {method:'POST', body: form});
    const j = await r.json();
    if (j.ok){
      $('#msg').textContent = 'Saved.';
      if (j.id) tr.setAttribute('data-id', j.id);
      DIRTY = false;
      load();
    } else {
      $('#msg').textContent = 'Save failed.';
    }
  }

  if (e.target.classList.contains('del')){
    const id = tr.getAttribute('data-id');
    if (!id) { tr.remove(); return; }
    if (!confirm('Delete this item?')) return;
    const form = new FormData(); form.append('method','delete'); form.append('id', id);
    const r = await fetch('index.php?ajax=1', {method:'POST', body: form});
    const j = await r.json();
    if (j.ok){ tr.remove(); $('#msg').textContent = 'Deleted.'; DIRTY = true; }
    else $('#msg').textContent = 'Delete failed.';
  }

  if (e.target.classList.contains('up')){
    e.preventDefault();
    const fileInput = tr.querySelector('input.file');
    fileInput.click();
  }
  if (e.target.classList.contains('rm')){
    e.preventDefault();
    tr.querySelector('input.img').value='';
    const pv = tr.querySelector('.preview'); if(pv) pv.remove();
    DIRTY = true;
  }
});

/* upload handler */
document.querySelector('#grid').addEventListener('change', async e => {
  if (!e.target.classList.contains('file')) return;
  const tr = e.target.closest('tr');
  const name = tr.querySelector('.name').value.trim() || 'image';
  const id = tr.getAttribute('data-id') || 'new';
  const file = e.target.files[0];
  if (!file) return;
  const form = new FormData();
  form.append('csrf', CSRF);
  form.append('name', name);
  form.append('menu_id', id);
  form.append('file', file);
  $('#msg').textContent = 'Uploading…';
  try{
    const r = await fetch('upload.php', {method:'POST', body: form});
    const j = await r.json();
    if (j.ok){
      tr.querySelector('input.img').value = j.url;
      const oldPrev = tr.querySelector('.preview'); if(oldPrev) oldPrev.remove();
      const up = tr.querySelector('.upload-inline');
      up.insertAdjacentHTML('afterbegin', `<span class="preview"><img src="${j.url}" alt=""></span>`);
      $('#msg').textContent = 'Uploaded ✔';
      DIRTY = true;
    } else {
      $('#msg').textContent = 'Upload failed: ' + (j.message || 'Unknown error');
    }
  }catch(err){
    $('#msg').textContent = 'Upload failed.';
  }
  e.target.value = ''; // reset for same-file reupload
});

load();
</script>
<?php endif; ?>
</body>
</html>
