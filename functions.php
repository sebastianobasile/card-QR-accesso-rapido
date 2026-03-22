<?php
session_start();

// --- CONFIGURAZIONE DINAMICA ---
$CONFIG = [
    'password'    => "Basile_Cambiami",      // Password area admin
    'titolo_sito' => "Strumenti Digitali – A.s. 2025/26",
    'istituto'    => "I.C. Capuana-De Amicis – A.s. 2025/26",
    'footer_text' => "© 2026 - Sviluppo: Sebastiano Basile",
    'json_file'   => "database.json",
    'upload_dir'  => "uploads/",
    'cols_desktop' => "2",
    'footer_text2' => "Realizzato per il 3° I.C. Capuana-DeAmicis",
    'sottotitolo'  => "capuanadeamicis.it/focus – Accesso Rapido agli Strumenti Digitali d'Istituto",
    'novita_giorni' => "21"
];

// Inizializzazione file JSON se vuoto
if (!file_exists($CONFIG['json_file'])) {
    $initial = ['categories' => [], 'items' => []];
    file_put_contents($CONFIG['json_file'], json_encode($initial));
}

function load_db() {
    global $CONFIG;
    return json_decode(file_get_contents($CONFIG['json_file']), true);
}

function save_db($data) {
    global $CONFIG;
    file_put_contents($CONFIG['json_file'], json_encode($data, JSON_PRETTY_PRINT));
}

function is_admin() {
    return isset($_SESSION['admin_access']) && $_SESSION['admin_access'] === true;
}
?>