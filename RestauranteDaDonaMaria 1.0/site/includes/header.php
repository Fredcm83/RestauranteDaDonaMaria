<?php if(!isset($page_title)) $page_title='Restaurante da Dona Maria'; ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>

<?php
  $heroRel  = '/assets/images/hero-800.jpg';
  $heroFile = __DIR__ . '/../assets/images/hero-800.jpg';
  $heroVer  = @filemtime($heroFile) ?: time();
?>
<link
  rel="preload"
  as="image"
  href="<?php echo BASE_PATH . $heroRel . '?v=' . $heroVer; ?>"
  fetchpriority="high">



<title><?php echo htmlspecialchars($page_title); ?></title>
<?php $css = BASE_PATH . '/assets/css/styles.css?v=' . @filemtime(__DIR__ . '/../assets/css/styles.css'); ?>
<link rel="stylesheet" href="<?php echo $css; ?>"/></head><body>
<header aria-label="Site header"><div class="container nav">
<a class="logo" href="<?php echo BASE_PATH; ?>/index.php"><span class="logo-mark">DM</span> <span>Restaurante da Dona Maria</span></a>
<nav aria-label="Primary"><ul>
  <li><a href="<?php echo BASE_PATH; ?>/index.php">Home</a></li>
  <li><a href="<?php echo BASE_PATH; ?>/menu.php">Menu</a></li>
  <li><a href="<?php echo BASE_PATH; ?>/about.php">About</a></li>
  <li><a href="<?php echo BASE_PATH; ?>/contact.php">Contact</a></li>
  <li><a href="<?php echo BASE_PATH; ?>/admin/">Admin</a></li>
</ul></nav></div></header>
