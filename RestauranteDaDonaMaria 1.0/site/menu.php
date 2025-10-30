<?php
require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/db/config.php';

$page_title = 'Menu • Restaurante da Dona Maria';
include __DIR__ . '/includes/header.php';

$pdo   = db();
$items = $pdo->query("
  SELECT id, name, price, img, tags, description
  FROM menu_items
  WHERE available = 1
  ORDER BY featured DESC, name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container"><h1>Menu</h1>
  <div id="results" class="grid cards">
    <?php foreach ($items as $it): ?>
      <?php
        $name = (string)$it['name'];
        $desc = (string)($it['description'] ?? '');
        $price = (float)$it['price'];
        $tags  = array_filter(array_map('trim', explode(',', (string)($it['tags'] ?? ''))));
      ?>
      <article class="card" data-tags="<?php echo htmlspecialchars(implode(' ', $tags)); ?>">
        <div class="media">
          <?php echo render_responsive_img($it['img'], $name); ?>
        </div>

        <div class="pad">
          <h3><?php echo htmlspecialchars($name); ?></h3>
          <p class="small"><?php echo htmlspecialchars($desc); ?></p>
          <div class="small"><?php echo money($price); ?></div>

          <?php if (!empty($tags)): ?>
            <div>
              <?php foreach ($tags as $t): ?>
                <span class="badge"><?php echo htmlspecialchars($t); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

<a class="btn" href="#"
   data-order
   data-name="<?php echo htmlspecialchars($it['name']); ?>"
   data-price="<?php echo htmlspecialchars((string)$it['price']); ?>">
  Order
</a>

      </article>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
