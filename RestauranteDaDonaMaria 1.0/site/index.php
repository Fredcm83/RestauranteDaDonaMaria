<?php
require_once __DIR__ . '/db/db.php'; require_once __DIR__ . '/includes/helpers.php'; require_once __DIR__ . '/db/config.php';
$page_title='Restaurante da Dona Maria'; include __DIR__ . '/includes/header.php';
$pdo=db(); $items=$pdo->query("SELECT id,name,price,img,description FROM menu_items WHERE available=1 AND featured=1 ORDER BY name ASC LIMIT 6")->fetchAll();
?>
<?php
// Optional background image support
$hero_bg = '';
if (defined('HERO_BG_URL') && HERO_BG_URL) {
  $hero_bg = HERO_BG_URL; // set this in config.php if you prefer
} elseif (file_exists(__DIR__ . '/assets/images/hero.jpg')) {
  // drop a file at /assets/images/hero.jpg and it will be used
  $hero_bg = BASE_PATH . '/assets/images/hero.jpg';
}
?>
<section class="hero <?php echo $hero_bg ? 'has-bg' : ''; ?>" <?php if($hero_bg){ ?>
  style="--hero-bg: url('<?php echo htmlspecialchars($hero_bg, ENT_QUOTES, 'UTF-8'); ?>');"
<?php } ?>>
  <div class="container kv">
    <div class="hero-copy">
      <span class="badge" id="hours-badge">Checking hours…</span>
      <h1>Homestyle Brazilian Flavors</h1>
      <p class="sub">Fresh, comforting dishes from our kitchen to your door. Order via form and confirm on WhatsApp.</p>
      <div class="cta">
        <a class="btn" href="/menu.php">Browse the Menu</a>
        <a class="btn secondary" href="/contact.php">Contact</a>
      </div>
    </div>

    <!-- Optional illustration stays, but collapses on mobile -->
    <div class="hero-art">
    </div>
  </div>
</section>

<div class="container"><h2>Popular Dishes</h2><div class="grid cards">
<?php foreach($items as $it): ?><article class="card">
  <?php echo render_responsive_img($it['img'], $it['name']); ?>
  <div class="pad"><h3><?php echo htmlspecialchars($it['name']); ?></h3>
    <p class="small"><?php echo htmlspecialchars($it['description'] ?: ''); ?></p>
    <div class="small"><?php echo money($it['price']); ?></div>

  
<a class="btn" href="#"
   data-order
   data-name="<?php echo htmlspecialchars($it['name']); ?>"
   data-price="<?php echo htmlspecialchars((string)$it['price']); ?>">
  Order
</a>


  </div></article>
<?php endforeach; ?></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
