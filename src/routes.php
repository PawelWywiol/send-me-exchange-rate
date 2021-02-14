<?php

defined('PTW') or die("476f7420796f7521205468657265206973206e6f7468696e67206865726521");

use Siler\Route;
use Siler\Http\Response;

use PTW\App;

Route\get('/data', function (array $routeParams) {
  $scrap = App\Scrap();
  Response\json($scrap);
});

Route\get('/text', function (array $routeParams) {
  $scrap = App\Scrap();
  if (empty($scrap['error'] ?? [])) {
    $html = App\Template($scrap);
    Response\html($html);
  }
});

Route\get('/send/{profile}?', function (array $routeParams) {
  $scrap = App\Scrap();
  if (empty($scrap['error'] ?? [])) {
    if (isset($routeParams['profile'])) {
      $profile_id = $routeParams['profile'] ?? 0;
      $profile = explode(';', $_ENV['PROFILE_' . $profile_id] ?? $_ENV['PROFILE'] ?? "");

      if (!empty($profile) && !empty($profile[0])) {
        App\Send($scrap, ...$profile);
      }
    } else {
      $profile_id = 0;
      while (!empty($_ENV['PROFILE_' . $profile_id] ?? "") || ($profile_id === 0 && !empty($_ENV['PROFILE'] ?? ""))) {
        $profile = explode(';', $_ENV['PROFILE_' . $profile_id] ?? $_ENV['PROFILE'] ?? "");
        if (!empty($profile) && !empty($profile[0])) {
          App\Send($scrap, ...$profile);
        }
        $profile_id += 1;
      }
    }
  }
});
