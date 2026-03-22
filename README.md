# 🔗 Focus – Accesso Rapido agli Strumenti Digitali

[![Sostieni il progetto](https://img.shields.io/badge/Sostieni%20il%20progetto-PayPal-0070ba?style=for-the-badge&logo=paypal)](https://paypal.me/superscuola)

Pannello web per l'accesso rapido agli strumenti digitali di un istituto scolastico.  
Consente di raggruppare link utili in **aree tematiche**, con QR code generato automaticamente per ogni risorsa, badge di novità, pubblicazione programmata delle card e un pannello di amministrazione protetto da password.

Sviluppato per il **3° I.C. Capuana-De Amicis – Avola (SR)** e rilasciato come strumento open-source riutilizzabile da qualsiasi scuola.

---

## ✨ Funzionalità principali

- 📂 **Aree tematiche** ordinabili e rinominabili dall'admin
- 🃏 **Card strumenti** con titolo, descrizione, URL, QR code automatico e data di pubblicazione
- ⭐ **Badge "Novità"** per le card pubblicate di recente (giorni configurabili)
- 🗓 **Pubblicazione programmata**: inserisci una data futura e la card sarà invisibile fino a quel giorno
- 🖨️ **Stampa / PDF** ottimizzata (layout a 2 colonne, QR visibile, URL in chiaro)
- 🔐 **Area admin** protetta da password con doppio clic sul footer
- ⚙️ **Configurazione completa** da interfaccia: titolo, sottotitolo, colonne, giorni novità, testi footer
- 💾 Nessun database: tutto salvato in un singolo file **JSON**

---

## 📁 Struttura del progetto

```
focuscapuana/
├── index.php          # Pagina pubblica
├── admin.php          # Pannello di amministrazione
├── functions.php      # Configurazione e funzioni condivise
├── database.json      # Dati (categorie e card)
├── .htaccess          # Protezione accesso diretto ai file
└── uploads/           # Cartella upload (se abilitata)
```

---

## 🚀 Installazione

1. Carica tutti i file su un server PHP (≥ 7.4)
2. Assicurati che `functions.php` e `database.json` siano **scrivibili** dal web server (`chmod 664`)
3. Apri `index.php` nel browser
4. Accedi all'area admin con **doppio clic sul footer**, poi configura titolo, password e aggiungi le prime card

> **Nessuna dipendenza da database** – funziona su qualsiasi hosting condiviso con PHP.

---

## ⚙️ Configurazione

Tutte le impostazioni si trovano nella sezione **Impostazioni** del pannello admin (`admin.php`):

| Parametro | Descrizione |
|---|---|
| `titolo_sito` | Titolo del tab del browser |
| `istituto` | Nome istituto visualizzato nell'header |
| `sottotitolo` | Sottotitolo (gli URL vengono resi cliccabili automaticamente) |
| `password` | Password accesso admin |
| `cols_desktop` | Numero di colonne (1–4) |
| `novita_giorni` | Giorni entro cui una card è considerata "Novità" |
| `footer_text` | Testo principale del footer |
| `footer_text2` | Testo secondario del footer |

---

## 🔒 Sicurezza

Aggiungere un file `.htaccess` nella root per bloccare l'accesso diretto a `functions.php` e `database.json`:

```apache
<FilesMatch "^(functions\.php|database\.json)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

---

## 🖼️ Screenshot

*(Inserire screenshot nella cartella `screenshots/`)*

---

## 📄 Licenza

Distribuito con licenza **MIT**.  
Puoi usare, modificare e ridistribuire liberamente questo strumento, anche per altre scuole.  
Consulta il file [LICENSE](LICENSE) per i dettagli.

---

## 👨‍💻 Autore

**Sebastiano Basile** – Funzione Strumentale Area 2  
I.C. Capuana-De Amicis – Avola (SR)  
🌐 [superscuola.com](https://superscuola.com) · 🐙 [GitHub](https://github.com/sebastianobasile?tab=repositories)

---

## ☕ Sostieni il progetto

Se questo strumento ti è utile, considera una piccola donazione volontaria:

[![Sostieni il progetto](https://img.shields.io/badge/Sostieni%20il%20progetto-PayPal-0070ba?style=for-the-badge&logo=paypal)](https://paypal.me/superscuola)

Ogni contributo aiuta a mantenere e migliorare gli strumenti open-source per le scuole italiane. 🙏
