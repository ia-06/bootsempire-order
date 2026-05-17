<?php
/**
 * index.php — Admin panel: Login → Channel select → Orders / Links / Settings
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootsempire | Admin</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --font: 'Inter', system-ui, -apple-system, sans-serif;
      --bg: #ffffff;
      --surface: #f7f7f8;
      --surface-2: #f0f0f2;
      --border: rgba(0, 0, 0, 0.09);
      --border-hover: rgba(0, 0, 0, 0.16);
      --text: #111;
      --text-2: #444;
      --text-m: #767676;
      --text-f: #aaa;
      --accent: #111;
      --accent-t: #fff;
      --ig: #e1306c;
      --wa: #25d366;
      --success: #16a34a;
      --warn: #b45309;
      --error: #dc2626;
      --radius: 12px;
      --radius-sm: 8px;
      --radius-xs: 6px;
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
      --shadow-md: 0 4px 16px rgba(0, 0, 0, .08), 0 2px 4px rgba(0, 0, 0, .04);
      --shadow-xl: 0 20px 60px rgba(0, 0, 0, .18), 0 4px 16px rgba(0, 0, 0, .10);
      --tr: 160ms cubic-bezier(.16, 1, .3, 1);
      --max-w: 960px;
      --px: clamp(16px, 4vw, 32px);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html {
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      -webkit-text-size-adjust: none
    }

    body {
      min-height: 100dvh;
      font-family: var(--font);
      font-size: 15px;
      line-height: 1.6;
      color: var(--text);
      background: var(--bg)
    }

    button {
      cursor: pointer;
      font: inherit;
      border: none;
      background: none
    }

    input,
    select,
    textarea {
      font: inherit;
      color: inherit
    }

    img {
      display: block;
      max-width: 100%
    }

    :focus-visible {
      outline: 2px solid var(--accent);
      outline-offset: 3px;
      border-radius: 4px
    }

    /* ---- Header ---- */
    .hdr {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(255, 255, 255, .92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border)
    }

    .hdr-in {
      max-width: var(--max-w);
      margin: 0 auto;
      padding: 0 var(--px);
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px
    }

    .logo-wrap {
      display: flex;
      align-items: center;
      gap: 9px
    }

    .logo-wrap img {
      max-height: 24px;
      width: auto
    }

    .hdr-right {
      display: flex;
      align-items: center;
      gap: 10px
    }

    .channel-badge {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 4px 12px;
      border-radius: 99px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .02em
    }

    .channel-badge.ig {
      background: rgba(225, 48, 108, .1);
      color: var(--ig)
    }

    .channel-badge.wa {
      background: rgba(37, 211, 102, .1);
      color: #15803d
    }

    .btn-ghost {
      padding: 6px 12px;
      border-radius: var(--radius-xs);
      font-size: 13px;
      font-weight: 600;
      color: var(--text-m);
      border: 1px solid var(--border-hover);
      transition: all var(--tr)
    }

    .btn-ghost:hover {
      color: var(--text);
      border-color: rgba(0, 0, 0, .3)
    }

    /* ---- Screens ---- */
    .screen {
      display: none
    }

    .screen.active {
      display: block
    }

    /* ---- Login ---- */
    .login-wrap {
      min-height: calc(100dvh - 56px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px var(--px)
    }

    .login-card {
      width: 100%;
      max-width: 400px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 36px 32px;
      box-shadow: var(--shadow-md)
    }

    .login-title {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -.03em;
      margin-bottom: 6px
    }

    .login-sub {
      font-size: 14px;
      color: var(--text-m);
      margin-bottom: 28px
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 16px
    }

    .field label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-2)
    }

    .inp {
      padding: 11px 13px;
      border: 1.5px solid var(--border-hover);
      border-radius: var(--radius-sm);
      font-size: 15px;
      outline: none;
      transition: border-color var(--tr)
    }

    .inp:focus {
      border-color: rgba(0, 0, 0, .4)
    }

    .btn-primary {
      width: 100%;
      padding: 13px;
      background: var(--accent);
      color: var(--accent-t);
      border-radius: var(--radius);
      font-size: 15px;
      font-weight: 700;
      transition: all var(--tr);
      margin-top: 4px
    }

    .btn-primary:hover {
      background: #333
    }

    .btn-primary:disabled {
      background: var(--surface-2);
      color: var(--text-f);
      cursor: not-allowed
    }

    .err-msg {
      font-size: 13px;
      color: var(--error);
      padding: 10px 14px;
      background: #fef2f2;
      border-radius: 8px;
      border: 1px solid #fecaca;
      margin-top: 12px;
      display: none
    }

    /* ---- Channel selector ---- */
    .channel-wrap {
      min-height: calc(100dvh - 56px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px var(--px)
    }

    .channel-card {
      width: 100%;
      max-width: 420px
    }

    .channel-title {
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -.03em;
      margin-bottom: 8px
    }

    .channel-sub {
      font-size: 14px;
      color: var(--text-m);
      margin-bottom: 28px
    }

    .channel-btns {
      display: flex;
      flex-direction: column;
      gap: 12px
    }

    .channel-btn {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 20px;
      border-radius: var(--radius);
      border: 2px solid var(--border-hover);
      background: var(--bg);
      text-align: left;
      transition: all var(--tr)
    }

    .channel-btn:hover {
      border-color: rgba(0, 0, 0, .4);
      box-shadow: var(--shadow-sm)
    }

    .channel-btn.ig:hover {
      border-color: var(--ig)
    }

    .channel-btn.wa:hover {
      border-color: var(--wa)
    }

    .ch-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .ch-icon.ig {
      background: rgba(225, 48, 108, .1);
      color: var(--ig)
    }

    .ch-icon.wa {
      background: rgba(37, 211, 102, .1);
      color: #15803d
    }

    .ch-name {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 2px
    }

    .ch-desc {
      font-size: 13px;
      color: var(--text-m)
    }

    /* ---- Admin panel ---- */
    .panel {
      max-width: var(--max-w);
      margin: 0 auto;
      padding: 32px var(--px) 80px
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: 4px;
      margin-bottom: 32px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 0
    }

    .tab-btn {
      padding: 10px 16px;
      font-size: 14px;
      font-weight: 600;
      color: var(--text-m);
      border-radius: var(--radius-sm) var(--radius-sm) 0 0;
      border: 1px solid transparent;
      border-bottom: none;
      margin-bottom: -1px;
      transition: all var(--tr)
    }

    .tab-btn.active {
      background: var(--bg);
      color: var(--text);
      border-color: var(--border)
    }

    .tab-btn:hover:not(.active) {
      color: var(--text-2)
    }

    .tab-pane {
      display: none
    }

    .tab-pane.active {
      display: block
    }

    /* Section header */
    .sec-hd {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      gap: 12px;
      flex-wrap: wrap
    }

    .sec-title {
      font-size: 18px;
      font-weight: 800;
      letter-spacing: -.02em
    }

    .sec-sub {
      font-size: 13px;
      color: var(--text-m);
      margin-top: 2px
    }

    /* Generate link card */
    .gen-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
      margin-bottom: 0
    }

    .gen-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 16px
    }

    .gen-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: flex-start
    }

    .gen-field {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;
      min-width: 120px
    }

    .gen-field label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-m);
      letter-spacing: .04em;
      text-transform: uppercase;
      white-space: nowrap
    }

    .gen-field small {
      font-size: 11px;
      color: var(--text-f);
      margin-top: 2px
    }

    .gen-btn {
      padding: 11px 20px;
      background: var(--accent);
      color: #fff;
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 700;
      white-space: nowrap;
      transition: all var(--tr);
      flex-shrink: 0;
      align-self: flex-end
    }

    .gen-btn:hover {
      background: #333
    }

    .gen-btn:disabled {
      background: var(--surface-2);
      color: var(--text-f);
      cursor: not-allowed
    }

    .gen-calc {
      margin-top: 14px;
      padding: 12px 16px;
      background: var(--surface);
      border-radius: var(--radius-sm);
      font-size: 13px;
      color: var(--text-2);
      display: flex;
      gap: 20px;
      flex-wrap: wrap
    }

    .gen-calc span {
      font-weight: 700;
      color: var(--text)
    }

    .gen-result {
      margin-top: 16px;
      padding: 16px 18px;
      border: 1px solid #bbf7d0;
      border-radius: var(--radius-sm);
      background: #f0fdf4;
      display: none
    }

    .gen-result-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      flex-wrap: wrap;
      gap: 8px
    }

    .gen-result-head p {
      font-size: 13px;
      color: #15803d;
      font-weight: 700
    }

    .gen-order-id {
      font-size: 12px;
      font-family: monospace;
      background: #dcfce7;
      color: #166534;
      padding: 3px 10px;
      border-radius: 99px;
      font-weight: 700;
      letter-spacing: .04em
    }

    .gen-url {
      display: flex;
      align-items: center;
      gap: 8px;
      border: 1px solid #86efac;
      border-radius: 6px;
      background: #fff;
      overflow: hidden
    }

    .gen-url span {
      flex: 1;
      padding: 10px 12px;
      font-size: 14px;
      font-family: monospace;
      color: #15803d;
      word-break: break-all
    }

    .gen-url button {
      flex-shrink: 0;
      padding: 0 12px;
      height: 40px;
      background: #dcfce7;
      border-left: 1px solid #86efac;
      font-size: 12px;
      font-weight: 700;
      color: #15803d;
      transition: all var(--tr)
    }

    .gen-url button:hover {
      background: #bbf7d0
    }

    .gen-err {
      margin-top: 12px;
      padding: 10px 14px;
      border: 1px solid #fecaca;
      border-radius: var(--radius-sm);
      background: #fef2f2;
      font-size: 13px;
      color: var(--error);
      display: none
    }

    /* ---- Links history list ---- */
    .links-section-hd {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 28px 0 12px;
      gap: 12px
    }

    .links-section-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--text-2)
    }

    .links-list {
      display: flex;
      flex-direction: column;
      gap: 8px
    }

    .link-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 12px 14px;
      background: var(--bg);
      cursor: pointer;
      transition: box-shadow var(--tr), border-color var(--tr);
    }

    .link-row:hover {
      box-shadow: var(--shadow-sm);
      border-color: rgba(0, 0, 0, .18)
    }

    .link-row-url {
      flex: 1;
      font-size: 13px;
      font-family: monospace;
      color: var(--text-m);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      min-width: 0
    }

    .link-row-meta {
      font-size: 11px;
      color: var(--text-f);
      flex-shrink: 0;
      white-space: nowrap
    }

    .link-row-actions {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0
    }

    /* ================================================================
       ORDERS — Card-style row list
    ================================================================ */
    .orders-list {
      display: flex;
      flex-direction: column;
      gap: 10px
    }

    .order-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--bg);
      padding: 16px 18px;
      transition: box-shadow var(--tr), border-color var(--tr);
      cursor: pointer;
    }

    .order-card:hover {
      box-shadow: var(--shadow-sm);
      border-color: rgba(0, 0, 0, .18)
    }

    .order-top {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 12px
    }

    .order-oid {
      font-family: monospace;
      font-size: 12px;
      font-weight: 700;
      color: var(--text);
      background: var(--surface);
      padding: 3px 9px;
      border-radius: 6px;
      letter-spacing: .04em;
      flex-shrink: 0
    }

    .order-link-chip {
      font-family: monospace;
      font-size: 11px;
      font-weight: 600;
      color: var(--text-m);
      background: var(--surface-2);
      border: 1px solid var(--border-hover);
      padding: 2px 8px;
      border-radius: 6px;
      letter-spacing: .03em;
      flex-shrink: 0
    }

    .order-date {
      font-size: 11px;
      color: var(--text-f);
      margin-left: auto;
      white-space: nowrap
    }

    .order-fields {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 10px 16px;
      margin-bottom: 14px
    }

    .order-field {
      display: flex;
      flex-direction: column;
      gap: 2px
    }

    .order-field-lbl {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--text-f)
    }

    .order-field-val {
      font-size: 13px;
      font-weight: 500;
      color: var(--text)
    }

    .order-field-val.mono {
      font-family: monospace;
      font-size: 12px
    }

    .order-field-val.muted {
      color: var(--text-m)
    }

    .order-foot {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      padding-top: 12px;
      border-top: 1px solid var(--border)
    }

    .order-proof {
      width: 36px;
      height: 36px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid var(--border);
      cursor: pointer;
      flex-shrink: 0;
      transition: opacity var(--tr)
    }

    .order-proof:hover {
      opacity: .75
    }

    /* Status badges */
    .badge {
      display: inline-flex;
      align-items: center;
      padding: 3px 9px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .03em;
      white-space: nowrap
    }

    .badge-not-filled {
      background: #f3f4f6;
      color: #6b7280
    }

    .badge-pending {
      background: #fef3c7;
      color: #92400e
    }

    .badge-confirmed {
      background: #dcfce7;
      color: #166534
    }

    .badge-issue {
      background: #fee2e2;
      color: #991b1b
    }

    .badge-orders {
      background: #eff6ff;
      color: #1d4ed8
    }

    /* Action buttons */
    .order-actions {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-left: auto;
      flex-wrap: wrap
    }

    .act-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 5px 13px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .02em;
      border: 1.5px solid;
      white-space: nowrap;
      transition: all var(--tr);
      flex-shrink: 0;
      line-height: 1
    }

    .act-confirm {
      border-color: rgba(22, 163, 74, .35);
      color: #15803d;
      background: transparent
    }

    .act-confirm:hover {
      background: #f0fdf4;
      border-color: #16a34a
    }

    .act-issue {
      border-color: rgba(180, 83, 9, .3);
      color: var(--warn);
      background: transparent
    }

    .act-issue:hover {
      background: #fffbeb;
      border-color: #b45309
    }

    .act-copy {
      border-color: rgba(0, 0, 0, .14);
      color: var(--text-m);
      background: transparent
    }

    .act-copy:hover {
      background: var(--surface);
      border-color: rgba(0, 0, 0, .28);
      color: var(--text)
    }

    .act-del {
      width: 30px;
      height: 30px;
      min-width: 30px;
      padding: 0;
      border-radius: 50%;
      border: 1.5px solid rgba(0, 0, 0, .13);
      color: var(--text-f);
      background: transparent;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all var(--tr)
    }

    .act-del:hover {
      border-color: rgba(220, 38, 38, .45);
      color: var(--error);
      background: #fef2f2
    }

    /* Toast */
    .toast {
      position: fixed;
      bottom: 24px;
      left: 50%;
      transform: translateX(-50%) translateY(12px);
      background: #111;
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      padding: 9px 18px;
      border-radius: 99px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .22);
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s, transform .2s;
      z-index: 9999;
      white-space: nowrap
    }

    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(0)
    }

    /* Settings */
    .settings-grid {
      display: flex;
      flex-direction: column;
      gap: 20px;
      max-width: 560px
    }

    .settings-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px
    }

    .settings-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 16px
    }

    .fields {
      display: flex;
      flex-direction: column;
      gap: 14px
    }

    .field-row {
      display: flex;
      flex-direction: column;
      gap: 5px
    }

    .field-row label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-2)
    }

    .field-row small {
      font-size: 12px;
      color: var(--text-m)
    }

    .save-btn {
      padding: 10px 24px;
      background: var(--accent);
      color: #fff;
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 700;
      transition: all var(--tr);
      margin-top: 8px
    }

    .save-btn:hover {
      background: #333
    }

    .save-ok {
      font-size: 13px;
      color: var(--success);
      display: none;
      margin-top: 8px
    }

    /* QR dropzone */
    .qr-dropzone {
      position: relative;
      overflow: hidden;
      border: 2px dashed var(--border-hover);
      border-radius: var(--radius-sm);
      padding: 20px 16px;
      text-align: center;
      cursor: pointer;
      transition: all var(--tr);
      background: var(--surface);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      min-height: 160px;
    }

    .qr-dropzone:hover,
    .qr-dropzone.drag-over {
      border-color: rgba(0, 0, 0, .35);
      background: var(--surface-2);
    }

    .qr-dropzone svg {
      color: var(--text-f);
    }

    .qr-dropzone p {
      font-size: 13px;
      color: var(--text-m);
    }

    .qr-dropzone span {
      font-size: 12px;
      color: var(--text-f);
    }

    .qr-preview-img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      background: #fff;
      z-index: 1;
    }

    .qr-overlay {
      position: absolute;
      inset: 0;
      background: rgba(220, 38, 38, 0.85);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
      opacity: 0;
      transition: opacity var(--tr);
      z-index: 2;
      backdrop-filter: blur(2px);
    }

    .qr-dropzone:hover .qr-overlay {
      opacity: 1;
    }

    /* Lightbox */
    .lightbox {
      position: fixed;
      inset: 0;
      z-index: 999;
      background: rgba(0, 0, 0, .85);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px
    }

    .lightbox img {
      max-width: 100%;
      max-height: 90dvh;
      border-radius: 12px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, .6)
    }

    .lightbox-close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .15);
      color: #fff;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .lightbox-close:hover {
      background: rgba(255, 255, 255, .25)
    }

    /* Empty / loading */
    .empty {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-m)
    }

    .empty p {
      font-size: 14px;
      margin-top: 8px
    }

    .loading-state {
      text-align: center;
      padding: 40px;
      color: var(--text-f);
      font-size: 14px
    }

    /* ================================================================
       ORDER DETAIL OVERLAY
    ================================================================ */
    .od-backdrop {
      position: fixed;
      inset: 0;
      z-index: 200;
      background: rgba(0, 0, 0, .55);
      opacity: 0;
      pointer-events: none;
      transition: opacity 220ms ease;
      backdrop-filter: blur(2px);
    }

    .od-backdrop.open {
      opacity: 1;
      pointer-events: all
    }

    .od-drawer {
      position: fixed;
      inset: 0;
      z-index: 201;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      pointer-events: none;
    }

    @media(min-width:600px) {
      .od-drawer {
        align-items: center
      }
    }

    .od-panel {
      width: 100%;
      max-width: 560px;
      max-height: 92dvh;
      background: var(--bg);
      border-radius: 20px 20px 0 0;
      box-shadow: var(--shadow-xl);
      display: flex;
      flex-direction: column;
      transform: translateY(32px);
      opacity: 0;
      transition: transform 280ms cubic-bezier(.16, 1, .3, 1), opacity 220ms ease;
      pointer-events: none;
      overflow: hidden;
    }

    @media(min-width:600px) {
      .od-panel {
        border-radius: 20px;
        transform: translateY(16px) scale(.97)
      }
    }

    .od-backdrop.open~.od-drawer .od-panel {
      transform: translateY(0);
      opacity: 1;
      pointer-events: all;
    }

    @media(min-width:600px) {
      .od-backdrop.open~.od-drawer .od-panel {
        transform: translateY(0) scale(1)
      }
    }

    /* Drag handle */
    .od-handle {
      flex-shrink: 0;
      display: flex;
      justify-content: center;
      padding: 12px 0 4px
    }

    .od-handle-bar {
      width: 36px;
      height: 4px;
      border-radius: 99px;
      background: var(--border-hover)
    }

    @media(min-width:600px) {
      .od-handle {
        display: none
      }
    }

    /* Header */
    .od-header {
      flex-shrink: 0;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 16px 20px 14px;
      border-bottom: 1px solid var(--border);
    }

    .od-header-left {
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .od-oid {
      font-family: monospace;
      font-size: 13px;
      font-weight: 700;
      color: var(--text);
      background: var(--surface);
      padding: 3px 10px;
      border-radius: 6px;
      letter-spacing: .04em;
      display: inline-block
    }

    .od-meta {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap
    }

    .od-date {
      font-size: 12px;
      color: var(--text-m)
    }

    .od-channel {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: 99px
    }

    .od-channel.ig {
      background: rgba(225, 48, 108, .1);
      color: var(--ig)
    }

    .od-channel.wa {
      background: rgba(37, 211, 102, .1);
      color: #15803d
    }

    .od-close {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--border-hover);
      color: var(--text-m);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all var(--tr)
    }

    .od-close:hover {
      background: var(--surface-2);
      color: var(--text);
      border-color: rgba(0, 0, 0, .3)
    }

    /* Scrollable body */
    .od-body {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px
    }

    .od-body::-webkit-scrollbar {
      width: 4px
    }

    .od-body::-webkit-scrollbar-track {
      background: transparent
    }

    .od-body::-webkit-scrollbar-thumb {
      background: var(--border-hover);
      border-radius: 99px
    }

    /* Section label */
    .od-sec-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--text-m);
      margin-bottom: 10px
    }

    /* Boot card */
    .od-boot-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      margin-bottom: 10px
    }

    .od-boot-card:last-child {
      margin-bottom: 0
    }

    .od-boot-hd {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      background: var(--surface);
      border-bottom: 1px solid var(--border)
    }

    .od-boot-num {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--text);
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0
    }

    .od-boot-label {
      font-size: 13px;
      font-weight: 700;
      color: var(--text)
    }

    .od-boot-body {
      padding: 14px;
      display: flex;
      gap: 14px;
      align-items: flex-start
    }

    .od-boot-img-wrap {
      width: 80px;
      height: 80px;
      flex-shrink: 0;
      border-radius: var(--radius-sm);
      overflow: hidden;
      border: 1px solid var(--border);
      background: var(--surface);
      cursor: pointer;
      transition: opacity var(--tr)
    }

    .od-boot-img-wrap:hover {
      opacity: .8
    }

    .od-boot-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .od-boot-img-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-f)
    }

    .od-boot-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 8px
    }

    .od-kv {
      display: flex;
      flex-direction: column;
      gap: 1px
    }

    .od-kv-label {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--text-f)
    }

    .od-kv-val {
      font-size: 13px;
      font-weight: 600;
      color: var(--text)
    }

    .od-kv-val.muted {
      color: var(--text-m);
      font-weight: 400
    }

    /* Info card (payment, customer) */
    .od-info-card {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 14px
    }

    .od-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px
    }

    /* Footer actions */
    .od-footer {
      flex-shrink: 0;
      padding: 14px 20px;
      border-top: 1px solid var(--border);
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .od-action-confirm {
      flex: 1;
      padding: 10px 16px;
      background: #f0fdf4;
      color: #15803d;
      border: 1.5px solid rgba(22, 163, 74, .35);
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 700;
      transition: all var(--tr)
    }

    .od-action-confirm:hover {
      background: #dcfce7;
      border-color: #16a34a
    }

    .od-action-issue {
      flex: 1;
      padding: 10px 16px;
      background: #fffbeb;
      color: var(--warn);
      border: 1.5px solid rgba(180, 83, 9, .3);
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 700;
      transition: all var(--tr)
    }

    .od-action-issue:hover {
      background: #fef3c7;
      border-color: #b45309
    }

    .od-footer-placeholder {
      font-size: 13px;
      color: var(--text-f);
      padding: 10px 0
    }

    /* Link detail — URL copy row inside od-panel */
    .od-url-row {
      display: flex;
      align-items: center;
      gap: 8px;
      border: 1px solid var(--border-hover);
      border-radius: var(--radius-sm);
      background: var(--surface);
      overflow: hidden
    }

    .od-url-row span {
      flex: 1;
      padding: 10px 12px;
      font-size: 13px;
      font-family: monospace;
      color: var(--text-m);
      word-break: break-all
    }

    .od-url-copy {
      flex-shrink: 0;
      padding: 0 12px;
      height: 38px;
      background: var(--surface-2);
      border-left: 1px solid var(--border-hover);
      font-size: 12px;
      font-weight: 700;
      color: var(--text-m);
      transition: all var(--tr)
    }

    .od-url-copy:hover {
      background: var(--border);
      color: var(--text)
    }
  </style>
</head>

<body>

  <!-- TOAST -->
  <div class="toast" id="toast"></div>

  <!-- HEADER -->
  <header class="hdr">
    <div class="hdr-in">
      <div class="logo-wrap"><img src="/logo.svg" alt="Bootsempire"></div>
      <div class="hdr-right" id="hdrRight" style="display:none">
        <span class="channel-badge" id="channelBadge"></span>
        <button class="btn-ghost" id="switchBtn" onclick="switchChannel()" style="display:none">Switch</button>
        <button class="btn-ghost" onclick="doLogout()">Sign out</button>
      </div>
    </div>
  </header>

  <!-- LIGHTBOX -->
  <div class="lightbox" id="lightbox" style="display:none" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">&#x2715;</button>
    <img id="lightboxImg" src="" alt="">
  </div>

  <!-- ORDER DETAIL OVERLAY -->
  <div class="od-backdrop" id="odBackdrop" onclick="closeOrderDetail()"></div>
  <div class="od-drawer" id="odDrawer">
    <div class="od-panel" id="odPanel">
      <div class="od-handle">
        <div class="od-handle-bar"></div>
      </div>
      <div class="od-header">
        <div class="od-header-left">
          <span class="od-oid" id="odOid"></span>
          <div class="od-meta">
            <span id="odBadge"></span>
            <span class="od-date" id="odDate"></span>
            <span class="od-channel" id="odChannel"></span>
          </div>
        </div>
        <button class="od-close" onclick="closeOrderDetail()" aria-label="Close">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
      <div class="od-body" id="odBody"></div>
      <div class="od-footer" id="odFooter"></div>
    </div>
  </div>

  <!-- SCREEN: LOGIN -->
  <div class="screen active" id="screenLogin">
    <div class="login-wrap">
      <div class="login-card">
        <h1 class="login-title">Admin login</h1>
        <p class="login-sub">Sign in to manage Bootsempire orders.</p>
        <div class="field">
          <label for="loginId">Admin ID</label>
          <input class="inp" id="loginId" type="text" autocomplete="username" placeholder="Your admin ID">
        </div>
        <div class="field">
          <label for="loginPass">Password</label>
          <input class="inp" id="loginPass" type="password" autocomplete="current-password" placeholder="Your password">
        </div>
        <button class="btn-primary" id="loginBtn" onclick="doLogin()">Sign in</button>
        <p class="err-msg" id="loginErr"></p>
      </div>
    </div>
  </div>

  <!-- SCREEN: CHANNEL SELECT -->
  <div class="screen" id="screenChannel">
    <div class="channel-wrap">
      <div class="channel-card">
        <h2 class="channel-title">Select channel</h2>
        <p class="channel-sub">Choose which order channel you're managing right now.</p>
        <div class="channel-btns">
          <button class="channel-btn ig" onclick="selectChannel('instagram')">
            <div class="ch-icon ig">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
              </svg>
            </div>
            <div>
              <p class="ch-name">Instagram</p>
              <p class="ch-desc">Orders from Instagram DMs</p>
            </div>
          </button>
          <button class="channel-btn wa" onclick="selectChannel('whatsapp')">
            <div class="ch-icon wa">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                <path
                  d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.172.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
              </svg>
            </div>
            <div>
              <p class="ch-name">WhatsApp</p>
              <p class="ch-desc">Orders from WhatsApp messages</p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- SCREEN: ADMIN PANEL -->
  <div class="screen" id="screenPanel">
    <div class="panel">
      <div class="tabs">
        <button class="tab-btn active" id="tabOrders" onclick="showTab('orders')">Orders</button>
        <button class="tab-btn" id="tabLinks" onclick="showTab('links')">Links</button>
        <button class="tab-btn" id="tabSettings" onclick="showTab('settings')">Settings</button>
      </div>

      <!-- ORDERS TAB -->
      <div class="tab-pane active" id="paneOrders">
        <div class="sec-hd">
          <div>
            <p class="sec-title">Orders</p>
            <p class="sec-sub" id="ordersSubtitle"></p>
          </div>
          <button class="btn-ghost" onclick="loadOrders()">&#8635; Refresh</button>
        </div>
        <div id="ordersList" class="orders-list">
          <div class="loading-state">Loading orders&#8230;</div>
        </div>
      </div>

      <!-- GENERATE LINKS TAB -->
      <div class="tab-pane" id="paneLinks">
        <div class="sec-hd">
          <div>
            <p class="sec-title">Generate Link</p>
            <p class="sec-sub">Create a new unique order link. Each link can receive multiple orders.</p>
          </div>
        </div>
        <div class="gen-card">
          <p class="gen-title">New link</p>
          <div class="gen-row">
            <div class="gen-field">
              <label>Qty (boots)</label>
              <input class="inp" id="genQty" type="number" value="1" min="1" max="20" style="width:100%"
                oninput="updateGenCalc()">
            </div>
            <div class="gen-field">
              <label>Total Price / boot (&#8377;)</label>
              <input class="inp" id="genTotal" type="number" value="6700" min="0" style="width:100%"
                oninput="updateGenCalc()">
              <small>per unit</small>
            </div>
            <div class="gen-field">
              <label>Advance / boot (&#8377;)</label>
              <input class="inp" id="genAdvance" type="number" value="2700" min="0" style="width:100%"
                oninput="updateGenCalc()">
              <small>per unit</small>
            </div>
            <div class="gen-field">
              <label>Add-ons Price (&#8377;)</label>
              <input class="inp" id="genAddons" type="number" value="0" min="0" style="width:100%"
                oninput="updateGenCalc()">
              <small>whole order</small>
            </div>
            <button class="gen-btn" id="genBtn" onclick="generateLink()">Generate Link</button>
          </div>
          <div class="gen-calc" id="genCalc">
            1 boot &mdash; Total: <span id="calcTotal">&#8377;6,700</span> &nbsp;|&nbsp; Advance: <span
              id="calcAdv">&#8377;2,700</span> &nbsp;|&nbsp; On delivery: <span id="calcDel">&#8377;4,000</span>
            &nbsp;|&nbsp; Grand total (full pay): <span id="calcGrand">&#8377;6,700</span>
          </div>
          <div class="gen-result" id="genResult">
            <div class="gen-result-head">
              <p>&#10003; Link generated!</p>
              <span class="gen-order-id" id="genOrderId"></span>
            </div>
            <div class="gen-url">
              <span id="genUrl"></span>
              <button onclick="copyGenUrl()">Copy</button>
            </div>
          </div>
          <div class="gen-err" id="genErr"></div>
        </div>

        <!-- ---- Links history ---- -->
        <div class="links-section-hd">
          <span class="links-section-title">Generated Links</span>
          <button class="btn-ghost" onclick="loadLinks()">&#8635; Refresh</button>
        </div>
        <div id="linksList" class="links-list">
          <div class="loading-state">Loading&#8230;</div>
        </div>
      </div>

      <!-- SETTINGS TAB -->
      <div class="tab-pane" id="paneSettings">
        <div class="sec-hd">
          <div>
            <p class="sec-title">Settings</p>
          </div>
        </div>
        <form class="settings-grid" id="settingsForm" onsubmit="saveSettings(event)">
          <div class="settings-card">
            <p class="settings-title">Payment</p>
            <div class="fields">
              <div class="field-row">
                <label>UPI ID</label>
                <input class="inp" id="sUpiId" type="text" placeholder="yourname@upi">
              </div>
              <div class="field-row">
                <label>QR Code Image</label>
                <div class="qr-dropzone" id="qrDropzone" role="button" tabindex="0" onclick="handleDropzoneClick(event)"
                  onkeydown="if(event.key==='Enter'||event.key===' ')handleDropzoneClick(event)"
                  ondragover="event.preventDefault();this.classList.add('drag-over')"
                  ondragleave="this.classList.remove('drag-over')"
                  ondrop="event.preventDefault();this.classList.remove('drag-over');handleQrDrop(event)">

                  <div id="qrEmptyState" style="display:flex;flex-direction:column;align-items:center;gap:6px">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                      <polyline points="17 8 12 3 7 8" />
                      <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    <p>Drag &amp; drop QR image here</p>
                    <span>or click to browse &mdash; PNG, JPG, WEBP</span>
                  </div>

                  <img id="qrPrev" class="qr-preview-img" src="" alt="QR Preview" style="display:none">
                  <div id="qrOverlay" class="qr-overlay" style="display:none" onclick="removeQr(event)">Remove QR Code
                  </div>
                </div>
                <input type="file" id="sQrFile" accept="image/*" style="display:none"
                  onchange="handleQrFile(this.files[0])">
              </div>
              <div class="field-row">
                <label>Default Total Price (&#8377;)</label>
                <input class="inp" id="sTotalPrice" type="number" min="0">
              </div>
              <div class="field-row">
                <label>Default Advance (&#8377;)</label>
                <input class="inp" id="sAdvance" type="number" min="0">
              </div>
              <div class="field-row">
                <label>Custom Letters Add-on Price (&#8377;)</label>
                <input class="inp" id="sAddons" type="number" min="0">
              </div>
            </div>
          </div>
          <div class="settings-card">
            <p class="settings-title">Customer Redirect Links</p>
            <div class="fields">
              <div class="field-row" id="waLinkRow">
                <label>WhatsApp Link</label>
                <input class="inp" id="sWaLink" type="url" placeholder="https://wa.me/91XXXXXXXXXX">
                <small>Customers who came via WhatsApp are sent here after confirming order.</small>
              </div>
              <div class="field-row" id="igLinkRow">
                <label>Instagram Link</label>
                <input class="inp" id="sIgLink" type="url" placeholder="https://instagram.com/channel_name">
                <small>Customers who came via Instagram are sent here after confirming order.</small>
              </div>
            </div>
          </div>
          <div>
            <button type="submit" class="save-btn">Save settings</button>
            <p class="save-ok" id="saveOk">&#10003; Saved!</p>
          </div>
        </form>
      </div>

    </div>
  </div>

  <script>
    var HOST = location.origin;
    var channel = null;
    var removeQrFlag = false;

    // ---- Toast ----
    function showToast(msg) {
      var t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      clearTimeout(t._tid);
      t._tid = setTimeout(function () { t.classList.remove('show'); }, 2200);
    }

    // ---- Startup: restore session ----
    fetch('api/session.php')
      .then(function (r) { return r.json(); })
      .then(function (s) {
        if (!s.authed) return;
        document.getElementById('hdrRight').style.display = 'flex';
        if (s.channel) {
          channel = s.channel;
          applyChannelUI(channel);
          loadOrders();
          loadSettings();
        } else {
          show('screenChannel');
        }
      })
      .catch(function () { });

    function show(id) {
      document.querySelectorAll('.screen').forEach(function (s) { s.classList.remove('active'); });
      document.getElementById(id).classList.add('active');
    }

    function applyChannelUI(ch) {
      var badge = document.getElementById('channelBadge');
      badge.textContent = ch.charAt(0).toUpperCase() + ch.slice(1);
      badge.className = 'channel-badge ' + (ch === 'instagram' ? 'ig' : 'wa');
      document.getElementById('switchBtn').style.display = 'inline-flex';
      document.getElementById('ordersSubtitle').textContent = ch.charAt(0).toUpperCase() + ch.slice(1) + ' orders';

      // Isolate Redirect Settings based on channel
      var waRow = document.getElementById('waLinkRow');
      var igRow = document.getElementById('igLinkRow');
      if (waRow) waRow.style.display = (ch === 'whatsapp') ? 'flex' : 'none';
      if (igRow) igRow.style.display = (ch === 'instagram') ? 'flex' : 'none';

      show('screenPanel');
    }

    function resetPanelState() {
      document.getElementById('genResult').style.display = 'none';
      document.getElementById('genErr').style.display = 'none';
      document.getElementById('ordersList').innerHTML = '<div class="loading-state">Loading\u2026</div>';
      document.getElementById('linksList').innerHTML = '<div class="loading-state">Loading\u2026</div>';
      showTab('orders');
    }

    document.getElementById('loginPass').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') doLogin();
    });

    function doLogin() {
      var id = document.getElementById('loginId').value.trim();
      var pw = document.getElementById('loginPass').value;
      var err = document.getElementById('loginErr');
      err.style.display = 'none';
      var btn = document.getElementById('loginBtn');
      btn.disabled = true; btn.textContent = 'Signing in\u2026';
      var fd = new FormData();
      fd.append('id', id); fd.append('password', pw);
      fetch('api/login.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) {
            document.getElementById('hdrRight').style.display = 'flex';
            show('screenChannel');
          } else {
            err.textContent = d.error || 'Login failed.';
            err.style.display = 'block';
          }
        })
        .catch(function () { err.textContent = 'Network error.'; err.style.display = 'block'; })
        .finally(function () { btn.disabled = false; btn.textContent = 'Sign in'; });
    }

    function selectChannel(ch) {
      channel = ch;
      fetch('api/channel.php', {
        method: 'POST',
        body: JSON.stringify({ channel: ch }),
        headers: { 'Content-Type': 'application/json' }
      })
        .finally(function () {
          resetPanelState();
          applyChannelUI(ch);
          loadOrders();
          loadSettings();
        });
    }

    function switchChannel() {
      channel = null;
      resetPanelState();
      document.getElementById('switchBtn').style.display = 'none';
      show('screenChannel');
    }

    function doLogout() {
      fetch('api/logout.php', { method: 'POST' }).finally(function () {
        channel = null;
        document.getElementById('hdrRight').style.display = 'none';
        document.getElementById('switchBtn').style.display = 'none';
        document.getElementById('channelBadge').className = 'channel-badge';
        document.getElementById('channelBadge').textContent = '';
        document.getElementById('loginId').value = '';
        document.getElementById('loginPass').value = '';
        resetPanelState();
        show('screenLogin');
      });
    }

    function showTab(name) {
      ['orders', 'links', 'settings'].forEach(function (t) {
        document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1)).classList.toggle('active', t === name);
        document.getElementById('pane' + t.charAt(0).toUpperCase() + t.slice(1)).classList.toggle('active', t === name);
      });
      if (name === 'links') {
        loadLinks();
      }
      if (name !== 'links') {
        document.getElementById('genResult').style.display = 'none';
        document.getElementById('genErr').style.display = 'none';
      }
    }

    // ---- ORDERS ----
    function loadOrders() {
      document.getElementById('ordersList').innerHTML = '<div class="loading-state">Loading\u2026</div>';
      fetch('api/orders.php')
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var el = document.getElementById('ordersList');
          var rows = Array.isArray(data) ? data : (data.orders || []);
          if (!rows.length) {
            el.innerHTML = '<div class="empty"><p>No orders yet.</p></div>';
            return;
          }
          el.innerHTML = rows.map(renderOrderCard).join('');
        })
        .catch(function () {
          document.getElementById('ordersList').innerHTML = '<div class="empty"><p>Failed to load orders.</p></div>';
        });
    }

    // ---- LINKS ----
    function loadLinks() {
      var el = document.getElementById('linksList');
      el.innerHTML = '<div class="loading-state">Loading\u2026</div>';
      fetch('api/orders.php')
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var links = Array.isArray(data) ? data : (data.links || []);
          if (!links.length) {
            el.innerHTML = '<div class="empty"><p>No links generated yet.</p></div>';
            return;
          }
          el.innerHTML = links.map(renderLinkRow).join('');
        })
        .catch(function () {
          el.innerHTML = '<div class="empty"><p>Failed to load links.</p></div>';
        });
    }

    function renderLinkRow(l) {
      var url = HOST + '/' + l.slug;
      var date = l.link_created_at ? l.link_created_at.slice(0, 10) : '';
      var qty = parseInt(l.qty) || 1;
      var qtyLabel = qty + ' boot' + (qty > 1 ? 's' : '');
      var orderCount = parseInt(l.order_count) || 0;
      var ordersBadge = '<span class="badge badge-orders">' + orderCount + ' order' + (orderCount !== 1 ? 's' : '') + '</span>';
      var addons = parseInt(l.addons_price) || 0;
      var addonsLabel = addons > 0 ? ' &middot; +&#8377;' + addons.toLocaleString('en-IN') + ' add-ons' : '';
      var encoded = esc(JSON.stringify(l));

      return '<div class="link-row js-link-row" data-link="' + encoded + '">' +
        ordersBadge +
        '<span class="link-row-url" title="' + esc(url) + '">' + esc(url) + '</span>' +
        '<span class="link-row-meta">' + esc(qtyLabel) + addonsLabel + ' &middot; ' + esc(date) + '</span>' +
        '<div class="link-row-actions">' +
        '<button class="act-btn act-copy js-copy-url" data-url="' + esc(url) + '">&#128279; Copy</button>' +
        '<button class="act-del js-delete-link" data-slug="' + esc(l.slug) + '" data-count="' + orderCount + '" title="Delete" aria-label="Delete">' +
        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
        '</button>' +
        '</div>' +
        '</div>';
    }

    // Delegated click handler for links list
    document.getElementById('linksList').addEventListener('click', function (e) {
      var copyBtn = e.target.closest('.js-copy-url');
      if (copyBtn) { e.stopPropagation(); copyText(copyBtn.dataset.url); showToast('\u2713 Copied!'); return; }
      var delBtn = e.target.closest('.js-delete-link');
      if (delBtn) {
        e.stopPropagation();
        var count = parseInt(delBtn.dataset.count) || 0;
        deleteLink(delBtn.dataset.slug, count);
        return;
      }
      var row = e.target.closest('.js-link-row');
      if (row) {
        try { openLinkDetail(JSON.parse(row.dataset.link)); } catch (err) { }
      }
    });

    function deleteLink(slug, count) {
      if (count > 0) {
        if (!confirm('WARNING: This link has ' + count + ' active order(s).\nAre you sure you want to delete this link AND all its orders?')) return;
        if (!confirm('Are you ABSOLUTELY sure? This action cannot be undone.')) return;
      } else {
        if (!confirm('Delete this link? This cannot be undone.')) return;
      }
      fetch('api/orders.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ slug: slug }) })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.ok === false) { showToast('Error: ' + (d.error || 'Could not delete.')); }
          else { showToast('Deleted.'); loadLinks(); loadOrders(); }
        })
        .catch(function () { showToast('Network error.'); });
    }

    function statusBadge(s) {
      var cfg = {
        NOT_FILLED: { cls: 'badge-not-filled', label: 'Not Filled' },
        PENDING_ACTION: { cls: 'badge-pending', label: 'Pending Action' },
        CONFIRMED: { cls: 'badge-confirmed', label: 'Confirmed' },
        ISSUE_RAISED: { cls: 'badge-issue', label: 'Issue Raised' }
      };
      var c = cfg[s] || { cls: 'badge-not-filled', label: s };
      return '<span class="badge ' + c.cls + '">' + c.label + '</span>';
    }

    function renderOrderCard(o) {
      var date = o.submitted_at ? o.submitted_at.slice(0, 10) : (o.link_created_at ? o.link_created_at.slice(0, 10) : '?');

      var linkChip = o.slug ? '<span class="order-link-chip" title="Generated link">' + esc(location.origin + '/' + o.slug) + '</span>' : '';

      var top = '<div class="order-top">' + '<span class="order-oid">' + esc(o.order_id) + '</span>' + linkChip + statusBadge(o.display_status) + '<span class="order-date">' + date + '</span>' + '</div>';

      var nameVal = esc(o.name || '\u2014');
      var waVal = o.whatsapp ? '<span class="order-field-val mono">' + esc(o.whatsapp) + '</span>' : '<span class="order-field-val muted">\u2014</span>';
      var modeVal = o.pay_mode === 'full' ? 'Full' : 'Advance';
      var amtVal = o.paid_amount ? '\u20B9' + Number(o.paid_amount).toLocaleString('en-IN') : '\u2014';

      var fields =
        '<div class="order-fields">' +
        fld('Name', '<span class="order-field-val">' + nameVal + '</span>') +
        fld('WhatsApp', waVal) +
        fld('Mode', '<span class="order-field-val">' + modeVal + '</span>') +
        fld('Amount', '<span class="order-field-val" style="font-variant-numeric:tabular-nums">' + amtVal + '</span>') +
        '</div>';

      var canAct = o.display_status === 'PENDING_ACTION';
      var confirmBtn = canAct ? '<button class="act-btn act-confirm js-confirm" data-id="' + esc(o.order_row_id) + '">Confirm Order</button>' : '';
      var issueBtn = canAct ? '<button class="act-btn act-issue js-issue" data-id="' + esc(o.order_row_id) + '">Raise Issue</button>' : '';

      // FIX: Deletion triggers off order_row_id now, not slug!
      var delBtn = '<button class="act-del js-delete-order" data-id="' + esc(o.order_row_id) + '" title="Delete order" aria-label="Delete"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg></button>';

      var foot = '<div class="order-foot"><div class="order-actions">' + confirmBtn + issueBtn + delBtn + '</div></div>';
      var encoded = esc(JSON.stringify(o));
      return '<div class="order-card js-order-card" data-order="' + encoded + '">' + top + fields + foot + '</div>';
    }

    // Delegated click handler for all order card interactions
    document.getElementById('ordersList').addEventListener('click', function (e) {
      var btn = e.target.closest('button');

      if (btn) {
        e.stopPropagation();
        if (btn.classList.contains('js-confirm')) confirmOrder(btn.dataset.id);
        else if (btn.classList.contains('js-issue')) raiseIssue(btn.dataset.id);
        else if (btn.classList.contains('js-delete-order')) deleteOrder(btn.dataset.id);
        return;
      }

      var card = e.target.closest('.js-order-card');
      if (card) {
        try {
          var o = JSON.parse(card.dataset.order);
          openOrderDetail(o);
        } catch (err) { }
      }
    });

    function fld(label, valHtml) {
      return '<div class="order-field"><span class="order-field-lbl">' + label + '</span>' + valHtml + '</div>';
    }

    // ---- Generate Link ----
    function updateGenCalc() {
      var qty = Math.max(1, parseInt(document.getElementById('genQty').value) || 1);
      var total = Math.max(0, parseInt(document.getElementById('genTotal').value) || 0);
      var adv = Math.max(0, parseInt(document.getElementById('genAdvance').value) || 0);
      var addons = Math.max(0, parseInt(document.getElementById('genAddons').value) || 0);
      var del = Math.max(0, total - adv);
      var grand = total * qty + addons;  // grand total when paying in full
      var fmt = function (n) { return '\u20B9' + n.toLocaleString('en-IN'); };
      document.getElementById('calcTotal').textContent = fmt(total * qty);
      document.getElementById('calcAdv').textContent = fmt(adv * qty);
      document.getElementById('calcDel').textContent = fmt(del * qty);
      document.getElementById('calcGrand').textContent = fmt(grand);
      document.getElementById('genCalc').firstChild.textContent =
        qty + ' boot' + (qty > 1 ? 's' : '') + ' \u2014 Total: ';
    }

    function generateLink() {
      var qty = Math.max(1, parseInt(document.getElementById('genQty').value) || 1);
      var total = Math.max(0, parseInt(document.getElementById('genTotal').value) || 0);
      var adv = Math.max(0, parseInt(document.getElementById('genAdvance').value) || 0);
      var addons = Math.max(0, parseInt(document.getElementById('genAddons').value) || 0);
      var errEl = document.getElementById('genErr');
      var resEl = document.getElementById('genResult');
      errEl.style.display = 'none';
      resEl.style.display = 'none';
      var btn = document.getElementById('genBtn');
      btn.disabled = true; btn.textContent = 'Generating\u2026';
      fetch('api/links.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ qty: qty, totalPrice: total, advanceAmount: adv, addonsPrice: addons })
      })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (!d.ok) {
            errEl.textContent = d.error || 'Failed to generate link.';
            errEl.style.display = 'block';
            return;
          }
          var url = HOST + '/' + d.slug;
          document.getElementById('genOrderId').textContent = d.order_id || ('BE-' + d.slug);
          document.getElementById('genUrl').textContent = url;
          resEl.style.display = 'block';
          loadLinks();
        })
        .catch(function () {
          errEl.textContent = 'Network error. Please try again.';
          errEl.style.display = 'block';
        })
        .finally(function () { btn.disabled = false; btn.textContent = 'Generate Link'; });
    }

    function copyGenUrl() {
      copyText(document.getElementById('genUrl').textContent);
      showToast('\u2713 Copied!');
    }

    // ---- Settings ----
    function loadSettings() {
      fetch('api/config.php')
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.upiId !== undefined) document.getElementById('sUpiId').value = d.upiId;
          if (d.totalPrice !== undefined) document.getElementById('sTotalPrice').value = d.totalPrice;
          if (d.advanceAmount !== undefined) document.getElementById('sAdvance').value = d.advanceAmount;
          if (d.whatsappLink !== undefined) document.getElementById('sWaLink').value = d.whatsappLink;
          if (d.instagramLink !== undefined) document.getElementById('sIgLink').value = d.instagramLink;

          if (d.addonsPrice !== undefined) {
            document.getElementById('sAddons').value = d.addonsPrice;
            document.getElementById('genAddons').value = d.addonsPrice;
          }
          if (d.qrImageUrl) {
            document.getElementById('qrPrev').src = d.qrImageUrl;
            document.getElementById('qrPrev').style.display = 'block';
            document.getElementById('qrOverlay').style.display = 'flex';
            document.getElementById('qrEmptyState').style.visibility = 'hidden';
          } else {
            removeQr(); // ensure it resets when switching channels
          }
          if (d.totalPrice) document.getElementById('genTotal').value = d.totalPrice;
          if (d.advanceAmount) document.getElementById('genAdvance').value = d.advanceAmount;
          updateGenCalc();
        })
        .catch(function () { });
    }

    function saveSettings(e) {
      e.preventDefault();
      var fd = new FormData();
      fd.append('upiId', document.getElementById('sUpiId').value.trim());
      fd.append('totalPrice', document.getElementById('sTotalPrice').value);
      fd.append('advanceAmount', document.getElementById('sAdvance').value);
      fd.append('addonsPrice', document.getElementById('sAddons').value);
      fd.append('whatsappLink', document.getElementById('sWaLink').value.trim());
      fd.append('instagramLink', document.getElementById('sIgLink').value.trim());
      if (removeQrFlag) fd.append('removeQr', '1');
      var qrFile = document.getElementById('sQrFile').files[0];
      if (qrFile) fd.append('qrImage', qrFile);

      fetch('api/config.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          var ok = document.getElementById('saveOk');
          if (d.ok) {
            ok.style.display = 'block';
            removeQrFlag = false;
            setTimeout(function () { ok.style.display = 'none'; }, 2500);

            if (document.getElementById('sTotalPrice').value) document.getElementById('genTotal').value = document.getElementById('sTotalPrice').value;
            if (document.getElementById('sAdvance').value) document.getElementById('genAdvance').value = document.getElementById('sAdvance').value;
            if (document.getElementById('sAddons').value) document.getElementById('genAddons').value = document.getElementById('sAddons').value;
            updateGenCalc();
          } else {
            showToast('Error: ' + (d.error || 'Could not save settings.'));
          }
        })
        .catch(function () { showToast('Network error.'); });
    }

    // ---- QR helpers ----
    var qrFileData = null;
    function handleDropzoneClick(e) {
      if (e.target.id === 'qrOverlay') return;
      document.getElementById('sQrFile').click();
    }
    function handleQrFile(file) {
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (ev) {
        document.getElementById('qrPrev').src = ev.target.result;
        document.getElementById('qrPrev').style.display = 'block';
        document.getElementById('qrOverlay').style.display = 'flex';
        document.getElementById('qrEmptyState').style.visibility = 'hidden';
        removeQrFlag = false;
      };
      reader.readAsDataURL(file);
    }
    function handleQrDrop(e) {
      var file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        var dt = new DataTransfer(); dt.items.add(file);
        document.getElementById('sQrFile').files = dt.files;
        handleQrFile(file);
      }
    }
    function removeQr(e) {
      if (e) e.stopPropagation();
      document.getElementById('qrPrev').src = '';
      document.getElementById('qrPrev').style.display = 'none';
      document.getElementById('qrOverlay').style.display = 'none';
      document.getElementById('qrEmptyState').style.visibility = 'visible';
      document.getElementById('sQrFile').value = '';
      removeQrFlag = true;
    }

    // ---- Order actions ----
    function confirmOrder(id) {
      if (!confirm('Mark this order as Confirmed?')) return;
      fetch('api/orders.php', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id, status: 'CONFIRMED' }) })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.ok === false) showToast('Error: ' + (d.error || 'Failed.'));
          else { showToast('Order confirmed.'); loadOrders(); closeOrderDetail(); }
        })
        .catch(function () { showToast('Network error.'); });
    }
    function raiseIssue(id) {
      if (!confirm('Raise issue on this order?')) return;
      fetch('api/orders.php', { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id, status: 'ISSUE_RAISED' }) })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.ok === false) showToast('Error: ' + (d.error || 'Failed.'));
          else { showToast('Issue raised.'); loadOrders(); closeOrderDetail(); }
        })
        .catch(function () { showToast('Network error.'); });
    }
    function deleteOrder(id) {
      if (!confirm('Delete this order? This cannot be undone.')) return;
      fetch('api/orders.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ order_row_id: id }) })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.ok === false) showToast('Error: ' + (d.error || 'Could not delete.'));
          else { showToast('Deleted.'); loadOrders(); loadLinks(); closeOrderDetail(); }
        })
        .catch(function () { showToast('Network error.'); });
    }

    // ---- Copy helpers ----
    function copyText(txt) {
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(txt).catch(function () { fallbackCopy(txt); });
      } else {
        fallbackCopy(txt);
      }
    }
    function fallbackCopy(txt) {
      var ta = document.createElement('textarea');
      ta.value = txt; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.focus(); ta.select();
      try { document.execCommand('copy'); } catch (e) { }
      document.body.removeChild(ta);
    }

    // ---- Lightbox ----
    function openLightbox(src) {
      document.getElementById('lightboxImg').src = src;
      document.getElementById('lightbox').style.display = 'flex';
    }
    function closeLightbox() {
      document.getElementById('lightbox').style.display = 'none';
      document.getElementById('lightboxImg').src = '';
    }

    // ---- Order Detail Overlay ----
    function openOrderDetail(o) {
      document.getElementById('odOid').textContent = o.order_id || '';
      document.getElementById('odBadge').outerHTML = '<span id="odBadge">' + statusBadge(o.display_status) + '</span>';
      document.getElementById('odDate').textContent = o.submitted_at ? o.submitted_at.slice(0, 16).replace('T', ' ') : '';
      var chEl = document.getElementById('odChannel');
      chEl.textContent = o.channel ? (o.channel.charAt(0).toUpperCase() + o.channel.slice(1)) : '';
      chEl.className = 'od-channel ' + (o.channel === 'instagram' ? 'ig' : 'wa');

      var body = document.getElementById('odBody');
      body.innerHTML = '';

      var bootQty = parseInt(o.qty) || 1;
      var addonsPrice = parseInt(o.addons_price) || 0;
      var totalBootsPrice = (parseInt(o.total_price) || 0) * bootQty;
      var paidAmt = parseInt(o.paid_amount) || 0;
      var grandTotal = totalBootsPrice + addonsPrice;
      var remaining = Math.max(0, grandTotal - paidAmt);
      var payMode = o.pay_mode === 'full' ? 'Full payment' : 'Advance';

      var fmt = function (n) { return '\u20B9' + Number(n).toLocaleString('en-IN'); };

      var custHtml =
        '<div><p class="od-sec-label">Customer Information</p>' +
        '<div class="od-info-card"><div class="od-info-grid">' +
        '<div class="od-kv"><span class="od-kv-label">Name</span><span class="od-kv-val">' + esc(o.name || '\u2014') + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">WhatsApp</span><span class="od-kv-val" style="font-family:monospace;font-size:12px">' + esc(o.whatsapp || '\u2014') + '</span></div>' +
        '</div></div></div>';

      var orderHtml =
        '<div><p class="od-sec-label">Order Details</p>' +
        '<div class="od-info-card"><div class="od-info-grid">' +
        '<div class="od-kv"><span class="od-kv-label">Boots Purchased</span><span class="od-kv-val">' + bootQty + '</span></div>' +
        '</div></div></div>';

      var payHtml =
        '<div><p class="od-sec-label">Payment Breakdown</p>' +
        '<div class="od-info-card"><div class="od-info-grid">' +
        '<div class="od-kv"><span class="od-kv-label">Total for Boots</span><span class="od-kv-val">' + fmt(totalBootsPrice) + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Add-ons Price</span><span class="od-kv-val">' + (addonsPrice > 0 ? fmt(addonsPrice) : '<span class="muted">\u2014</span>') + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Payment Mode</span><span class="od-kv-val">' + payMode + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Amount Paid' + (o.pay_mode === 'advance' ? ' (Advance)' : '') + '</span><span class="od-kv-val" style="color:var(--success)">' + fmt(paidAmt) + '</span></div>' +
        (o.pay_mode === 'advance' ? '<div class="od-kv"><span class="od-kv-label">Amount Remaining</span><span class="od-kv-val">' + fmt(remaining) + '</span></div>' : '') +
        '</div></div></div>';

      body.innerHTML = custHtml + orderHtml + payHtml;

      var footer = document.getElementById('odFooter');
      var canAct = o.display_status === 'PENDING_ACTION' && o.order_row_id;
      if (canAct) {
        footer.innerHTML =
          '<button class="od-action-confirm" onclick="confirmOrder(\'' + esc(o.order_row_id) + '\')">&#10003; Confirm Order</button>' +
          '<button class="od-action-issue" onclick="raiseIssue(\'' + esc(o.order_row_id) + '\')">&#9888; Raise Issue</button>';
      } else {
        footer.innerHTML = '<p class="od-footer-placeholder">' +
          (o.display_status === 'CONFIRMED' ? '&#10003; Order confirmed' :
            o.display_status === 'ISSUE_RAISED' ? '&#9888; Issue raised' : 'No actions available') +
          '</p>';
      }
      document.getElementById('odBackdrop').classList.add('open');
    }

    // ---- Link Detail Overlay ----
    function openLinkDetail(l) {
      var url = HOST + '/' + l.slug;
      var qty = parseInt(l.qty) || 1;
      var total = parseInt(l.total_price) || parseInt(l.price_per_boot) || 0;
      var advance = parseInt(l.advance_amount) || parseInt(l.advance_per_boot) || 0;
      var addons = parseInt(l.addons_price) || 0;
      var delivery = Math.max(0, total - advance);
      var date = l.link_created_at ? l.link_created_at.slice(0, 10) : '\u2014';
      var orderCount = parseInt(l.order_count) || 0;
      var fmt = function (n) { return '\u20B9' + n.toLocaleString('en-IN'); };

      // Header — use slug as ID, no status badge, no channel pill
      document.getElementById('odOid').textContent = l.slug || '';
      document.getElementById('odBadge').outerHTML = '<span id="odBadge"><span class="badge badge-orders">' + orderCount + ' order' + (orderCount !== 1 ? 's' : '') + '</span></span>';
      document.getElementById('odDate').textContent = date;
      var chEl = document.getElementById('odChannel');
      chEl.textContent = '';
      chEl.className = 'od-channel';

      // Body
      var body = document.getElementById('odBody');

      var configHtml =
        '<div>' +
        '<p class="od-sec-label">Link Configuration</p>' +
        '<div class="od-info-card">' +
        '<div class="od-info-grid">' +
        '<div class="od-kv"><span class="od-kv-label">Qty (boots)</span><span class="od-kv-val">' + qty + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Total / boot</span><span class="od-kv-val">' + fmt(total) + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Advance / boot</span><span class="od-kv-val">' + fmt(advance) + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">On delivery / boot</span><span class="od-kv-val">' + fmt(delivery) + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Add-ons Price</span><span class="od-kv-val">' + (addons > 0 ? fmt(addons) : '<span class="muted">\u2014</span>') + '</span></div>' +
        '<div class="od-kv"><span class="od-kv-label">Orders received</span><span class="od-kv-val">' + orderCount + '</span></div>' +
        '</div>' +
        '</div>' +
        '</div>';

      var urlHtml =
        '<div>' +
        '<p class="od-sec-label">Order URL</p>' +
        '<div class="od-url-row">' +
        '<span>' + esc(url) + '</span>' +
        '<button class="od-url-copy" onclick="copyText(\'' + esc(url) + '\');showToast(\'\u2713 Copied!\')">Copy</button>' +
        '</div>' +
        '</div>';

      body.innerHTML = configHtml + urlHtml;

      document.getElementById('odBackdrop').classList.add('open');
    }

    function closeOrderDetail() {
      document.getElementById('odBackdrop').classList.remove('open');
    }

    // ---- esc helper ----
    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }
  </script>
</body>

</html>