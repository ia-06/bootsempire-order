<?php
/**
 * order.php — Public customer order page (single-page, no wizard)
 */
require_once __DIR__ . '/db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
  header('Location: /');
  exit;
}

$pdo = getPDO();
$link = $pdo->prepare('SELECT * FROM `order_links` WHERE slug = ? LIMIT 1');
$link->execute([$slug]);
$row = $link->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  http_response_code(404);
  echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Link not found</title></head><body style="font-family:system-ui;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fff;color:#111"><div style="text-align:center"><p style="font-size:18px;font-weight:700">This order link is invalid or has expired.</p><p style="margin-top:8px;color:#767676;font-size:14px">Contact Bootsempire on Instagram or WhatsApp for a new link.</p></div></body></html>';
  exit;
}

$channel = $row['channel'] ?? 'instagram';
$qty = (int) ($row['qty'] ?? 1);
$total = (int) ($row['total_price'] ?? 6700);
$advance = (int) ($row['advance_amount'] ?? 2700);
$onDel = $total - $advance;
$slugSafe = htmlspecialchars($slug, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootsempire | Your Order</title>
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
      --surface-3: #e8e8ec;
      --border: rgba(0, 0, 0, 0.09);
      --border-hover: rgba(0, 0, 0, 0.16);
      --border-focus: rgba(0, 0, 0, 0.40);
      --text: #111111;
      --text-2: #444444;
      --text-m: #767676;
      --text-f: #aaaaaa;
      --accent: #111111;
      --accent-t: #ffffff;
      --success: #16a34a;
      --success-dim: rgba(22, 163, 74, 0.07);
      --success-border: rgba(22, 163, 74, 0.18);
      --error: #dc2626;
      --radius-xs: 6px;
      --radius-sm: 8px;
      --radius: 12px;
      --radius-lg: 16px;
      --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
      --tr: 160ms cubic-bezier(0.16, 1, 0.3, 1);
      --max-w: 560px;
      --px: clamp(18px, 5vw, 32px);
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      -webkit-text-size-adjust: none;
      scroll-behavior: smooth;
      overflow-x: hidden;
      /* Fixes horizontal scroll offset */
    }

    body {
      min-height: 100dvh;
      width: 100%;
      /* Ensures bounds are respected */
      overflow-x: hidden;
      /* Prevents 100vw gap bugs */
      font-family: var(--font);
      font-size: 15px;
      line-height: 1.6;
      color: var(--text);
      background: var(--bg);
    }

    button {
      cursor: pointer;
      font: inherit;
      border: none;
      background: none;
    }

    input,
    textarea,
    select {
      font: inherit;
      color: inherit;
    }

    img {
      display: block;
      max-width: 100%;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    :focus-visible {
      outline: 2px solid var(--accent);
      outline-offset: 3px;
      border-radius: 4px;
    }

    @media (prefers-reduced-motion: reduce) {

      *,
      *::before,
      *::after {
        transition-duration: 0.01ms !important;
      }
    }

    /* Header */
    .hdr {
      position: sticky;
      top: 0;
      z-index: 50;
      background: rgba(255, 255, 255, 0.90);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }

    .hdr-in {
      max-width: var(--max-w);
      margin: 0 auto;
      padding: 0 var(--px);
      height: 54px;
      display: flex;
      align-items: center;
    }

    .logo-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logo-wrap img {
      max-height: 22px;
      width: auto;
    }

    .logo-text {
      font-size: 14px;
      font-weight: 700;
      letter-spacing: -0.02em;
    }

    /* Layout */
    .main {
      max-width: var(--max-w);
      margin: 0 auto;
      padding: 40px var(--px) 88px;
      display: flex;
      flex-direction: column;
      gap: 28px;
    }

    /* Typography */
    .page-title {
      font-size: clamp(22px, 5vw, 27px);
      font-weight: 800;
      letter-spacing: -0.03em;
      line-height: 1.15;
      margin-bottom: 4px;
    }

    .page-sub {
      font-size: 14px;
      color: var(--text-m);
    }

    .section-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-m);
      margin-bottom: 10px;
    }

    /* Validity badge */
    .validity-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 99px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-2);
    }

    .validity-badge span {
      color: var(--text);
      font-weight: 700;
    }

    /* Bill card */
    .bill-card {
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }

    .bill-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      border-bottom: 1px solid var(--border);
      font-size: 14px;
    }

    .bill-row:last-child {
      border-bottom: none;
    }

    .bill-row.total-row {
      background: var(--surface-2);
      font-weight: 700;
    }

    .bill-row.total-row .bill-label {
      color: var(--text-2);
      font-size: 13px;
      font-weight: 600;
    }

    .bill-row.total-row .bill-amt {
      color: var(--text);
      font-size: 16px;
    }

    .bill-row.advance-row {
      background: #f0fdf4;
    }

    .bill-row.delivery-row {
      background: var(--surface);
    }

    .bill-label {
      color: var(--text-2);
    }

    .bill-amt {
      font-weight: 700;
      font-variant-numeric: tabular-nums;
    }

    .bill-amt.green {
      color: var(--success);
    }

    .bill-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 700;
      background: #dcfce7;
      color: #15803d;
      margin-left: 8px;
    }

    .bill-row-label-stack {
      display: flex;
      flex-direction: column;
      gap: 1px;
    }

    .bill-row-sublabel {
      font-size: 11px;
      color: var(--text-m);
      font-weight: 500;
    }

    /* Pay-now chip */
    .pay-now-chip {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      margin-top: 10px;
    }

    .pay-now-chip-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 700;
      color: var(--text-2);
    }

    .pay-now-chip-amt {
      font-size: 20px;
      font-weight: 800;
      color: var(--text);
      font-variant-numeric: tabular-nums;
      letter-spacing: -0.02em;
    }

    /* Form fields */
    .field {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .field label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text-2);
    }

    .field small {
      font-size: 12px;
      color: var(--text-m);
    }

    .inp {
      padding: 11px 13px;
      border: 1.5px solid var(--border-hover);
      border-radius: var(--radius-sm);
      font-size: 15px;
      color: var(--text);
      background: var(--bg);
      outline: none;
      transition: border-color var(--tr);
      width: 100%;
    }

    .inp:focus {
      border-color: var(--border-focus);
    }

    .inp.ok {
      border-color: #22c55e;
    }

    /* Pay mode toggle */
    .pay-toggle {
      display: flex;
      border: 1.5px solid var(--border-hover);
      border-radius: var(--radius-sm);
      overflow: hidden;
    }

    .pay-toggle-btn {
      flex: 1;
      padding: 10px 12px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-m);
      background: var(--surface);
      transition: all var(--tr);
      border: none;
      cursor: pointer;
    }

    .pay-toggle-btn+.pay-toggle-btn {
      border-left: 1.5px solid var(--border-hover);
    }

    .pay-toggle-btn.active {
      background: var(--text);
      color: var(--accent-t);
    }

    /* Payment card */
    .pay-card {
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }

    .pay-card-qr {
      display: flex;
      justify-content: center;
      padding: 24px 20px 20px;
      background: var(--surface);
      border-bottom: 1px solid var(--border);
    }

    .qr-placeholder {
      width: 180px;
      height: 180px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--surface-2);
      border-radius: var(--radius-sm);
      color: var(--text-f);
      font-size: 13px;
      text-align: center;
    }

    .pay-card-upi {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
    }

    .upi-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--text-m);
      margin-bottom: 2px;
    }

    .upi-id {
      font-size: 15px;
      font-weight: 700;
      font-family: monospace;
    }

    .copy-btn {
      padding: 6px 12px;
      border: 1px solid var(--border-hover);
      border-radius: var(--radius-xs);
      font-size: 12px;
      font-weight: 600;
      color: var(--text-m);
      transition: all var(--tr);
    }

    .copy-btn:hover {
      border-color: rgba(0, 0, 0, .3);
      color: var(--text);
    }

    /* Upload box */
    .upload-box {
      border: 2px dashed var(--border-hover);
      border-radius: var(--radius);
      padding: 28px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      background: var(--surface);
      transition: all var(--tr);
      position: relative;
    }

    .upload-box:hover,
    .upload-box.drag {
      border-color: var(--text);
      background: var(--surface-2);
    }

    .upload-box input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .upload-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: var(--surface-2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-m);
    }

    .upload-title {
      font-size: 14px;
      font-weight: 600;
      text-align: center;
    }

    .upload-sub {
      font-size: 12px;
      color: var(--text-m);
      text-align: center;
    }

    .img-preview-wrap {
      border-radius: var(--radius);
      overflow: hidden;
      position: relative;
      aspect-ratio: 4/3;
      background: var(--surface);
      border: 1px solid var(--border);
    }

    .img-preview-wrap img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .change-btn {
      position: absolute;
      bottom: 10px;
      right: 10px;
      padding: 5px 11px;
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid var(--border-hover);
      border-radius: 99px;
      font-size: 12px;
      font-weight: 600;
      backdrop-filter: blur(4px);
      transition: all var(--tr);
    }

    .change-btn:hover {
      background: #fff;
      border-color: rgba(0, 0, 0, .3);
    }

    /* Details card */
    .details-card {
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    /* Error banner */
    .err-banner {
      padding: 12px 16px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: var(--radius-sm);
      font-size: 13px;
      color: var(--error);
      display: none;
    }

    /* Buttons */
    .btn-primary {
      width: 100%;
      padding: 14px 20px;
      background: var(--text);
      color: var(--accent-t);
      border-radius: var(--radius);
      font-size: 15px;
      font-weight: 700;
      letter-spacing: -0.01em;
      transition: all var(--tr);
    }

    .btn-primary:hover:not(:disabled) {
      background: #333;
    }

    .btn-primary:disabled {
      background: var(--surface-3);
      color: var(--text-f);
      cursor: not-allowed;
    }

    /* Delivery Prompt Screen */
    .delivery-screen {
      display: none;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 20px 0;
      gap: 20px;
    }

    .delivery-screen.on {
      display: flex;
    }

    .delivery-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #fffbeb;
      border: 2px solid #fde68a;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #d97706;
    }

    .delivery-title {
      font-size: 24px;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: var(--text);
    }

    .delivery-sub {
      font-size: 15px;
      color: var(--text-m);
      line-height: 1.6;
      max-width: 420px;
      margin: 10px auto 0;
    }

    .delivery-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 100%;
      max-width: 420px;
      margin-top: 10px;
    }

    .btn-secondary {
      width: 100%;
      padding: 14px 20px;
      background: transparent;
      color: var(--text-2);
      border: 1px solid var(--border-hover);
      border-radius: var(--radius);
      font-size: 15px;
      font-weight: 700;
      transition: all var(--tr);
    }

    .btn-secondary:hover {
      background: var(--surface);
      color: var(--text);
    }

    /* Success */
    .success-screen {
      display: none;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding: 32px 0;
      gap: 20px;
    }

    .success-screen.on {
      display: flex;
    }

    .success-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: var(--success-dim);
      border: 2px solid var(--success-border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--success);
    }

    .success-title {
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -0.03em;
    }

    .success-sub {
      font-size: 15px;
      color: var(--text-m);
      max-width: 38ch;
      line-height: 1.7;
    }

    .success-card {
      width: 100%;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px 22px;
      text-align: left;
    }

    .success-card-title {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 16px;
      color: var(--text-2);
    }

    .channel-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      padding: 14px 20px;
      border-radius: var(--radius);
      font-size: 15px;
      font-weight: 700;
      transition: all var(--tr);
      text-decoration: none;
    }

    .channel-btn.whatsapp {
      background: #25D366;
      color: #fff;
    }

    .channel-btn.whatsapp:hover {
      background: #1ebe5d;
    }

    .channel-btn.instagram {
      background: linear-gradient(135deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
      color: #fff;
    }

    .channel-btn.instagram:hover {
      opacity: 0.92;
    }

    /* Ticker Banner */
    @font-face {
      font-family: "Zing Rust Demo Base";
      src: url("https://framerusercontent.com/assets/k4MlYkPvaCyNSNadZpDgmQScg2w.woff2") format("woff2");
      font-display: swap;
      font-style: normal;
      font-weight: 400;
    }

    .ticker-wrap {
      width: 100%;
      /* Changed from 100vw */
      height: 40px;
      background-color: #3AA63A;
      overflow: hidden;
      display: flex;
      align-items: center;
    }

    .ticker-content {
      display: flex;
      align-items: center;
      gap: 30px;
      white-space: nowrap;
      padding-right: 30px;
      flex-shrink: 0;
      /* CRITICAL: Prevents content from squishing */
      animation: tickerLoop 25s linear infinite;
      /* Desktop Speed */
    }

    .ticker-text {
      font-family: "Zing Rust Demo Base", sans-serif;
      color: #ffffff;
      font-size: 33px;
      line-height: 40px;
      text-transform: uppercase;
      padding-top: 4px;
      /* Optical alignment for custom font */
    }

    .ticker-svg {
      width: 20px;
      height: 20px;
      fill: #ffffff;
      flex-shrink: 0;
    }

    .text-desktop {
      display: inline;
    }

    .text-mobile {
      display: none;
    }

    @keyframes tickerLoop {
      0% {
        transform: translateX(0);
      }

      100% {
        transform: translateX(-100%);
      }

      /* Scrolls exactly its own width seamlessly */
    }

    @media (max-width: 768px) {
      .text-desktop {
        display: none;
      }

      .text-mobile {
        display: inline;
      }

      /* Mobile Speed (100%) */
    }
  </style>
</head>

<body>

  <header class="hdr">
    <div class="hdr-in">
      <div class="logo-wrap">
        <img src="/logo.svg" alt="Bootsempire">
      </div>
    </div>
  </header>

  <div class="ticker-wrap">
    <div class="ticker-content">
      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">WORLDWIDE SHIPPING</span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">
        <span class="text-desktop">TRUSTED BY MORE THAN 10,000 PLAYERS</span>
        <span class="text-mobile">TRUSTED BY MORE THAN 10K+ PLAYERS</span>
      </span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">WORLDWIDE SHIPPING</span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">
        <span class="text-desktop">TRUSTED BY MORE THAN 10,000 PLAYERS</span>
        <span class="text-mobile">TRUSTED BY MORE THAN 10K+ PLAYERS</span>
      </span>
    </div>

    <div class="ticker-content" aria-hidden="true">
      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">WORLDWIDE SHIPPING</span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">
        <span class="text-desktop">TRUSTED BY MORE THAN 10,000 PLAYERS</span>
        <span class="text-mobile">TRUSTED BY MORE THAN 10K+ PLAYERS</span>
      </span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">WORLDWIDE SHIPPING</span>

      <svg class="ticker-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#f8f7f7"
        viewBox="0 0 256 256">
        <path
          d="M240,128a15.79,15.79,0,0,1-10.5,15l-63.44,23.07L143,229.5a16,16,0,0,1-30,0L89.94,166.06,26.5,143a16,16,0,0,1,0-30L89.94,89.94,113,26.5a16,16,0,0,1,30,0l23.07,63.44L229.5,113A15.79,15.79,0,0,1,240,128Z">
        </path>
      </svg>
      <span class="ticker-text">
        <span class="text-desktop">TRUSTED BY MORE THAN 10,000 PLAYERS</span>
        <span class="text-mobile">TRUSTED BY MORE THAN 10K+ PLAYERS</span>
      </span>
    </div>
  </div>

  <main class="main" id="mainWrap">

    <div id="formContent" style="display:flex; flex-direction:column; gap:28px;">

      <!-- Page header -->
      <div id="pageHeader">
        <h1 class="page-title">Your Order</h1>
        <p class="page-sub">Fill in your details and pay to confirm your order.</p>
      </div>

      <!-- Validity badge -->
      <div>
        <div class="validity-badge">
          Order valid for <?= $qty ?> Boot<?= $qty > 1 ? 's' : '' ?>
        </div>
      </div>

      <!-- Payment Option -->
      <div id="sectionPayMode">
        <p class="section-label">Payment Option</p>
        <div class="pay-toggle" role="group" aria-label="Payment option">
          <button class="pay-toggle-btn active" id="toggleAdvance" onclick="setPayMode('advance')" type="button">Pay
            Advance</button>
          <button class="pay-toggle-btn" id="toggleFull" onclick="setPayMode('full')" type="button">Pay in Full</button>
        </div>
      </div>

      <!-- Payment Breakdown -->
      <div id="sectionBill">
        <p class="section-label">Payment Breakdown</p>
        <div class="bill-card">
          <div class="bill-row">
            <span class="bill-label">Price for boots (x<?= $qty ?>)</span>
            <span class="bill-amt">&#8377;<?= number_format($total) ?></span>
          </div>
          <!-- On delivery row (advance mode) -->
          <div class="bill-row delivery-row" id="onDelRow">
            <div class="bill-row-label-stack">
              <span class="bill-label">Pay on Delivery (Remaining)</span>
              <span class="bill-row-sublabel">&#8377;<?= number_format($onDel / $qty) ?> &times; <?= $qty ?>
                boot<?= $qty > 1 ? 's' : '' ?></span>
            </div>
            <span class="bill-amt" id="onDelAmt">&#8377;<?= number_format($onDel) ?></span>
          </div>
          <!-- Advance row (advance mode) -->
          <div class="bill-row advance-row" id="advanceRow">
            <div class="bill-row-label-stack">
              <span class="bill-label">Pay Now (Advance) <span class="bill-badge">Due now</span></span>
              <span class="bill-row-sublabel">&#8377;<?= number_format($advance / $qty) ?> &times; <?= $qty ?>
                boot<?= $qty > 1 ? 's' : '' ?></span>
            </div>
            <span class="bill-amt green" id="advanceAmt">&#8377;<?= number_format($advance) ?></span>
          </div>
          <div class="bill-row total-row">
            <span class="bill-label">Total</span>
            <span class="bill-amt" id="totalAmt">&#8377;<?= number_format($total) ?></span>
          </div>
        </div>
        <!-- Pay-now chip -->
        <div class="pay-now-chip" id="payNowChip">
          <span class="pay-now-chip-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            <span id="payNowLabel">Amount to pay now (advance)</span>
          </span>
          <span class="pay-now-chip-amt" id="payNowAmt">&#8377;<?= number_format($advance) ?></span>
        </div>
      </div>

      <!-- QR + UPI -->
      <div id="sectionQr">
        <p class="section-label">Scan &amp; Pay</p>
        <div class="pay-card">
          <div class="pay-card-qr">
            <div class="qr-placeholder" id="qrPlaceholder">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="2" y="2" width="8" height="8" rx="1" />
                <rect x="14" y="2" width="8" height="8" rx="1" />
                <rect x="2" y="14" width="8" height="8" rx="1" />
                <path d="M14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z" />
              </svg>
              <span>Loading QR&hellip;</span>
            </div>
            <img id="qrImg" src="" alt="UPI QR Code" width="180" height="180"
              style="display:none;object-fit:contain;border-radius:8px">
          </div>
          <div class="pay-card-upi">
            <div>
              <p class="upi-label">UPI ID</p>
              <p class="upi-id" id="upiIdText">Loading&hellip;</p>
            </div>
            <button class="copy-btn" onclick="copyUpi()" type="button">Copy</button>
          </div>

          <div
            style="display: flex; justify-content: space-between; align-items: center; padding: 12px 18px; border-top: 1px solid var(--border); background: var(--surface);">
            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: var(--text-2);">PAY
              SECURELY</span>
            <span
              style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--success);">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              </svg>
              100% Secure Payment
            </span>
          </div>

        </div>
      </div>

      <!-- Your Details -->
      <div id="sectionDetails">
        <p class="section-label">Your Details</p>
        <div class="details-card">
          <div class="field">
            <label for="custName">Full Name</label>
            <input class="inp" id="custName" type="text" placeholder="Your full name" autocomplete="name"
              oninput="onNameInput(this)">
          </div>
          <div class="field">
            <label for="custWa">WhatsApp Number</label>
            <div style="display:flex;align-items:stretch;gap:0">
              <div
                style="padding:11px 13px;border:1.5px solid var(--border-hover);border-right:none;border-radius:var(--radius-sm) 0 0 var(--radius-sm);font-size:15px;font-weight:600;color:var(--text-m);background:var(--surface);white-space:nowrap;flex-shrink:0">
                +91</div>
              <input class="inp" id="custWa" type="tel" placeholder="10-digit number" autocomplete="tel" maxlength="10"
                oninput="onPhoneInput(this)"
                style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;border-left:none">
            </div>
            <small>We'll send your order confirmation to this number.</small>
          </div>
        </div>
      </div>

      <div class="err-banner" id="errBanner"></div>

      <div id="sectionSubmit">
        <button class="btn-primary" id="submitBtn" onclick="submitOrder()" disabled>Place Order</button>
      </div>

    </div>
    <div class="delivery-screen" id="deliveryScreen">
      <div class="delivery-icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
      </div>
      <div>
        <h2 class="delivery-title">Delivery Timeline</h2>
        <p class="delivery-sub">Please note that the estimated delivery time for all custom boots is between <strong>15
            to 20 days</strong>. By proceeding, you acknowledge and agree to this timeline.</p>
      </div>
      <div class="delivery-actions">
        <button class="btn-primary" id="confirmOrderBtn" onclick="processOrder()">I agree - Continue</button>
        <button class="btn-secondary" onclick="cancelDeliveryPrompt()">Cancel</button>
      </div>
    </div>

    <div class="success-screen" id="successScreen">
      <div class="success-icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>
      <h2 class="success-title">Order placed!</h2>
      <div class="success-card">
        <p class="success-card-title">NEXT STEPS</p>
        <p style="font-size:14px;color:var(--text-2);line-height:1.65;">Send payment proof to our employee and we will
          confirm your order upon verification.</p>
        <div style="margin-top:14px">
          <a id="channelBtn" href="#" target="_blank" rel="noopener noreferrer" class="channel-btn whatsapp">
            <svg id="channelIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
              <path
                d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.534 5.857L0 24l6.335-1.51A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.007-1.369l-.36-.213-3.724.888.925-3.63-.234-.374A9.818 9.818 0 1 1 12 21.818z" />
            </svg>
            <span id="channelBtnText">Send proof on WhatsApp</span>
          </a>
        </div>
      </div>
    </div>

  </main>

  <script>
    var SLUG = '<?= $slugSafe ?>';
    var CHANNEL = '<?= htmlspecialchars($channel, ENT_QUOTES) ?>';
    var QTY = <?= $qty ?>;
    var TOTAL = <?= $total ?>;
    var ADVANCE = <?= $advance ?>;
    var ON_DEL = <?= $onDel ?>;

    var upiId = '';
    var whatsappLink = '';
    var instagramLink = '';
    var payMode = 'advance';
    var screenshotFile = null;

    document.addEventListener('DOMContentLoaded', function () { loadConfig(); });

    function loadConfig() {
      fetch('/api/config.php?slug=' + encodeURIComponent(SLUG))
        .then(function (r) { return r.json(); })
        .then(function (d) {
          upiId = d.upiId || '';
          whatsappLink = d.whatsappLink || '';
          instagramLink = d.instagramLink || '';
          document.getElementById('upiIdText').textContent = upiId || 'N/A';
          if (d.qrDataUrl) {
            var img = document.getElementById('qrImg');
            img.src = d.qrDataUrl;
            img.style.display = 'block';
            document.getElementById('qrPlaceholder').style.display = 'none';
          }
          var btn = document.getElementById('channelBtn');
          var btnt = document.getElementById('channelBtnText');
          if (CHANNEL === 'whatsapp' && whatsappLink) {
            btn.href = whatsappLink; btn.className = 'channel-btn whatsapp';
            btnt.textContent = 'Contact us on WhatsApp';
          } else if (CHANNEL === 'instagram' && instagramLink) {
            btn.href = instagramLink; btn.className = 'channel-btn instagram';
            btnt.textContent = 'Contact us on Instagram';
          }
        })
        .catch(function () { document.getElementById('upiIdText').textContent = 'Failed to load'; });
    }

    function setPayMode(mode) {
      payMode = mode;
      document.getElementById('toggleAdvance').classList.toggle('active', mode === 'advance');
      document.getElementById('toggleFull').classList.toggle('active', mode === 'full');
      var fmt = function (n) { return '\u20B9' + n.toLocaleString('en-IN'); };
      if (mode === 'full') {
        document.getElementById('advanceRow').style.display = 'none';
        document.getElementById('onDelRow').style.display = 'none';
        document.getElementById('totalAmt').textContent = fmt(TOTAL);
        document.getElementById('payNowLabel').textContent = 'Amount to pay now (full)';
        document.getElementById('payNowAmt').textContent = fmt(TOTAL);
      } else {
        document.getElementById('advanceRow').style.display = '';
        document.getElementById('onDelRow').style.display = '';
        document.getElementById('advanceAmt').textContent = fmt(ADVANCE);
        document.getElementById('onDelAmt').textContent = fmt(ON_DEL);
        document.getElementById('totalAmt').textContent = fmt(TOTAL);
        document.getElementById('payNowLabel').textContent = 'Amount to pay now (advance)';
        document.getElementById('payNowAmt').textContent = fmt(ADVANCE);
      }
    }

    function handleDrag(e, over) {
      e.preventDefault();
      document.getElementById('uploadBox').classList.toggle('drag', over);
    }
    function handleDrop(e) {
      e.preventDefault();
      document.getElementById('uploadBox').classList.remove('drag');
      var f = e.dataTransfer.files[0];
      if (f && f.type.startsWith('image/')) handleFile(f);
    }
    function handleFile(f) {
      if (!f) return;
      screenshotFile = f;
      var reader = new FileReader();
      reader.onload = function (ev) {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('previewWrap').style.display = 'block';
        document.getElementById('uploadBox').style.display = 'none';
      };
      reader.readAsDataURL(f);
    }
    function clearFile() {
      screenshotFile = null;
      document.getElementById('previewWrap').style.display = 'none';
      document.getElementById('uploadBox').style.display = 'flex';
      document.getElementById('screenshotFile').value = '';
    }

    function onNameInput(el) {
      var ok = el.value.trim().length >= 2;
      el.classList.toggle('ok', ok);
      validateForm();
    }
    function onPhoneInput(el) {
      el.value = el.value.replace(/\D/g, '').slice(0, 10);
      el.classList.toggle('ok', el.value.length === 10);
      validateForm();
    }
    function validateForm() {
      var nameOk = document.getElementById('custName').value.trim().length >= 2;
      var phoneOk = document.getElementById('custWa').value.length === 10;
      document.getElementById('submitBtn').disabled = !(nameOk && phoneOk);
    }

    function copyUpi() {
      if (!upiId) return;
      navigator.clipboard.writeText(upiId).then(function () {
        var btn = document.querySelector('.copy-btn');
        if (btn) { btn.textContent = 'Copied!'; setTimeout(function () { btn.textContent = 'Copy'; }, 2000); }
      });
    }

    function submitOrder() {
      var name = document.getElementById('custName').value.trim();
      var wa = document.getElementById('custWa').value.trim();
      if (!name || !wa) { showErr('Please fill in your name and WhatsApp number.'); return; }
      hideErr();

      // Hide form, show delivery timeline prompt
      document.getElementById('formContent').style.display = 'none';
      document.getElementById('deliveryScreen').classList.add('on');
      window.scrollTo(0, 0);
    }

    function cancelDeliveryPrompt() {
      // Hide delivery prompt, return to form
      document.getElementById('deliveryScreen').classList.remove('on');
      document.getElementById('formContent').style.display = 'flex';
      window.scrollTo(0, 0);
    }

    function processOrder() {
      var name = document.getElementById('custName').value.trim();
      var wa = document.getElementById('custWa').value.trim();

      var btn = document.getElementById('confirmOrderBtn');
      btn.disabled = true;
      btn.textContent = 'Placing order\u2026';

      var fd = new FormData();
      fd.append('slug', SLUG);
      fd.append('name', name);
      fd.append('whatsapp', wa);
      fd.append('payMode', payMode);

      fetch('/api/orders.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) {
            document.getElementById('deliveryScreen').classList.remove('on');
            showSuccess();
          } else {
            cancelDeliveryPrompt();
            showErr(d.error || 'Something went wrong. Please try again.');
            btn.disabled = false;
            btn.textContent = 'I agree - Continue';
          }
        })
        .catch(function () {
          cancelDeliveryPrompt();
          showErr('Network error. Please check your connection and try again.');
          btn.disabled = false;
          btn.textContent = 'I agree - Continue';
        });
    }

    function showSuccess() {
      document.getElementById('formContent').style.display = 'none';
      document.getElementById('deliveryScreen').classList.remove('on');
      document.getElementById('successScreen').classList.add('on');
      window.scrollTo(0, 0);
    }

    function showErr(msg) {
      var b = document.getElementById('errBanner');
      b.textContent = msg;
      b.style.display = 'block';
    }
    function hideErr() {
      document.getElementById('errBanner').style.display = 'none';
    }
  </script>
</body>

</html>