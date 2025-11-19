<?php
define('NETLIFY', true);

$projectDir = '/tmp/forgotten_books';
if (!file_exists($projectDir)) {
  copy_dir(__DIR__ . '/..', $projectDir);
}
chdir($projectDir);

$port = getenv('PORT') ?: 8000;
passthru("php -S 0.0.0.0:$port -t .");

function copy_dir($src, $dst) {
  $dir = opendir($src);
  mkdir($dst);
  while($file = readdir($dir)) {
    if ($file != '.' && $file != '..') {
      if (is_dir($src . '/' . $file)) {
        copy_dir($src . '/' . $file, $dst . '/' . $file);
      } else {
        copy($src . '/' . $file, $dst . '/' . $file);
      }
    }
  }
  closedir($dir);
}
?>