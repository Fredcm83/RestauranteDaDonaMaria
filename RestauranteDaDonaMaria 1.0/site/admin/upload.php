<?php
// Single-variant uploader: always save -800.jpg and return its URL
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../includes/helpers.php'; // for slugify()
header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code=400){
  http_response_code($code);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Invalid method', 405);
if (!isset($_FILES['file'])) fail('No file uploaded');

$err = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
if ($err !== UPLOAD_ERR_OK) fail('Upload error code '.$err);

$name = $_FILES['file']['name'] ?? 'image';
$tmp  = $_FILES['file']['tmp_name'] ?? null;
if (!$tmp || !is_uploaded_file($tmp)) fail('Invalid upload');

$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp','gif'];
if (!in_array($ext, $allowed, true)) fail('Unsupported file type');

// Figure out filesystem + URL paths without relying on extra constants
$projectRoot = dirname(__DIR__); // /maria/site
$baseUrl     = rtrim(defined('BASE_PATH') ? BASE_PATH : '', '/'); // e.g. /maria/site
$y = date('Y'); $m = date('m');

$uploadsDir = $projectRoot . '/uploads/' . $y . '/' . $m;
if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0755, true)) {
  fail('Failed to create upload directory');
}

// File names
$rootName   = slugify(pathinfo($name, PATHINFO_FILENAME)).'--'.substr(md5(uniqid('', true)),0,12);
$target800  = $uploadsDir . '/' . $rootName . '-800.jpg';
$url800     = $baseUrl . '/uploads/' . $y . '/' . $m . '/' . $rootName . '-800.jpg';

// Process to 800 on the long side, save as JPEG q=82
try {
  if (extension_loaded('gd')) {
    $data = @file_get_contents($tmp);
    if ($data === false) fail('Failed to read upload');
    $src  = @imagecreatefromstring($data);
    if (!$src) throw new Exception('GD failed to decode');

    $w = imagesx($src); $h = imagesy($src);
    if ($w <= 0 || $h <= 0) throw new Exception('Invalid image dimensions');

    if ($w >= $h) { $newW = 800; $newH = (int)round($h * (800 / $w)); }
    else          { $newH = 800; $newW = (int)round($w * (800 / $h)); }

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, false);
    imagecopyresampled($dst, $src, 0,0,0,0, $newW,$newH, $w,$h);

    if (!@imagejpeg($dst, $target800, 82)) throw new Exception('Failed to save jpg');
    imagedestroy($dst); imagedestroy($src);

  } elseif (class_exists('Imagick')) {
    $img = new Imagick($tmp);
    $img->setImageFormat('jpeg');
    $img->setImageCompression(Imagick::COMPRESSION_JPEG);
    $img->setImageCompressionQuality(82);
    $img->resizeImage(800, 800, Imagick::FILTER_LANCZOS, 1, true);
    if (!$img->writeImage($target800)) throw new Exception('Failed to save jpg');
    $img->clear(); $img->destroy();

  } else {
    fail('No image library available (GD/Imagick)');
  }

  echo json_encode(['ok'=>true,'url'=>$url800], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  exit;

} catch (Throwable $e) {
  // Last-ditch: copy original to -800.jpg so the UI doesn’t break
  if (!@copy($tmp, $target800)) {
    fail('Processing failed: '.$e->getMessage(), 500);
  }
  echo json_encode(['ok'=>true,'url'=>$url800,'note'=>'stored-original-as-800'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  exit;
}
