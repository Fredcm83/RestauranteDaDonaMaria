<?php
function open_now(DateTime $dt=null){$dt=$dt?:new DateTime('now');$d=(int)$dt->format('w');$h=(int)$dt->format('G')+((int)$dt->format('i'))/60.0;return ($d>=1&&$d<=6&&$h>=9&&$h<18);}
function money($v){return '$'.number_format((float)$v,2);}
function slugify($s){ if(function_exists('iconv')){$s = @iconv('UTF-8','ASCII//TRANSLIT',$s);} $s = preg_replace('~[^\w]+~','-',$s); $s = trim($s,'-'); $s = preg_replace('~-+~','-',$s); return strtolower($s?:'image'); }

/**
 * Render a single <img> for our stored 800px variant.
 * - Accepts absolute or relative URLs
 * - If URL doesn't look like our pattern, just prints a normal <img>
 */
function render_responsive_img($url, $alt){
  $alt = htmlspecialchars($alt ?: '');
  $u = trim((string)$url);
  if ($u === '') $u = BASE_PATH . '/assets/images/cover.svg';

  // If user pasted non-uploads URL, just output it directly
  if (!preg_match('~/(uploads/\d{4}/\d{2}/[a-z0-9-]+)-(\d+)\.(jpe?g|png|webp)$~i', $u, $m)) {
    $safe = htmlspecialchars($u, ENT_QUOTES, 'UTF-8');
    return '<img src="'.$safe.'" decoding="async" loading="lazy" alt="'.$alt.'">';
  }

  // Force to our 800px jpg variant (stop 404s on other sizes)
  $base = substr($u, 0, strrpos($u, '-')); // up to the last '-<width>'
  $src  = $base . '-800.jpg';
  $safe = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
  return '<img src="'.$safe.'" decoding="async" loading="lazy" alt="'.$alt.'">';
}
