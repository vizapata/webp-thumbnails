<form action='options.php' method='post'>

  <h2>Webp Thumbnails</h2>

  <?php
  settings_fields('webpThumbnailsConfigPage');
  do_settings_sections('webpThumbnailsConfigPage');
  submit_button();
  ?>

</form>