<?php

namespace PTW\App;

defined('PTW') or die("476f7420796f7521205468657265206973206e6f7468696e67206865726521");

use Siler\IO;
use Siler\Twig;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function Scrap()
{
  $result = [
    'error' => [],
  ];
  $url = $_ENV['COMPONENT_URL'] ?? "";
  if (!empty($url)) {
    $html = IO\fetch($url)['response'] ?? "";
    if (!empty($html)) {
      $match = [];
      preg_match_all('/<span[^>]*>(.*?)<\/span>/', $html, $match);
      if (!empty($match[1] ?? []) && count($match[1]) > 6) {
        $result['rate'] = [];
        $result['symbols'] = [];
        for ($i = 0; $i < count($match[1]) - 6; $i++) {
          $name = $match[1][$i + 0];
          $symbol = explode(" ", $match[1][$i + 1] ?? "");
          $buy = ($match[1][$i + 2]);
          $sell = ($match[1][$i + 3]);
          $spread = ($match[1][$i + 4]);
          $mean = ($match[1][$i + 5]);

          if (
            count($symbol) === 2 &&
            $symbol[0] . " " === "1 " &&
            is_string($symbol[1]) &&
            strlen($symbol[1]) === 3
          ) {
            $result['rate'][strtolower($symbol[1])] = [
              'symbol' => $symbol[1],
              'name' => $name,
              'buy' => $buy,
              'sell' => $sell,
              'spread' => $spread,
              'mean' => $mean,
            ];
            $result['symbols'][] = $symbol[1];
          }
        }

        $match = [];
        preg_match_all('/<b[^>]*>(.*?)<\/b>/', $html, $match);

        if (count($match[1] ?? []) === 2) {
          $result['table'] = trim($match[1][0]);
          $result['date'] = trim($match[1][1]);
        }

        $match = [];
        preg_match_all('/<a[^>]*>(.*?)<\/a>/', $html, $match);

        if (count($match[1] ?? []) > 0) {
          $time = explode(':', $match[1][count($match[1] ?? []) - 1]);
          if (count($time) === 2) {
            $result['time'] = $match[1][count($match[1] ?? []) - 1];
          }
        }
      } else {
        $result['error'][] = 'No data match';
      }
    } else {
      $result['error'][] = 'Empty html';
    }
  } else {
    $result['error'][] = 'Empty url';
  }

  return $result;
}

function Template($data, $name = 'email.twig')
{
  Twig\init(dirname(__DIR__, 1) . '/templates/');
  return Twig\render($name, $data);
}

function Send($data, $addresses, $symbols = 'USD,EUR', $template = 'email.twig')
{
  $mail = new PHPMailer(true);

  if (empty($data['error'] ?? []) && !empty($addresses)) {
    $data['symbols'] = explode(',', $symbols);

    $body = Template($data, $template);

    $subject = $_ENV['EMAIL_SUBJECT'] ?? "Aktualny kurs walut [date], [time]";

    $subject = str_replace("[date]", $data['date'], $subject);
    $subject = str_replace("[time]", $data['time'], $subject);

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_EMAIL'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
    $mail->Port       = $_ENV['SMTP_PORT'];
    $mail->setFrom($_ENV['SMTP_EMAIL'],   $_ENV['SMTP_NAME']);
    $mail->isHTML(true);

    $mail->Subject = $subject;
    $mail->Body    = $body;

    $parsed_addresses = $mail->parseAddresses($addresses);
    foreach ($parsed_addresses as $m) {
      if (isset($m['address']) && !empty($m['address'])) {
        $mail->addAddress($m['address'], $m['name'] ?? $m['address']);
      }
    }

    $mail->send();

    return true;
  }

  return false;
}
