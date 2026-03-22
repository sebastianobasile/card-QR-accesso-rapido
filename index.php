<?php include('functions.php'); $db = load_db(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($CONFIG['titolo_sito']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --blue-dark: #003a7a;
            --blue-main: #004a99;
            --blue-light: #e8f0fb;
        }

        body { background: #f0f4f8; }

        .card-custom {
            border: 2px solid var(--blue-main);
            border-radius: 12px;
            height: 100%;
            background: #fff;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .card-custom:hover {
            box-shadow: 0 6px 24px rgba(0,74,153,0.15);
            transform: translateY(-2px);
        }

        .area-title {
            border-bottom: 3px solid var(--blue-main);
            padding-bottom: 6px;
            margin-top: 36px;
            color: var(--blue-main);
            font-size: 1.05rem;
            letter-spacing: 0.08em;
        }

        /* URL in chiaro – sotto il titolo, piena larghezza, online e stampa */
        .url-display {
            font-size: 0.68rem;
            color: #2255aa;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            opacity: 0.75;
            line-height: 1.3;
        }

        /* Date badge – subtle, non invasivo */
        .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: #f0f5ff;
            color: #3366aa;
            font-size: 0.67rem;
            font-weight: 600;
            border: 1px solid #ccddf4;
            border-radius: 5px;
            padding: 1px 6px;
            margin-top: 3px;
        }
        /* "Novità" highlight: items published in last 30 days */
        .date-badge.is-new {
            background: #fff3e0;
            color: #c05a00;
            border-color: #f5c89a;
        }
        .date-badge.is-new::before {
            content: '★ ';
            font-size: 0.6rem;
        }
        /* Card programmata (solo admin): bordo tratteggiato + badge verde */
        .card-custom.is-scheduled {
            border-style: dashed;
            opacity: 0.82;
        }
        .date-badge.is-scheduled {
            background: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
        }

        .qr-img { width: 80px; height: 80px; flex-shrink: 0; }

        footer {
            background: #f8f9fa;
            padding: 20px 0;
            margin-top: 50px;
            border-top: 1px solid #ddd;
        }

        /* ===== PRINT ===== */
        @media print {
            .no-print { display: none !important; }

            /* 1. TUTTO IN NERO pieno – nessun grigio/colore tenue */
            * { color: #000 !important; background: transparent !important;
                border-color: #777 !important; opacity: 1 !important; }

            body { background: white !important; padding: 0; font-size: 10pt; }
            .container { width: 100%; max-width: 100%; margin: 0; padding: 0 .5cm; }

            .category-block { break-inside: avoid-page; page-break-inside: avoid; }
            /* Spazio fisico prima di ogni area tranne la prima: garantisce margine dopo salto pagina */
            .page-break-spacer { display: block; height: 0; }
            .category-block ~ .category-block .page-break-spacer { display: block; height: 14pt; }
            .area-title { break-after: avoid; page-break-after: avoid; margin-top: 14px;
                          border-bottom: 2px solid #000 !important; }

            .card-custom {
                box-shadow: none !important; border: 1px solid #000 !important;
                break-inside: avoid; page-break-inside: avoid;
                padding: 5px 7px !important;
            }

            /* Sempre 2 colonne */
            .row { display: flex !important; flex-wrap: wrap !important; }
            [class*="col-"] { width: 50% !important; box-sizing: border-box !important; }

            /* Bottone Vai nascosto */
            .btn { display: none !important; }

            /* QR con URL compatto sotto */
            .qr-col { display: flex !important; flex-direction: column !important;
                      align-items: center !important; gap: 2px !important; }
            .qr-img { width: 70px !important; height: 70px !important; flex-shrink: 0 !important; }
            /* URL visibile in stampa a piena larghezza sotto il titolo */
            .url-display {
                display: block !important;
                font-size: 0.6rem !important;
                word-break: break-all !important;
                font-family: 'Courier New', monospace !important;
                margin-bottom: 2px !important;
                opacity: 1 !important;
            }

            /* Testo compatto */
            .card-custom h6 { margin-bottom: 1px !important; font-size: .82rem !important; }
            .card-custom p  { margin-bottom: 1px !important; font-size: .72rem !important; }
            .date-badge { font-size: 0.57rem !important; padding: 1px 3px !important; }

            footer { display: none !important; }
            @page { size: A4; margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <header class="text-center mb-4">
        <div class="d-flex justify-content-end mb-1 no-print">
            <button onclick="window.print()" class="btn btn-dark btn-sm shadow-sm" title="Stampa / Esporta PDF">
                🖨️ Stampa
            </button>
        </div>
        <h1 class="fw-bold" style="color: var(--blue-dark);"><?= htmlspecialchars($CONFIG['istituto']) ?></h1>
        <p class="lead text-muted"><?php
            // Rende cliccabili i token URL nel sottotitolo (es. "capuanadeamicis.it/focus")
            $sub = $CONFIG['sottotitolo'] ?? '';
            echo preg_replace_callback(
                '#((?:https?://)?[\w-]+(?:\.[\w-]+)+(?:/[^\s–]*)?)#u',
                function($m) {
                    $href = preg_match('#^https?://#', $m[1]) ? $m[1] : 'https://' . $m[1];
                    return '<a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener"'
                         . ' style="color:inherit;text-decoration:underline dotted;">'
                         . htmlspecialchars($m[1]) . '</a>';
                },
                htmlspecialchars($sub)
            );
        ?></p>
    </header>

    <?php
    if (!empty($db['categories'])):
        usort($db['categories'], fn($a,$b)=>$a['order']<=>$b['order']);
        foreach ($db['categories'] as $cat):
            $items = array_values(array_filter($db['items'], fn($i)=>$i['cat_id']==$cat['id']));
            if (empty($items)) continue;
            usort($items, fn($a,$b)=>$a['order']<=>$b['order']);
            // Colonne configurabili da admin
            $cols = (int)($CONFIG['cols_desktop'] ?? 3);
            $col_map = [1=>'col-12', 2=>'col-md-6', 3=>'col-md-6 col-lg-4', 4=>'col-md-6 col-lg-3'];
            $col_class = $col_map[$cols] ?? 'col-md-6 col-lg-4';
    ?>
        <div class="category-block">
            <div class="page-break-spacer"></div>
            <h3 class="area-title fw-bold text-uppercase"><?= htmlspecialchars($cat['name']) ?></h3>
            <div class="row g-3 mt-1">
                <?php foreach ($items as $item):
                    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($item['url']);
                    $display_url = preg_replace('#^https?://#', '', $item['url']);

                    // --- Pubblicazione programmata: nascondi ai visitatori se data futura ---
                    $today = date('Y-m-d');
                    $pub = $item['publish_date'] ?? '';
                    $is_scheduled = ($pub !== '' && $pub > $today);
                    if ($is_scheduled && !is_admin()) continue; // invisibile ai visitatori

                    // Date badge logic
                    $is_new = false;
                    if($pub && !$is_scheduled) {
                        $diff = (time() - strtotime($pub)) / 86400;
                        $giorni = (int)($CONFIG['novita_giorni'] ?? 30);
                        $is_new = ($diff >= 0 && $diff <= $giorni);
                    }
                ?>
                <div class="<?= $col_class ?>">
                    <div class="card-custom p-3 shadow-sm<?= $is_scheduled ? ' is-scheduled' : '' ?>">
                        <div class="card-inner d-flex align-items-start gap-2">
                            <div class="card-text flex-grow-1" style="min-width:0;">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['title']) ?></h6>
                                <!-- URL sotto il titolo: leggibile a piena larghezza sia online che in stampa -->
                                <div class="url-display mb-1"><?= htmlspecialchars($display_url) ?></div>
                                <p class="small text-muted mb-1" style="font-size:.82rem;"><?= htmlspecialchars($item['desc']) ?></p>
                                <?php if($is_scheduled): ?>
                                <div class="mb-1">
                                    <span class="date-badge is-scheduled">
                                        🗓 Programmata · <?= htmlspecialchars(date('d/m/Y', strtotime($pub))) ?>
                                    </span>
                                </div>
                                <?php elseif($pub): ?>
                                <div class="mb-1">
                                    <span class="date-badge <?= $is_new ? 'is-new' : '' ?>">
                                        <?= $is_new ? 'Novità · ' : '' ?>📅 <?= htmlspecialchars(date('d/m/Y', strtotime($pub))) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="btn btn-sm btn-primary px-3 shadow-sm no-print mt-1" target="_blank" rel="noopener">Vai →</a>
                            </div>
                            <!-- QR pulito, senza testo sotto -->
                            <div class="qr-col">
                                <img src="<?= $qr_url ?>" class="qr-img" alt="QR <?= htmlspecialchars($item['title']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<footer class="text-center no-print">
    <div class="container">
        <p class="mb-0 text-muted"
           ondblclick="window.location.href='admin.php'"
           style="cursor:default;user-select:none;">
           <?= htmlspecialchars($CONFIG['footer_text']) ?>
        </p>
        <small><?= htmlspecialchars($CONFIG['footer_text2'] ?? 'Realizzato per '.$CONFIG['istituto']) ?></small>
        <div class="mt-2">
            <a href="https://github.com/sebastianobasile?tab=repositories"
               target="_blank" rel="noopener"
               style="color:#888;font-size:0.78rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.385-1.335-1.755-1.335-1.755-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.418-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.295 24 12c0-6.63-5.37-12-12-12z"/></svg>
                Progetto su GitHub – superscuola
            </a>
        </div>
    </div>
</footer>

</body>
</html>