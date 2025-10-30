<?php
// order.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db/config.php';
require_once __DIR__ . '/includes/helpers.php'; // for money() if you have it

function money_safe($n) {
  if (function_exists('money')) return money($n);
  return '$' . number_format((float)$n, 2);
}

// Read payload (JSON with items/total) built by the modal JS
$payloadJson = $_POST['payload'] ?? '';
$data = json_decode($payloadJson, true);

// Fallback: if someone posts single fields directly
if (!is_array($data)) {
  $name  = trim($_POST['item']  ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $qty   = (int)($_POST['qty']   ?? 1);
  $items = [];
  if ($name !== '') {
    $items[] = [
      'name'       => $name,
      'price'      => round($price, 2),
      'qty'        => max(1, $qty),
      'line_total' => round($price * max(1, $qty), 2),
    ];
  }
  $data = [
    'items' => $items,
    'total' => array_reduce($items, fn($s,$i)=>$s + $i['line_total'], 0.0),
  ];
}

// Normalize and re-calc total on the server (don’t trust the browser)
$items = [];
$total = 0.0;
if (isset($data['items']) && is_array($data['items'])) {
  foreach ($data['items'] as $it) {
    $name  = trim((string)($it['name']  ?? ''));
    $price = (float)($it['price'] ?? 0);
    $qty   = (int)  ($it['qty']   ?? 1);
    if ($name === '') continue;
    if ($qty < 1) $qty = 1;
    $line_total = round($price * $qty, 2);
    $items[] = [
      'name'       => $name,
      'price'      => round($price, 2),
      'qty'        => $qty,
      'line_total' => $line_total,
    ];
    $total += $line_total;
  }
}
$total = round($total, 2);

// Customer info
$customer = [
  'name'    => trim($_POST['name']    ?? ''),
  'phone'   => trim($_POST['phone']   ?? ''),
  'email'   => trim($_POST['email']   ?? ''),
  'address' => trim($_POST['address'] ?? ''),
  'time'    => trim($_POST['time']    ?? ''),
  'notes'   => trim($_POST['notes']   ?? ''),
];

// Build email
$lines = [];
$lines[] = 'Order Summary';
$lines[] = str_repeat('=', 40);
if ($items) {
  foreach ($items as $it) {
    $lines[] = sprintf(
      '%s  x%d  @ %s  = %s',
      $it['name'],
      $it['qty'],
      money_safe($it['price']),
      money_safe($it['line_total'])
    );
  }
} else {
  $lines[] = '(No items received)';
}
$lines[] = str_repeat('-', 40);
$lines[] = 'Total: ' . money_safe($total);
$lines[] = '';
$lines[] = 'Customer: ' . ($customer['name'] ?: '—');
$lines[] = 'Phone: '    . ($customer['phone'] ?: '—');
$lines[] = 'Email: '    . ($customer['email'] ?: '—');
$lines[] = 'Address: '  . ($customer['address'] ?: '—');
$lines[] = 'Preferred time: ' . ($customer['time'] ?: '—');
$lines[] = 'Notes: '    . ($customer['notes'] ?: '—');

$body = implode("\n", $lines);

// Send email
$to      = defined('ORDER_TO_EMAIL') ? ORDER_TO_EMAIL : (getenv('ORDER_TO_EMAIL') ?: 'webmaster@localhost');
$from    = 'orders@' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
$subject = 'New order';

$headers = [];
$headers[] = 'From: ' . $from;
if ($customer['email'] !== '') {
  $headers[] = 'Reply-To: ' . $customer['email'];
}
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

@mail($to, $subject, $body, implode("\r\n", $headers));

// Optional: build WhatsApp message
$waNumber = defined('WHATSAPP_E164') ? WHATSAPP_E164 : '';
if ($waNumber) {
  $waText = rawurlencode($body);
  $waUrl  = "https://wa.me/" . preg_replace('/\D+/', '', $waNumber) . "?text={$waText}";
  header('Location: ' . $waUrl);
  exit;
}

// Fallback thank-you page
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order received</title>
<link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_PATH, ENT_QUOTES); ?>/assets/css/styles.css">
</head><body>
  <div class="container" style="max-width:720px;margin:2rem auto;">
    <h1>Thanks! We got your order.</h1>
    <pre style="background:#fafafa;border:1px solid #eee;padding:1rem;border-radius:12px;white-space:pre-wrap;"><?php echo htmlspecialchars($body, ENT_QUOTES); ?></pre>
    <p class="small">We’ll confirm by phone or WhatsApp.</p>
    <p><a class="btn" href="<?php echo htmlspecialchars(BASE_PATH, ENT_QUOTES); ?>/index.php">Back to site</a></p>
  </div>
</body></html>
