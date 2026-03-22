<?php 
include('functions.php');

if(isset($_POST['login']) && $_POST['p'] === $CONFIG['password']) {
    $_SESSION['admin_access'] = true;
    header("Location: admin.php"); exit;
}
if(isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

/* ============================================================
   LOGIN PAGE
   ============================================================ */
if (!is_admin()): ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Riservato – <?= htmlspecialchars($CONFIG['istituto']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --blue-dark:#003a7a; --blue-main:#004a99; }
        body {
            min-height:100vh;
            background:linear-gradient(135deg,#003a7a 0%,#0062cc 60%,#004a99 100%);
            display:flex; align-items:center; justify-content:center;
            font-family:'Segoe UI',system-ui,sans-serif;
        }
        .login-wrapper { width:100%; max-width:420px; padding:1rem; }
        .login-card {
            background:rgba(255,255,255,0.97); border-radius:20px;
            padding:2.5rem 2rem; box-shadow:0 20px 60px rgba(0,0,0,0.35);
        }
        .login-logo {
            width:70px; height:70px; background:var(--blue-main); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 1.2rem; font-size:2rem;
        }
        .login-card h4 { color:var(--blue-dark); font-weight:700; font-size:1.3rem; margin-bottom:0.25rem; }
        .subtitle { color:#6b7a99; font-size:.87rem; margin-bottom:1.8rem; }
        .form-label { font-size:.82rem; font-weight:600; color:#334166; letter-spacing:.04em; text-transform:uppercase; }
        .form-control {
            border-radius:10px; border:1.5px solid #c8d4e8;
            padding:.65rem 1rem; font-size:.97rem;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-control:focus { border-color:var(--blue-main); box-shadow:0 0 0 3px rgba(0,74,153,.15); }
        .password-wrapper { position:relative; }
        .toggle-pw {
            position:absolute; right:12px; top:50%; transform:translateY(-50%);
            background:none; border:none; cursor:pointer; color:#6b7a99; font-size:1.1rem; padding:0;
        }
        .btn-login {
            background:var(--blue-main); color:#fff; border:none; border-radius:10px;
            padding:.7rem; font-weight:600; font-size:1rem; width:100%;
            transition:background .2s,transform .1s;
        }
        .btn-login:hover { background:var(--blue-dark); transform:translateY(-1px); }
        .login-footer { text-align:center; color:rgba(255,255,255,.6); font-size:.78rem; margin-top:1.5rem; }
        <?php if(isset($_POST['login'])): ?>
        .login-card { animation:shake .4s ease; }
        @keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-6px)}80%{transform:translateX(6px)}}
        <?php endif; ?>
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">🏫</div>
        <h4 class="text-center"><?= htmlspecialchars($CONFIG['istituto']) ?></h4>
        <p class="subtitle text-center">Area di amministrazione riservata</p>
        <?php if(isset($_POST['login'])): ?>
        <div class="alert alert-danger py-2 text-center" style="border-radius:10px;font-size:.87rem;">
            ⚠️ Password non corretta. Riprova.
        </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="p" id="pw-field" class="form-control pe-5" placeholder="Inserisci la password" required autofocus>
                    <button type="button" class="toggle-pw" onclick="togglePw()">👁</button>
                </div>
            </div>
            <button type="submit" name="login" class="btn-login mt-1">Accedi</button>
        </form>
    </div>
    <p class="login-footer">© <?= date('Y') ?> – <?= htmlspecialchars($CONFIG['footer_text']) ?></p>
</div>
<script>function togglePw(){const f=document.getElementById('pw-field');f.type=f.type==='password'?'text':'password';}</script>
</body>
</html>
<?php exit; endif;

/* ============================================================
   LOGICA BACKEND
   ============================================================ */
$db = load_db();

// Aggiungi categoria
if(isset($_POST['add_cat']) && !empty(trim($_POST['cat_name']))) {
    $db['categories'][] = ['id'=>uniqid(),'name'=>trim($_POST['cat_name']),'order'=>count($db['categories'])];
    save_db($db); header("Location: admin.php?ok=cat"); exit;
}

// Elimina categoria (e relativi items)
if(isset($_GET['del_cat'])) {
    $cid = $_GET['del_cat'];
    $db['categories'] = array_values(array_filter($db['categories'], fn($c)=>$c['id']!==$cid));
    $db['items']      = array_values(array_filter($db['items'],      fn($i)=>$i['cat_id']!==$cid));
    save_db($db); header("Location: admin.php"); exit;
}

// Rinomina categoria
if(isset($_POST['rename_cat']) && !empty(trim($_POST['cat_new_name']))) {
    $cid = $_POST['rename_cat_id'];
    foreach($db['categories'] as &$c) {
        if($c['id']===$cid) { $c['name'] = trim($_POST['cat_new_name']); break; }
    }
    save_db($db); header("Location: admin.php?ok=rename"); exit;
}

// Salva impostazioni sito
if(isset($_POST['save_settings'])) {
    $map = [
        'titolo_sito'  => trim($_POST['titolo_sito']),
        'istituto'     => trim($_POST['istituto']),
        'footer_text'  => trim($_POST['footer_text']),
        'footer_text2' => trim($_POST['footer_text2']),
        'sottotitolo'   => trim($_POST['sottotitolo']),
        'novita_giorni' => (int)$_POST['novita_giorni'],
        'url_focus'     => trim($_POST['url_focus']),
        'cols_desktop' => trim($_POST['cols_desktop']),
    ];
    $lines = file('functions.php');
    $out = [];
    foreach($lines as $line) {
        foreach($map as $key => $val) {
            // Match:  'key' => 'anything'  or  "key" => "anything"
            if(preg_match('/([\'"]'.$key.'[\'"]\s*=>\s*[\'"])/', $line)) {
                $line = preg_replace(
                    '/([\'"]'.$key.'[\'"]\s*=>\s*[\'"])[^\'"]*([\'"])/',
                    '${1}'.str_replace(['\\','$'], ['\\\\','\$'], $val).'${2}',
                    $line
                );
            }
        }
        $out[] = $line;
    }
    file_put_contents('functions.php', implode('', $out));
    header("Location: admin.php?ok=settings"); exit;
}



// Aggiungi strumento
if(isset($_POST['add_item'])) {
    $db['items'][] = [
        'id'           => uniqid(),
        'cat_id'       => $_POST['cat_id'],
        'title'        => trim($_POST['title']),
        'url'          => trim($_POST['url']),
        'desc'         => trim($_POST['desc']),
        'publish_date' => trim($_POST['publish_date']??''),
        'order'        => count($db['items'])
    ];
    save_db($db); header("Location: admin.php?ok=item"); exit;
}

// Modifica strumento
if(isset($_POST['edit_item'])) {
    $eid = $_POST['edit_id'];
    foreach($db['items'] as &$it) {
        if($it['id']===$eid) {
            $it['cat_id']       = $_POST['cat_id'];
            $it['title']        = trim($_POST['title']);
            $it['url']          = trim($_POST['url']);
            $it['desc']         = trim($_POST['desc']);
            $it['publish_date'] = trim($_POST['publish_date']??'');
            break;
        }
    }
    save_db($db); header("Location: admin.php?ok=edit"); exit;
}

// Elimina strumento
if(isset($_GET['del_item'])) {
    $iid = $_GET['del_item'];
    $db['items'] = array_values(array_filter($db['items'], fn($i)=>$i['id']!==$iid));
    save_db($db); header("Location: admin.php"); exit;
}

// AJAX: riordina items (e aggiorna cat_id se spostato tra aree)
if(isset($_POST['update_order'])) {
    $orders  = $_POST['order'];   // array di id nell'ordine corretto
    $cat_map = $_POST['cat_map'] ?? [];  // array id=>cat_id per riassegnazione area
    foreach($db['items'] as &$it) {
        $pos = array_search($it['id'], $orders);
        if($pos !== false) {
            $it['order'] = (int)$pos;
            // Aggiorna cat_id se l'item è stato spostato in un'altra area
            if(isset($cat_map[$it['id']])) {
                $it['cat_id'] = $cat_map[$it['id']];
            }
        }
    }
    unset($it);
    save_db($db); exit("OK");
}

// AJAX: riordina categorie
if(isset($_POST['update_cat_order'])) {
    $orders = $_POST['order'];
    foreach($db['categories'] as &$c) {
        $pos = array_search($c['id'], $orders);
        if($pos!==false) $c['order'] = (int)$pos;
    }
    save_db($db); exit("OK");
}

usort($db['categories'], fn($a,$b)=>$a['order']<=>$b['order']);
usort($db['items'],      fn($a,$b)=>$a['order']<=>$b['order']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <title>Admin – <?= htmlspecialchars($CONFIG['istituto']) ?></title>
    <style>
        :root { --blue-main:#004a99; --blue-dark:#003a7a; --blue-light:#e8f0fb; }
        body { font-family:'Segoe UI',system-ui,sans-serif; background:#eef2f7; }

        .topbar {
            background:var(--blue-main); color:#fff; padding:14px 24px;
            display:flex; align-items:center; justify-content:space-between;
            border-radius:0 0 16px 16px; margin-bottom:24px;
            box-shadow:0 4px 16px rgba(0,74,153,.2);
        }
        .topbar h1 { font-size:1.05rem; margin:0; font-weight:700; }
        .topbar small { opacity:.7; font-size:.75rem; }

        .card-section {
            background:#fff; border-radius:16px; padding:1.5rem;
            box-shadow:0 2px 12px rgba(0,74,153,.07); margin-bottom:1.5rem;
        }
        .card-section > h5 {
            color:var(--blue-dark); font-weight:700; font-size:.88rem;
            text-transform:uppercase; letter-spacing:.06em;
            border-left:4px solid var(--blue-main); padding-left:10px; margin-bottom:1.1rem;
        }

        .form-control,.form-select { border-radius:8px; border:1.5px solid #c8d4e8; font-size:.9rem; }
        .form-control:focus,.form-select:focus { border-color:var(--blue-main); box-shadow:0 0 0 3px rgba(0,74,153,.12); }
        label.form-label { font-size:.75rem; font-weight:600; color:#55627a; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.3rem; }
        .btn-primary  { background:var(--blue-main); border-color:var(--blue-main); border-radius:8px; }
        .btn-primary:hover { background:var(--blue-dark); border-color:var(--blue-dark); }
        .btn-success,.btn-danger,.btn-warning,.btn-secondary { border-radius:8px; }

        /* --- Category section --- */
        .cat-wrapper { margin-bottom:8px; }
        .cat-section-header {
            display:flex; align-items:center; justify-content:space-between;
            border-bottom:3px solid var(--blue-main); padding:8px 4px 8px;
            margin-bottom:8px;
        }
        .cat-section-header h6 {
            color:var(--blue-main); font-weight:800; font-size:.88rem;
            text-transform:uppercase; letter-spacing:.08em; margin:0;
        }
        .cat-handle { color:#99aabb; font-size:1.3rem; margin-right:10px; cursor:grab; flex-shrink:0; }
        .cat-handle:active { cursor:grabbing; }

        /* --- Item row --- */
        .item-row {
            display:flex; align-items:center; gap:10px;
            background:#fff; border:1.5px solid #dde6f0; border-radius:10px;
            padding:9px 12px; margin-bottom:6px; transition:border-color .15s, background .15s;
        }
        .item-row:hover { border-color:#a8c4e0; background:#f5f9ff; }
        .item-handle { cursor:grab; color:#bbc; font-size:1rem; flex-shrink:0; }
        .item-handle:active { cursor:grabbing; }

        .item-emoji { width:30px; height:30px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
        .item-info { flex:1; min-width:0; }
        .item-info strong { font-size:.88rem; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .item-url { font-size:.7rem; color:#7899bb; font-family:monospace; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
        .item-date-badge {
            display:inline-flex; align-items:center; gap:4px;
            background:#f0f6ff; color:#3366aa; font-size:.68rem; font-weight:600;
            border:1px solid #ccddf5; border-radius:5px; padding:1px 7px; margin-top:2px;
        }
        .item-actions { display:flex; gap:5px; flex-shrink:0; }

        .badge-cat {
            background:var(--blue-light); color:var(--blue-main);
            font-weight:600; font-size:.72rem; border-radius:6px; padding:4px 10px;
        }


        /* Modal */
        .modal-header { background:var(--blue-main); color:#fff; border-radius:12px 12px 0 0; }
        .modal-header .btn-close { filter:invert(1); }
        .modal-content { border-radius:12px; border:none; box-shadow:0 20px 60px rgba(0,0,0,.2); }

        /* Empty state */
        .empty-cat { color:#aab; font-size:.82rem; font-style:italic; padding:6px 4px 10px; }

        /* Inline icon button (no bg) */
        .btn-icon-inline {
            background:none; border:none; padding:0 2px; cursor:pointer;
            font-size:.85rem; line-height:1; opacity:.7;
        }
        .btn-icon-inline:hover { opacity:1; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h1>🛠️ Gestione Contenuti</h1>
        <small><?= htmlspecialchars($CONFIG['istituto']) ?></small>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-light btn-sm">🌐 Vai al sito</a>
        <a href="?logout" class="btn btn-outline-light btn-sm">Esci ↩</a>
    </div>
</div>

<div class="container-fluid px-3 px-md-4" style="max-width:980px;">

    <?php
    $ok_msgs = ['cat'=>'✅ Area creata!','item'=>'✅ Strumento aggiunto!','edit'=>'✅ Strumento aggiornato!','rename'=>'✅ Area rinominata!','settings'=>'✅ Impostazioni salvate!'];
    if(isset($_GET['ok']) && isset($ok_msgs[$_GET['ok']])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 py-2" role="alert">
        <?= $ok_msgs[$_GET['ok']] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ===== 1. AREE ===== -->
    <div class="card-section">
        <h5>1. Aree / Categorie</h5>
        <form method="POST" class="row g-2 align-items-end mb-3">
            <div class="col-sm-8">
                <label class="form-label">Nome nuova area</label>
                <input type="text" name="cat_name" class="form-control" placeholder="Es: Didattica e Innovazione" required>
            </div>
            <div class="col-sm-4">
                <button type="submit" name="add_cat" class="btn btn-success w-100">＋ Aggiungi Area</button>
            </div>
        </form>
        <?php if(!empty($db['categories'])): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach($db['categories'] as $c): ?>
            <span class="badge-cat d-flex align-items-center gap-2">
                📂 <?= htmlspecialchars($c['name']) ?>
                <button type="button"
                    class="btn-icon-inline text-primary"
                    title="Rinomina"
                    onclick="openRenameModal('<?= htmlspecialchars(addslashes($c['id'])) ?>','<?= htmlspecialchars(addslashes($c['name'])) ?>')">✏️</button>
                <a href="?del_cat=<?= $c['id'] ?>" class="text-danger text-decoration-none fw-bold"
                   onclick="return confirm('Eliminare l\'area «<?= htmlspecialchars(addslashes($c['name'])) ?>» e tutte le CARD presenti?')">×</a>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== 0. IMPOSTAZIONI SITO ===== -->
    <div class="card-section">
        <h5>0. Impostazioni Sito</h5>
        <?php
        // Leggi valori attuali da functions.php
        $cfg_raw = file_get_contents('functions.php');
        function getCfgVal($raw, $key) {
            // cerca sia 'key' => 'val' che "key" => "val"
            if(preg_match('/[\'"]'.$key.'[\'"]\s*=>\s*[\'"]([^\'"]*)[\'"]/', $raw, $m)) return $m[1];
            return '';
        }
        $s_titolo   = getCfgVal($cfg_raw,'titolo_sito');
        $s_istituto = getCfgVal($cfg_raw,'istituto');
        $s_footer1  = getCfgVal($cfg_raw,'footer_text');
        $s_footer2  = getCfgVal($cfg_raw,'footer_text2');
        $s_sottotitolo = getCfgVal($cfg_raw,'sottotitolo');
        $s_novita   = getCfgVal($cfg_raw,'novita_giorni') ?: '30';
        $s_cols     = getCfgVal($cfg_raw,'cols_desktop') ?: '3';
        $s_url_focus = getCfgVal($cfg_raw,'url_focus');
        ?>
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Titolo sito (tab browser)</label>
                <input type="text" name="titolo_sito" class="form-control" value="<?= htmlspecialchars($s_titolo) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nome istituto</label>
                <input type="text" name="istituto" class="form-control" value="<?= htmlspecialchars($s_istituto) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Sottotitolo home (sotto il nome istituto)</label>
                <input type="text" name="sottotitolo" class="form-control" value="<?= htmlspecialchars($s_sottotitolo) ?>">
            </div>
            <div class="col-12">
                <label class="form-label">🔗 URL QR code stampa <small class="text-muted">(QR visibile in alto a destra nella versione stampata)</small></label>
                <input type="url" name="url_focus" class="form-control" placeholder="https://capuanadeamicis.it/focus" value="<?= htmlspecialchars($s_url_focus) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Footer riga 1 (copyright ecc.)</label>
                <input type="text" name="footer_text" class="form-control" value="<?= htmlspecialchars($s_footer1) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Footer riga 2 (es: Realizzato per…)</label>
                <input type="text" name="footer_text2" class="form-control" value="<?= htmlspecialchars($s_footer2) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Colonne desktop</label>
                <select name="cols_desktop" class="form-select">
                    <option value="1" <?= $s_cols=='1'?'selected':'' ?>>1 colonna</option>
                    <option value="2" <?= $s_cols=='2'?'selected':'' ?>>2 colonne</option>
                    <option value="3" <?= $s_cols=='3'||!$s_cols?'selected':'' ?>>3 colonne</option>
                    <option value="4" <?= $s_cols=='4'?'selected':'' ?>>4 colonne</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Badge "Novità" (giorni)</label>
                <input type="number" name="novita_giorni" class="form-control"
                       min="1" max="365" value="<?= htmlspecialchars($s_novita) ?>">
                <small class="text-muted" style="font-size:.7rem;">Giorni dalla data pub.</small>
            </div>
            <div class="col-12">
                <button type="submit" name="save_settings" class="btn btn-primary">💾 Salva impostazioni</button>
                <small class="text-muted ms-3">Le modifiche si applicano immediatamente alla home pubblica.</small>
            </div>
        </form>
    </div>

    <!-- ===== 2. AGGIUNGI STRUMENTO ===== -->
    <div class="card-section">
        <h5>2. Aggiungi Nuovo Strumento</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Area</label>
                <select name="cat_id" class="form-select" required>
                    <?php foreach($db['categories'] as $c): ?>
                        <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Titolo</label>
                <input type="text" name="title" class="form-control" placeholder="Es: Biblioteca Scolastica" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">URL</label>
                <input type="url" name="url" class="form-control" placeholder="https://..." required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Descrizione breve</label>
                <input type="text" name="desc" class="form-control" placeholder="Breve descrizione">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data pubblicazione / implementazione</label>
                <input type="date" name="publish_date" class="form-control">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add_item" class="btn btn-primary w-100">Salva</button>
            </div>
        </form>
    </div>

    <!-- ===== 3. GESTIONE VISIVA PER CATEGORIA ===== -->
    <div class="card-section">
        <h5>3. Gestisci e Ordina Card</h5>
        <p class="text-muted small mb-3">
            <strong>⠿</strong> Trascina le aree per riordinarle &nbsp;|&nbsp;
            <strong>☰</strong> Trascina le CARD per riordinarle (anche tra aree diverse)
        </p>

        <div id="cat-sortable">
        <?php foreach($db['categories'] as $cat):
            $cat_items = array_values(array_filter($db['items'], fn($i)=>$i['cat_id']===$cat['id']));
            usort($cat_items, fn($a,$b)=>$a['order']<=>$b['order']);
        ?>
            <div class="cat-wrapper" data-cat-id="<?= htmlspecialchars($cat['id']) ?>">
                <div class="cat-section-header">
                    <div class="d-flex align-items-center">
                        <span class="cat-handle">⠿</span>
                        <h6><?= htmlspecialchars($cat['name']) ?></h6>
                    </div>
                    <span class="badge bg-light text-secondary border" style="font-size:.7rem;">
                        <?= count($cat_items) ?> strument<?= count($cat_items)===1?'o':'i' ?>
                    </span>
                </div>

                <div class="items-sortable" data-cat="<?= htmlspecialchars($cat['id']) ?>">
                <?php if(empty($cat_items)): ?>
                    <div class="empty-cat">Nessuno strumento in questa area.</div>
                <?php endif; ?>
                <?php foreach($cat_items as $it): ?>
                    <div class="item-row" data-id="<?= htmlspecialchars($it['id']) ?>">
                        <span class="item-handle">☰</span>

                        <div class="item-info">
                            <strong><?= htmlspecialchars($it['title']) ?></strong>
                            <span class="item-url"><?= htmlspecialchars($it['url']) ?></span>
                            <?php if(!empty($it['publish_date'])): ?>
                            <span class="item-date-badge">
                                📅 <?= htmlspecialchars(date('d/m/Y', strtotime($it['publish_date']))) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="item-actions">
                            <button class="btn btn-warning btn-sm"
                                onclick='openEditModal(<?= json_encode([
                                    "id"           => $it["id"],
                                    "cat_id"       => $it["cat_id"],
                                    "title"        => $it["title"],
                                    "url"          => $it["url"],
                                    "desc"         => $it["desc"] ?? "",
                                    "publish_date" => $it["publish_date"] ?? "",
                                    "icon"         => $it["icon"] ?? ""
                                ], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                ✏️
                            </button>
                            <a href="?del_item=<?= htmlspecialchars($it['id']) ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Eliminare «<?= htmlspecialchars(addslashes($it['title'])) ?>»?')">🗑</a>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <?php if(empty($db['items'])): ?>
            <p class="text-muted text-center py-3">Nessuno strumento ancora aggiunto.</p>
        <?php endif; ?>
    </div>

</div>


<!-- ===== MODAL MODIFICA ===== -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Modifica Strumento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Area</label>
                            <select name="cat_id" id="edit-cat_id" class="form-select" required>
                                <?php foreach($db['categories'] as $c): ?>
                                    <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" id="edit-url" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data pubblicazione / implementazione</label>
                            <input type="date" name="publish_date" id="edit-publish_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrizione</label>
                            <input type="text" name="desc" id="edit-desc" class="form-control">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" name="edit_item" class="btn btn-primary">💾 Salva modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL RINOMINA AREA ===== -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Rinomina Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="rename_cat_id" id="rename-cat-id">
                    <label class="form-label">Nuovo nome area</label>
                    <input type="text" name="cat_new_name" id="rename-cat-name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" name="rename_cat" class="btn btn-primary">Salva nome</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Open edit modal */
const editModalEl = document.getElementById('editModal');
const editModal   = new bootstrap.Modal(editModalEl);
function openEditModal(data) {
    document.getElementById('edit-id').value           = data.id;
    document.getElementById('edit-cat_id').value       = data.cat_id;
    document.getElementById('edit-title').value        = data.title;
    document.getElementById('edit-url').value          = data.url;
    document.getElementById('edit-desc').value         = data.desc;
    document.getElementById('edit-publish_date').value = data.publish_date;

    editModal.show();
}

/* Rename modal */
const renameModalEl = document.getElementById('renameModal');
const renameModal   = new bootstrap.Modal(renameModalEl);
function openRenameModal(id, name) {
    document.getElementById('rename-cat-id').value  = id;
    document.getElementById('rename-cat-name').value = name;
    renameModal.show();
    setTimeout(() => document.getElementById('rename-cat-name').select(), 300);
}

/* Sortable: categories */
Sortable.create(document.getElementById('cat-sortable'), {
    animation: 200,
    handle: '.cat-handle',
    onEnd: function() {
        const order = Array.from(document.getElementById('cat-sortable').children)
                          .map(el => el.dataset.catId);
        fetch('admin.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'update_cat_order=1&'+order.map(id=>'order[]='+encodeURIComponent(id)).join('&')
        });
    }
});

/* Sortable: items (cross-category drag allowed) */
document.querySelectorAll('.items-sortable').forEach(list => {
    Sortable.create(list, {
        animation: 150,
        handle: '.item-handle',
        group: 'shared-items',
        onEnd: function() {
            // Raccogli ordine globale E cat_id corrente per ogni item
            const orderParts  = [];
            const catMapParts = [];
            document.querySelectorAll('.items-sortable').forEach(function(zone) {
                const catId = zone.dataset.cat;
                zone.querySelectorAll('.item-row').forEach(function(row) {
                    const id = row.dataset.id;
                    orderParts.push('order[]=' + encodeURIComponent(id));
                    catMapParts.push('cat_map[' + encodeURIComponent(id) + ']=' + encodeURIComponent(catId));
                });
            });
            fetch('admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'update_order=1&' + orderParts.join('&') + '&' + catMapParts.join('&')
            });
        }
    });
});
</script>
</body>
</html>
