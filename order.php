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
$bootTotal = (int) ($row['total_price'] ?? 6700);
$addons = (int) ($row['addons_price'] ?? 0);
$advance = (int) ($row['advance_amount'] ?? 2700);

$grandTotal = $bootTotal + $addons;
$onDel = $grandTotal - $advance;
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

    /* NEW: Aligns the SVG and text group perfectly on the horizontal axis */
    .bill-row-left {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 14px;
    }

    /* NEW: Stacks the text vertically next to the SVG */
    .bill-text-group {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .bill-row.total-row {
      background: #f8fafc;
      font-weight: 700;
    }

    .bill-row.total-row .bill-label {
      color: var(--text-2);
      font-size: 16px;
      font-weight: 600;
    }

    .bill-row.total-row .bill-amt {
      color: var(--text);
      font-size: 16px;
    }

    .bill-label {
      color: var(--text-2);
      display: flex;
      align-items: center;
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

    .bill-row-sublabel {
      font-size: 11px;
      color: var(--text-m);
      font-weight: 500;
    }

    /* Unified Mobile-Optimized Cards (Following Global Style Guidelines) */
    .pay-now-chip,
    .reservation-chip {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f0fdf4;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 14px 20px;
      /* Matches bill-row padding perfectly */
      margin-top: 16px;
      box-shadow: none;
      transition: all 0.2s ease;
    }

    .pay-now-chip-left,
    .reservation-chip-left {
      display: flex;
      flex-direction: row;
      align-items: center;
      gap: 14px;
      /* Matches bill-row-left gap */
    }

    .pay-now-text-group,
    .reservation-text-group {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .pay-now-chip-label,
    .reservation-title {
      font-size: 14px;
      /* Matches bill-label exactly */
      font-weight: 600;
      color: var(--text-2);
      display: flex;
      align-items: center;
    }

    .pay-now-chip-subtitle,
    .reservation-subtitle {
      font-size: 11px;
      /* Matches bill-row-sublabel exactly */
      color: var(--text-m);
      font-weight: 500;
      line-height: 1.4;
    }

    .reservation-chip {
      background-color: #ffcece;
      border-color: #ff7f7f;
    }

    /* Redesigned Trust Features Card (Stacked Text & Responsive) */
    .trust-features-card {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f0fdf4;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 14px 8px;
      margin-top: 16px;
      gap: 4px;
    }

    .trust-item {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      flex: 1;
    }

    .trust-item svg {
      color: #16a34a;
      flex-shrink: 0;
    }

    /* New container to stack the two text lines */
    .trust-item-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
    }

    .trust-item-text span {
      font-size: 10.5px;
      font-weight: 700;
      color: var(--text-2);
      line-height: 1.15;
      white-space: nowrap;
    }

    .trust-item-text span.trust-sub {
      font-weight: 500;
      color: var(--text-m);
      margin-top: 1px;
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
          Order valid for
          <?= $qty ?> Boot<?= $qty > 1 ? 's' : '' ?>
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
            <div class="bill-row-left">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible">
                <g fill="transparent">
                  <path d="M0 15C0 6.716 6.716 0 15 0s15 6.716 15 15-6.716 15-15 15S0 23.284 0 15" />
                  <path
                    d="m5.5 21.75 9.063 3.75 9.375-4.375V10.5l-9.375-5L10.5 8l-5 2.813L10.5 8l8.438 5v3.75V13l-4.375 2.188V25.5l9.375-4.375V10.5l-9.375 4.688L5.5 10.813Z"
                    stroke-width="2" stroke="#000" stroke-linejoin="round" />
                </g>
              </svg>
              <div class="bill-text-group">
                <span class="bill-label"><strong>Boot Price (<?= $qty ?>×)</strong></span>
              </div>
            </div>
            <span class="bill-amt">&#8377;<?= number_format($bootTotal) ?></span>
          </div>

          <?php if ($addons > 0): ?>
            <div class="bill-row">
              <div class="bill-row-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible">
                  <path d="M15 0c8.284 0 15 6.716 15 15s-6.716 15-15 15S0 23.284 0 15 6.716 0 15 0" fill="#fef3c7" />
                  <path d="M15 8v14M8 15h14" fill="transparent" stroke-width="2.5" stroke="#d97706"
                    stroke-linecap="round" />
                </svg>
                <div class="bill-text-group">
                  <span class="bill-label"><strong>Custom Add-ons</span></strong>
                </div>
              </div>
              <span class="bill-amt">&#8377;<?= number_format($addons) ?></span>
            </div>
          <?php endif; ?>

          <div class="bill-row advance-row" id="advanceRow">
            <div class="bill-row-left">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible">
                <path d="M15 0c8.284 0 15 6.716 15 15s-6.716 15-15 15S0 23.284 0 15 6.716 0 15 0" fill="#def4e1" />
                <path
                  d="m10.8 10.2.3-1.5h8.7l-.6 1.5h-2.1s.6.943.6 1.5h2.1l-.6 1.8H18s.304 3-4.5 3c1.357 1.357 4.2 5.4 4.2 5.4h-2.1l-4.8-5.7V15s5.1 1.006 5.1-1.5h-5.4l.6-1.8h4.8s-.225-.825-1.5-1.2-3.6-.3-3.6-.3"
                  fill="#198a42" />
              </svg>
              <div class="bill-text-group">
                <span class="bill-label"><strong>Pay Now (Advance) </strong><span class="bill-badge">Due
                    now</span></span>
                <span class="bill-row-sublabel">Confirm your order by paying now</span>
              </div>
            </div>
            <span class="bill-amt green" id="advanceAmt">&#8377;<?= number_format($advance) ?></span>
          </div>

          <div class="bill-row delivery-row" id="onDelRow">
            <div class="bill-row-left">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible">
                <path d="M15 0c8.284 0 15 6.716 15 15s-6.716 15-15 15S0 23.284 0 15 6.716 0 15 0" fill="#ccc" />
                <path
                  d="M18.9 17.4a1.5 1.5 0 1 1-.001 3.001A1.5 1.5 0 0 1 18.9 17.4Zm-7.8 0a1.5 1.5 0 1 1-.001 3.001A1.5 1.5 0 0 1 11.1 17.4Z"
                  fill="transparent" stroke="#000" />
                <path d="M20.4 18.9h2.1V15l-2.4-3.9h-2.7v3.3h4.5-4.5v4.5h-4.5 4.5V9.3H7.5v9.6h2.1" fill="transparent"
                  stroke="#000" stroke-linejoin="round" />
              </svg>
              <div class="bill-text-group">
                <span class="bill-label"><strong>Remaining on Delivery</strong></span>
                <span class="bill-row-sublabel">Payable on delivery (COD)</span>
              </div>
            </div>
            <span class="bill-amt" id="onDelAmt">&#8377;<?= number_format($onDel) ?></span>
          </div>

          <div class="bill-row total-row">
            <span class="bill-label">Total</span>
            <span class="bill-amt" id="totalAmt">&#8377;<?= number_format($grandTotal) ?></span>
          </div>
        </div>

        <div class="pay-now-chip" id="payNowChip">
          <div class="pay-now-chip-left">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible">
              <g fill="transparent">
                <path d="M15 .484c8.284 0 15 6.607 15 14.758S23.284 30 15 30 0 23.393 0 15.242 6.716.484 15 .484" />
                <g stroke="#2f9454" stroke-linecap="round">
                  <path
                    d="M3 3.871s3.307 0 6.368-.968C12.429 1.936 15.245 0 15.245 0s3.306 1.966 6.367 2.903S27 3.871 27 3.871v16.451s-2.449 3.024-5.388 5.323c-2.938 2.298-6.367 3.871-6.367 3.871s-3.306-1.573-6.367-3.871S3 20.322 3 20.322Z"
                    stroke-width="3" stroke-linejoin="round" />
                  <path d="m10.836 13.548 3.592 3.871 6.694-5.807" stroke-width="2" stroke-miterlimit="10" />
                </g>
              </g>
            </svg>
            <div class="pay-now-text-group">
              <span class="pay-now-chip-label">
                <span id="payNowLabel">Pay <span
                    style="color: #2ca659; font-weight: 700;">₹<?= number_format($advance) ?></span> to Confirm
                  Order</span>
              </span>
              <span class="pay-now-chip-subtitle" id="payNowSubtitle">Secure your order. Pay the remaining
                ₹<?= number_format($onDel) ?> on delivery.</span>
            </div>
          </div>
        </div>

        <div class="reservation-chip">
          <div class="reservation-chip-left">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" overflow="visible"
              viewBox="0 0 26.471 30" style="flex-shrink:0;">
              <path d="m19.412 23.75 2.132 2.132 4.265-4.264M12.353 11.471v5.735l3.529 3.529" fill="transparent"
                stroke-width="3" stroke="#ff6868" stroke-linecap="round" />
              <path
                d="M13.235 3.529c7.31 0 13.236 5.926 13.236 13.236q-.001 1.065-.164 2.086a6.6 6.6 0 0 0-2.472-.308q.135-.833.136-1.705c0-5.888-4.774-10.662-10.662-10.662S2.647 10.95 2.647 16.838 7.42 27.5 13.309 27.5c1.6 0 3.118-.352 4.48-.984.174.83.504 1.602.958 2.285A13.2 13.2 0 0 1 13.235 30C5.926 30 0 24.074 0 16.765 0 9.455 5.926 3.529 13.235 3.529"
                fill="#ff6868" />
              <path d="M9.706 0h7.059" fill="transparent" stroke-width="3" stroke="#ff6868" stroke-linecap="round" />
            </svg>
            <div class="reservation-text-group">
              <span class="reservation-title">Your order can be delivered faster.</span>
              <span class="reservation-subtitle">Next import shipment leaving soon. Order now to get yours in 8 - 10
                days!</span>
            </div>
          </div>
        </div>

      </div>

      <!-- QR + UPI -->
      <div id="sectionQr">
        <p class="section-label">Pay Securely Via UPI</p>
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

          <div class="pay-card-upi" style="border-top: 1px solid var(--border); padding-top: 12px;">
            <div>
              <p class="upi-label">UPI Number</p>
              <p class="upi-id" id="upiNumberText">Loading&hellip;</p>
            </div>
            <button class="copy-btn" onclick="copyUpiNumber(this)" type="button">Copy</button>
          </div>

          <div
            style="display: flex; flex-direction: column; gap: 12px; padding: 12px 18px 16px; border-top: 1px solid var(--border); background: var(--surface);">

            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
              <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.08em; color: var(--text-2);">PAY
                SECURELY VIA UPI</span>
              <span
                style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--success);">
                <svg xmlns="http://www.w3.org/2000/svg" width="12.049" height="15" fill="none" overflow="visible">
                  <g fill="transparent" stroke-width="2" stroke="#2f9454" stroke-linecap="round">
                    <path
                      d="M0 1.967s1.66 0 3.197-.492C4.734.984 6.148 0 6.148 0s1.659.999 3.196 1.475 2.705.492 2.705.492v8.361s-1.23 1.537-2.705 2.705S6.148 15 6.148 15s-1.66-.799-3.197-1.967S0 10.328 0 10.328Z"
                      stroke-linejoin="round" />
                    <path d="m3.934 6.885 1.803 1.967 3.361-2.951" stroke-miterlimit="10" />
                  </g>
                </svg>
                100% Secure Payment
              </span>
            </div>

            <div
              style="display: flex; justify-content: center; align-items: center; gap: 16px; margin-top: 4px; width: 100%; flex-wrap: wrap;">

              <svg xmlns="http://www.w3.org/2000/svg" width="50.125" height="20" fill="none" overflow="visible">
                <path
                  d="M23.75 9.812v5.813h-1.875V1.25h4.875a4.65 4.65 0 0 1 3.187 1.25c.875.75 1.313 1.875 1.313 3.062 0 1.188-.438 2.25-1.313 3.063a4.54 4.54 0 0 1-3.187 1.25Zm0-6.812v5h3.125c.687 0 1.375-.25 1.812-.75 1-.938 1-2.5.063-3.438l-.063-.062c-.5-.5-1.125-.813-1.812-.75Zm11.812 2.5c1.375 0 2.438.375 3.25 1.125.813.75 1.188 1.75 1.188 3v6h-1.75V14.25h-.063c-.75 1.125-1.812 1.687-3.062 1.687-1.063 0-2-.312-2.75-.937-.688-.625-1.125-1.5-1.125-2.438q0-1.5 1.125-2.437c.75-.625 1.812-.875 3.062-.875 1.125 0 2 .187 2.688.625v-.438c0-.625-.25-1.25-.75-1.625a2.73 2.73 0 0 0-1.813-.687q-1.593 0-2.437 1.312l-1.625-1c1-1.312 2.312-1.937 4.062-1.937m-2.375 7.125c0 .5.25.937.625 1.187.438.313.938.5 1.438.5.75 0 1.5-.312 2.062-.875.625-.562.938-1.25.938-2-.563-.437-1.375-.687-2.438-.687q-1.125 0-1.875.562-.75.469-.75 1.313m16.938-6.812L43.937 20h-1.875l2.313-4.938-4.063-9.187h2l2.938 7.062h.062l2.875-7.062h1.938Z"
                  fill="#5f6368" />
                <path
                  d="M16.062 8.562c0-.562-.062-1.125-.125-1.687H8.125v3.187h4.437c-.187 1-.75 1.938-1.625 2.5v2.063h2.688c1.562-1.438 2.437-3.563 2.437-6.063"
                  fill="#4285f4" />
                <path
                  d="M8.312 16.687c2.25 0 4.125-.75 5.5-2l-2.687-2.062c-.75.5-1.688.812-2.813.812-2.125 0-4-1.437-4.625-3.437H.937v2.125c1.438 2.812 4.25 4.562 7.375 4.562"
                  fill="#34a853" />
                <path d="M3.641 10c-.375-1-.375-2.125 0-3.188V4.687H.891C-.297 7-.297 9.75.891 12.125Z"
                  fill="#fbbc04" />
                <path
                  d="M8.25 3.253c1.187 0 2.312.438 3.187 1.25l2.375-2.375c-1.5-1.375-3.5-2.187-5.5-2.125-3.125 0-6 1.75-7.375 4.563l2.75 2.125c.563-2 2.438-3.438 4.563-3.438"
                  fill="#ea4335" />
              </svg>

              <svg xmlns="http://www.w3.org/2000/svg" width="73.682" height="20" fill="none" overflow="visible">
                <path
                  d="M0 10C0 4.477 4.477 0 10 0s10 4.477 10 10-4.477 10-10 10S0 15.523 0 10m50.609 5.734v-3.632c0-.896-.335-1.343-1.169-1.343-.336 0-.721.049-.945.112v5.311c0 .161-.162.335-.336.335h-1.281c-.162 0-.336-.161-.336-.335V9.975c0-.224.162-.385.336-.448.833-.273 1.679-.447 2.575-.447 2.015 0 3.134 1.057 3.134 3.022v4.129c0 .162-.162.336-.336.336h-.783c-.523 0-.859-.385-.859-.833m5.025-2.177-.049.498c0 .671.447 1.057 1.169 1.057.559 0 1.057-.162 1.617-.448.049 0 .112-.05.161-.05.112 0 .162.05.224.112.05.05.162.224.162.224.112.162.224.386.224.56 0 .274-.162.56-.386.672-.609.335-1.343.497-2.127.497-.895 0-1.617-.224-2.176-.672a2.6 2.6 0 0 1-.896-2.014v-2.177c0-1.729 1.12-2.799 3.023-2.799 1.84 0 2.91 1.008 2.91 2.799v1.343c0 .162-.162.336-.336.336h-3.52Zm-.049-1.231h2.126v-.56c0-.671-.385-1.119-1.057-1.119s-1.057.385-1.057 1.119c-.012 0-.012.56-.012.56m14.241 1.231-.05.498c0 .671.448 1.057 1.169 1.057.56 0 1.057-.162 1.617-.448.05 0 .112-.05.162-.05.112 0 .162.05.224.112.05.05.161.224.161.224.112.162.224.386.224.56 0 .274-.161.56-.385.672-.61.335-1.344.497-2.127.497-.896 0-1.617-.224-2.177-.672a2.6 2.6 0 0 1-.895-2.014v-2.177c0-1.729 1.119-2.799 3.022-2.799 1.841 0 2.911 1.008 2.911 2.799v1.343c0 .162-.162.336-.336.336h-3.52Zm-.05-1.231h2.127v-.56c0-.671-.386-1.119-1.057-1.119-.672 0-1.057.385-1.057 1.119v.56Zm-32.861 4.241h.784c.162 0 .336-.162.336-.336v-4.129c0-1.903-1.008-3.022-2.687-3.022a5 5 0 0 0-1.393.223V7.239a.85.85 0 0 0-.833-.834h-.784c-.161 0-.336.162-.336.336v9.503c0 .161.162.336.336.336h1.281c.162 0 .336-.162.336-.336v-5.261c.274-.112.672-.162.945-.162.834 0 1.17.385 1.17 1.343v3.632c.062.386.398.771.845.771m8.433-4.689v2.065c0 1.729-1.169 2.798-3.134 2.798-1.903 0-3.134-1.057-3.134-2.798v-2.065c0-1.729 1.169-2.798 3.134-2.798s3.134 1.069 3.134 2.798m-1.952 0c0-.672-.386-1.119-1.12-1.119s-1.119.385-1.119 1.119v2.065c0 .671.385 1.057 1.119 1.057s1.12-.386 1.12-1.057Zm-12.463-.945c0 1.778-1.343 3.01-3.122 3.01-.448 0-.833-.05-1.231-.224v2.512c0 .162-.162.336-.336.336h-1.281c-.162 0-.336-.162-.336-.336v-8.88c0-.224.162-.386.336-.448.833-.274 1.679-.448 2.574-.448 2.015 0 3.408 1.232 3.408 3.135-.012 0-.012 1.343-.012 1.343M28.93 9.478c0-.896-.609-1.344-1.455-1.344-.497 0-.833.162-.833.162v3.694c.336.162.497.224.895.224.834 0 1.456-.498 1.456-1.343V9.478Zm38.11 1.455c0 1.791-1.343 3.022-3.135 3.022-.447 0-.833-.05-1.231-.224v2.513c0 .161-.162.336-.336.336h-1.281c-.161 0-.336-.162-.336-.336V7.351c0-.224.162-.386.336-.448.834-.274 1.679-.448 2.575-.448 2.015 0 3.408 1.232 3.408 3.135Zm-2.015-1.455c0-.896-.61-1.344-1.455-1.344-.498 0-.834.162-.834.162v3.694c.336.162.498.224.896.224.833 0 1.455-.498 1.455-1.343V9.478Z"
                  fill="#5f259f" />
                <path
                  d="M14.515 7.418a.74.74 0 0 0-.721-.722H12.45l-3.072-3.52c-.274-.336-.721-.447-1.169-.336l-1.057.336c-.162.05-.224.274-.112.386l3.358 3.184H5.311a.26.26 0 0 0-.274.274v.559c0 .386.336.722.722.722h.783V11c0 2.015 1.058 3.184 2.849 3.184.559 0 1.007-.05 1.567-.274v1.791c0 .498.385.896.895.896h.784c.162 0 .336-.162.336-.336V8.276h1.281a.26.26 0 0 0 .273-.274c-.012-.025-.012-.584-.012-.584m-3.582 4.813c-.336.162-.784.224-1.12.224-.895 0-1.343-.448-1.343-1.455V8.313h2.463Z"
                  fill="#fff" />
              </svg>

              <svg xmlns="http://www.w3.org/2000/svg" width="63.496" height="20" fill="none" overflow="visible">
                <path
                  d="M63.282 5.668a4.24 4.24 0 0 0-3.997-2.835h-.039a4.23 4.23 0 0 0-3.053 1.302 4.23 4.23 0 0 0-3.053-1.302h-.039a4.22 4.22 0 0 0-2.761 1.026v-.325a.626.626 0 0 0-.621-.578h-2.833a.627.627 0 0 0-.627.628v15.385a.63.63 0 0 0 .627.628h2.833a.624.624 0 0 0 .617-.541l-.001-11.045q0-.057.005-.111c.045-.493.406-.898.977-.949h.522c.218.017.427.097.599.232.248.202.39.507.383.828l.011 10.99c0 .348.281.629.627.629h2.833a.626.626 0 0 0 .622-.598l-.001-11.036a1.04 1.04 0 0 1 .461-.885c.145-.093.32-.156.521-.174h.522c.561.028.996.498.983 1.059l.01 10.976a.627.627 0 0 0 .627.628h2.833c.345 0 .626-.28.626-.628V7.167c0-.805-.09-1.147-.214-1.499M44.306 3.203h-1.621V.57a.57.57 0 0 0-.679-.56c-1.797.493-1.437 2.981-4.717 3.193h-.318a.7.7 0 0 0-.138.016h-.002l.002.001a.63.63 0 0 0-.49.609v2.834a.624.624 0 0 0 .628.626h1.71l-.003 12.014c0 .343.277.62.62.62h2.801a.62.62 0 0 0 .618-.62l.002-12.014h1.587c.346 0 .626-.28.626-.626V3.829a.626.626 0 0 0-.626-.626"
                  fill="#54c1f0" />
                <path
                  d="M34.135 3.305h-2.833a.627.627 0 0 0-.625.627V9.79a.666.666 0 0 1-.664.653h-1.186a.666.666 0 0 1-.665-.663l-.01-5.848a.627.627 0 0 0-.627-.627h-2.833a.627.627 0 0 0-.626.627v6.421c0 2.438 1.739 4.178 4.179 4.178 0 0 1.831 0 1.887.01a.66.66 0 0 1 .009 1.31l-.048.01-4.143.014a.627.627 0 0 0-.626.627v2.832c0 .346.28.626.626.626h4.632c2.442 0 4.18-1.738 4.18-4.178V3.932a.627.627 0 0 0-.627-.627M6.558 8.555v1.748a.666.666 0 0 1-.664.665l-1.797.002V7.466h1.797a.664.664 0 0 1 .664.664Zm.249-5.192H.614A.61.61 0 0 0 0 3.977v2.776l.001.016L0 6.808v12.564c0 .341.256.62.573.628H3.46c.345 0 .626-.28.626-.626l.011-4.306h2.71c2.269 0 3.849-1.574 3.849-3.852V7.22c0-2.278-1.58-3.857-3.849-3.857m11.427 11.84v.443a.7.7 0 0 1-.037.195.67.67 0 0 1-.631.428h-1.179c-.368 0-.668-.28-.668-.623v-.535l-.001-.02.001-1.422v-.445l.002-.004c.001-.342.298-.62.666-.62h1.179c.369 0 .668.279.668.624Zm-.45-11.827h-3.932c-.348 0-.629.263-.629.587v1.102l.001.022-.001.024v1.51c0 .342.299.622.666.622h3.744a.654.654 0 0 1 .564.6v.365c-.034.321-.266.556-.548.582h-1.854c-2.465 0-4.222 1.638-4.222 3.938v3.295c0 2.287 1.51 3.914 3.958 3.914h5.138c.922 0 1.67-.699 1.67-1.558V7.628c0-2.607-1.344-4.252-4.555-4.252"
                  fill="#233266" />
              </svg>

              <svg xmlns="http://www.w3.org/2000/svg" width="81.088" height="20" fill="none" overflow="visible">
                <path d="m77.116 0 3.972 7.903-8.354 7.902Z" fill="#008c44" />
                <path d="m74.34 0 3.97 7.903-8.359 7.902Z" fill="#f47920" />
                <path
                  d="M17.831 11.425a2.14 2.14 0 0 1-1.657 1.201H4.952l.903-3.231h11.331c.413.102.735.427.832.842q.024.214.01.429v.061ZM7.616 3.071h11.356c.307.075.57.275.724.552.086.3.106.615.058.924l-.028.103-.111.396c-.301.7-.95 1.187-1.705 1.281H6.71Zm11.83 12.173c.373-.276.643-.668.769-1.115l1.322-4.734q.182-.646-.157-1.085c-.338-.439-.559-.439-.998-.439a2 2 0 0 0 1.246-.449c.369-.27.639-.655.766-1.095l1.317-4.729q.195-.689-.128-1.14-.32-.454-1.009-.454H5.341L.962 15.696H18.2q.658 0 1.246-.449M41.769.019 40.036 6.32H27.601L29.334.019h-3.09L21.94 15.653h3.093l1.728-6.273h12.433l-1.726 6.273h3.093l4.3-15.634Zm3.99 15.684h-3.112L46.975.072h3.113ZM71.733.059 58.818 11.004 54.31 3.515 52.232.059h-.058l-.979 3.552-3.322 12.032h3.093l2.328-8.433 4.931 8.455 8.168-7.484-2.046 7.462h3.092l3.017-10.937L71.74.059Z"
                  fill="#6d6e71" />
                <path
                  d="M.714 18.572h.151q.286 0 .421-.075a.35.35 0 0 0 .177-.245q.045-.189-.045-.267-.091-.076-.394-.076H.873Zm-.247 1.019h.136q.159.003.318-.015a.5.5 0 0 0 .176-.058.45.45 0 0 0 .227-.292.35.35 0 0 0-.005-.195.26.26 0 0 0-.108-.126.5.5 0 0 0-.121-.04l-.19-.013H.643ZM0 19.904l.555-2.313h.618q.263 0 .383.028a.5.5 0 0 1 .195.088q.095.077.126.207a.69.69 0 0 1-.169.593.67.67 0 0 1-.303.171.4.4 0 0 1 .293.184q.088.152.03.386a.83.83 0 0 1-.595.618q-.254.045-.51.038Zm3.014.003.555-2.313h.396l-.204.855h1.203l.204-.855h.401l-.554 2.313h-.399l.27-1.123H3.68l-.27 1.123Zm4.331-.908h.684l-.126-.53a1.2 1.2 0 0 1-.028-.26l-.076.141-.075.121Zm.883.908-.134-.596h-.968l-.429.596h-.416l1.763-2.404.608 2.404Zm2.404-1.327h.071c.148.01.297-.014.436-.068a.38.38 0 0 0 .164-.247q.045-.19-.048-.27-.091-.076-.394-.076h-.07Zm-.096.292-.247 1.035h-.373l.554-2.308h.555q.245 0 .371.027a.5.5 0 0 1 .207.101.4.4 0 0 1 .124.215.8.8 0 0 1-.253.726.83.83 0 0 1-.466.189l.592 1.052h-.451l-.568-1.034Zm3.067.127h.684l-.126-.53-.015-.114-.013-.146-.076.141-.075.121Zm.883.908-.134-.596h-.971l-.426.596h-.419l1.763-2.404.608 2.404Zm2.77-1.993-.48 1.993h-.393l.477-1.993h-.654l.076-.32h1.698l-.076.32Zm3.584 1.993.555-2.313h.396l-.552 2.313Zm2.005 0 .575-2.404 1.219 1.412q.11.135.199.283l.383-1.604h.369l-.575 2.403-1.244-1.437a1.3 1.3 0 0 1-.174-.26l-.386 1.607Zm5.058-1.993-.48 1.993h-.396l.48-1.993h-.656l.078-.32h1.698l-.076.32Zm1.511 1.993.552-2.313h1.367l-.075.32h-.972l-.138.58h.968l-.075.328h-.972l-.179.749h.969l-.081.336Zm3.574-1.327h.07c.149.01.298-.014.437-.068a.38.38 0 0 0 .164-.247q.045-.19-.048-.27-.091-.076-.394-.076h-.07Zm-.096.292-.247 1.035h-.373l.554-2.311h.555q.242 0 .371.028a.5.5 0 0 1 .207.101.4.4 0 0 1 .124.214.81.81 0 0 1-.253.727.84.84 0 0 1-.466.189l.59 1.052h-.449l-.568-1.035Zm2.333 1.035.555-2.313h1.365l-.076.32H36.1l-.138.575h.971l-.081.331h-.968l-.26 1.087Zm3.446-.908h.681l-.121-.53a1.3 1.3 0 0 1-.03-.26q-.068.136-.152.262Zm.883.908-.134-.596h-.971l-.426.596h-.419l1.76-2.404.611 2.404Zm4.013-1.751a1 1 0 0 0-.31-.202 1.11 1.11 0 0 0-1.093.177 1.1 1.1 0 0 0-.401.631q-.089.37.099.615c.186.245.31.245.56.245q.216 0 .428-.076.232-.08.432-.219l-.101.428c-.399.23-.877.276-1.312.126a.86.86 0 0 1-.54-.645 1.3 1.3 0 0 1 .021-.48q.063-.257.209-.479.151-.222.373-.393c.222-.172.311-.197.48-.258q.258-.084.532-.088.216 0 .398.061.184.057.336.179Zm1.11 1.751.552-2.313h1.367l-.075.32h-.972l-.136.58h.969l-.081.328h-.969l-.176.749h.968l-.083.336Zm4.926 0 .555-2.313h1.367l-.076.32h-.971l-.139.575h.972l-.081.331h-.969l-.26 1.087Zm5.206-1.151a.8.8 0 0 0 .013-.332.66.66 0 0 0-.371-.47.8.8 0 0 0-.328-.065 1.16 1.16 0 0 0-.694.252 1.3 1.3 0 0 0-.262.28 1 1 0 0 0-.146.335.8.8 0 0 0-.013.331.63.63 0 0 0 .371.467c.103.046.215.07.328.068a1 1 0 0 0 .358-.068c.36-.133.637-.429.744-.798m.416 0c-.038.168-.11.326-.212.465a1.84 1.84 0 0 1-.867.655 1.5 1.5 0 0 1-.52.094 1.3 1.3 0 0 1-.484-.094 1 1 0 0 1-.361-.262.9.9 0 0 1-.192-.391 1 1 0 0 1 .016-.467q.064-.252.209-.469a1.75 1.75 0 0 1 .868-.656q.257-.087.524-.088c.268 0 .336.028.48.088a.91.91 0 0 1 .552.659q.045.222-.013.466m1.932-.176h.071c.149.01.298-.014.436-.068a.38.38 0 0 0 .164-.247q.046-.19-.048-.27-.093-.076-.393-.076h-.073Zm-.095.292-.248 1.035h-.373l.552-2.308h.555q.242 0 .371.027a.5.5 0 0 1 .207.101.4.4 0 0 1 .124.215.83.83 0 0 1-.253.726.83.83 0 0 1-.466.189l.59 1.052h-.449l-.568-1.034Zm6.674-.108.012-.149.018-.166a1.3 1.3 0 0 1-.189.32L62.644 20l-.343-1.256a1.2 1.2 0 0 1-.051-.295 1.2 1.2 0 0 1-.121.32l-.535 1.138h-.365l1.155-2.412.373 1.463.025.114.033.199q.058-.1.172-.252l.045-.063 1.054-1.461.016 2.412h-.369Zm4.091-.008a.8.8 0 0 0 .013-.332.66.66 0 0 0-.371-.47.8.8 0 0 0-.328-.065 1.16 1.16 0 0 0-.696.252 1.16 1.16 0 0 0-.406.615.8.8 0 0 0-.013.331.62.62 0 0 0 .373.467c.103.046.215.07.328.068q.185-.002.356-.068c.36-.133.637-.429.744-.798m.416 0c-.038.168-.11.326-.212.465a1.76 1.76 0 0 1-1.387.749c-.165 0-.329-.031-.482-.094a.94.94 0 0 1-.555-.653 1 1 0 0 1 .016-.467q.06-.247.209-.469c.151-.222.227-.277.378-.393q.23-.172.49-.263c.259-.09.348-.088.524-.088q.265-.001.48.088a.91.91 0 0 1 .552.659q.045.222-.013.466m1.216 1.151.575-2.404 1.219 1.412q.108.135.196.283l.386-1.604h.369l-.576 2.403-1.243-1.437a1.2 1.2 0 0 1-.177-.26l-.383 1.607Zm3.789 0 .555-2.313h1.364l-.075.32h-.971l-.139.58h.971l-.081.328h-.968l-.177.749h.966l-.078.336Zm3.385 0 .252-1.06-.525-1.253h.419l.328.784.025.081.031.106q.065-.107.151-.197l.711-.774h.399l-1.143 1.256-.252 1.059Z"
                  fill="#414042" />
              </svg>
            </div>
          </div>
        </div>

        <div class="trust-features-card">

          <div class="trust-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="27.27" height="20" fill="none" overflow="visible">
              <g fill="transparent" stroke="#2f9454">
                <path
                  d="M20.545 14.544a2.728 2.728 0 1 1-.001 5.455 2.728 2.728 0 0 1 .001-5.455Zm-14.18 0a2.729 2.729 0 1 1-.004 5.458 2.729 2.729 0 0 1 .004-5.458Z"
                  stroke-miterlimit="10" />
                <path
                  d="M23.452 17.453h3.818v-7.09l-4.363-7.091h-4.909v6h8.181-8.181v8.181H9.817h8.181V0H0v17.453h3.818"
                  stroke-linejoin="round" />
              </g>
            </svg>
            <div class="trust-item-text">
              <span>Best Quality</span>
              <span class="trust-sub">Assured</span>
            </div>
          </div>

          <div class="trust-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" overflow="visible">
              <path
                d="M3.955 16.029 5.245 20l1.995-1.958-.348-2.325 5.878-5.213 2.995 9.03.999-1.56V6.022S21.428 2.266 19.558.43 13.97 3.226 13.97 3.226H2.22l-1.601.987 8.859 2.996-5.22 5.872-2.286-.311L0 14.728Z"
                fill="transparent" stroke="#2f9454" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="trust-item-text">
              <span>Delivery</span>
              <span class="trust-sub">Across India</span>
            </div>
          </div>

          <div class="trust-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" overflow="visible">
              <g fill="transparent" stroke="#2f9454" stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M16.585 5.469H20V2.051v3.418s-2.659-2.791-4.878-4.102C12.903.057 11.123-.001 10.244 0 8.487.002 2.468.531 0 7.968m3.415 6.563H0v3.418-3.418s2.659 2.791 4.878 4.102c2.219 1.31 3.999 1.368 4.878 1.367 1.757-.002 7.776-.531 10.244-7.968" />
                <path
                  d="M6.341 7.52v4.557l3.903 1.595L14 12V7.5l-3.756-2.031L14 7.5l-3.756 1.615v4.557-4.557zl3.903-2.051Z" />
              </g>
            </svg>
            <div class="trust-item-text">
              <span>Easy Returns &</span>
              <span class="trust-sub">Size Exchange</span>
            </div>
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
        <p style="font-size:14px;color:var(--text-2);line-height:1.65;">Send payment proof to our employee and
          we will
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
    var BOOT_TOTAL = <?= $bootTotal ?>;
    var ADDONS = <?= $addons ?>;
    var TOTAL = <?= $grandTotal ?>;
    var ADVANCE = <?= $advance ?>;
    var ON_DEL = <?= $onDel ?>;

    var upiId = '';
    var upiNumber = '';
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
          upiNumber = d.upiNumber || ''; // Captures value from backend JSON payload safely
          whatsappLink = d.whatsappLink || '';
          instagramLink = d.instagramLink || '';

          // Push both values to their respective HTML elements dynamically
          document.getElementById('upiIdText').textContent = upiId || 'N/A';
          document.getElementById('upiNumberText').textContent = upiNumber || 'N/A';

          if (d.qrDataUrl) {
            var img = document.getElementById('qrImg');
            img.src = d.qrDataUrl;
            img.style.display = 'block';
            document.getElementById('qrPlaceholder').style.display = 'none';
          }

          var btn = document.getElementById('channelBtn');
          var btnt = document.getElementById('channelBtnText');
          var iconSvg = document.getElementById('channelIcon');

          if (CHANNEL === 'whatsapp' && whatsappLink) {
            btn.href = whatsappLink;
            btn.className = 'channel-btn whatsapp';
            btnt.textContent = 'Contact us on WhatsApp';
            iconSvg.setAttribute('viewBox', '0 0 24 24');
            iconSvg.innerHTML = '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.534 5.857L0 24l6.335-1.51A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.007-1.369l-.36-.213-3.724.888.925-3.63-.234-.374A9.818 9.818 0 1 1 12 21.818z"/>';
          } else if (CHANNEL === 'instagram' && instagramLink) {
            btn.href = instagramLink;
            btn.className = 'channel-btn instagram';
            btnt.textContent = 'Contact us on Instagram';
            iconSvg.setAttribute('viewBox', '0 0 24 24');
            iconSvg.innerHTML = '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>';
          }
        })
        .catch(function () {
          document.getElementById('upiIdText').textContent = 'Failed to load';
          document.getElementById('upiNumberText').textContent = 'Failed to load';
        });
    }

    function setPayMode(mode) {
      payMode = mode;
      document.getElementById('toggleAdvance').classList.toggle('active', mode === 'advance');
      document.getElementById('toggleFull').classList.toggle('active', mode === 'full');

      // Formats the number with the ₹ symbol
      var fmt = function (n) { return '\u20B9' + n.toLocaleString('en-IN'); };

      var labelEl = document.getElementById('payNowLabel');
      var subEl = document.getElementById('payNowSubtitle');

      if (mode === 'full') {
        document.getElementById('advanceRow').style.display = 'none';
        document.getElementById('onDelRow').style.display = 'none';
        document.getElementById('totalAmt').textContent = fmt(TOTAL);

        // Standardized font sizes to ensure perfect alignment with billing elements on mobile devices
        labelEl.innerHTML = 'Pay <span style="color: #2ca659; font-weight: 700;">' + fmt(TOTAL) + '</span> to Confirm Order';
        if (subEl) {
          subEl.textContent = 'Secure your order. Pay ' + fmt(TOTAL) + ' now to confirm your order.';
        }
      } else {
        document.getElementById('advanceRow').style.display = '';
        document.getElementById('onDelRow').style.display = '';
        document.getElementById('advanceAmt').textContent = fmt(ADVANCE);
        document.getElementById('onDelAmt').textContent = fmt(ON_DEL);
        document.getElementById('totalAmt').textContent = fmt(TOTAL);

        // Standardized font sizes for advance mode layout state
        labelEl.innerHTML = 'Pay <span style="color: #2ca659; font-weight: 700;">' + fmt(ADVANCE) + '</span> to Confirm Order';
        if (subEl) {
          subEl.textContent = 'Secure your order. Pay the remaining ' + fmt(ON_DEL) + ' on delivery.';
        }
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

    function copyUpiNumber(btn) {
      if (!upiNumber) return;
      navigator.clipboard.writeText(upiNumber).then(function () {
        if (btn) {
          btn.textContent = 'Copied!';
          setTimeout(function () { btn.textContent = 'Copy'; }, 2000);
        }
      });
    }

    function submitOrder() {
      var name = document.getElementById('custName').value.trim();
      var wa = document.getElementById('custWa').value.trim();

      if (!name || !wa) {
        showErr('Please fill in your name and WhatsApp number.');
        return;
      }
      hideErr();

      var btn = document.getElementById('submitBtn');
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
            // Direct to Success Screen immediately on DB confirmation
            showSuccess();
          } else {
            showErr(d.error || 'Something went wrong. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Place Order';
          }
        })
        .catch(function () {
          showErr('Network error. Please check your connection and try again.');
          btn.disabled = false;
          btn.textContent = 'Place Order';
        });
    }

    function showSuccess() {
      document.getElementById('formContent').style.display = 'none';
      // Removed the delivery screen toggle since it no longer exists
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