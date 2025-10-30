<?php require_once __DIR__ . '/db/config.php'; $page_title='Contact • Restaurante da Dona Maria'; include __DIR__ . '/includes/header.php'; ?>
<div class="container"><h1>Contact</h1>
<p>We’re online only. Reach us by phone, email, or WhatsApp.</p>
<p><strong>Phone:</strong> <a href="tel:+12019237409">+1 201-923-7409</a><br/>
<strong>Email:</strong> <a href="mailto:fcm030383@gmail.com">fcm030383@gmail.com</a><br/>
<strong>WhatsApp:</strong> <a id="waLink" href="https://wa.me/12019237409" target="_blank" rel="noopener">Open chat</a></p>
<h2>General message</h2>
<form onsubmit="event.preventDefault(); location.href='mailto:fcm030383@gmail.com?subject=' + encodeURIComponent('Contact from website') + '&body=' + encodeURIComponent(document.getElementById('msg').value);">
  <label for="msg" class="hidden">Your message</label>
  <textarea id="msg" rows="6" placeholder="Write your message here…"></textarea><br/>
  <button class="btn">Send via Email</button>
</form></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
