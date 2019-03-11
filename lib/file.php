<?php
function checkFileInfo($data) {
  $err = ["success" => false];

  if (!isset($data['error']) || !is_int($data['error'])) {
    $err["error"] = "パラメータが不正です";
    return $err;
  }

  $max_filesize = ini_get('upload_max_filesize');
  switch ($data['error']) {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      $err["error"] = "ファイルが大きすぎます, {$max_filesize}以内にしてください";
      break;
    case UPLOAD_ERR_PARTIAL:
      $err["error"] = "ファイルのアップロードが中断されました";
      break;
    case UPLOAD_ERR_NO_FILE:
      $err["error"] = "ファイルがありません";
      break;
    case UPLOAD_ERR_NO_TMP_DIR:
    case UPLOAD_ERR_CANT_WRITE:
    case UPLOAD_ERR_EXTENSION:
      $err["error"] = "サーバー側に問題がありファイルの書き込みに失敗しました, 管理者にお問い合わせください。";
      break;
    default:
      $err["error"] = "不明なエラー";
      break;
  }
  if (isset($err["error"])) return $err;

  return ["success" => true];
}

function checkMime($data, $allow_file_type) {
  $err = ["success" => false];

  if (!isset($data['error']) || !is_int($data['error'])) {
    $err["error"] = "パラメータが不正です";
    return $err;
  }

  $mime = mime_content_type($data['tmp_name']);
  $list = [
    "image/pjpeg" => "image/jpeg",
    "image/jpeg" => [
      "ext" => "jpg",
      "type" => "image"
    ],
    "image/png" => [
      "ext" => "png",
      "type" => "image"
    ],
    "audio/mpeg3" => "audio/mp3",
    "audio/mpeg" => "audio/mp3",
    "audio/x-mpeg-3" => "audio/mp3",
    "audio/mp3" => [
      "ext" => "mp3",
      "type" => "audio"
    ],
    "audio/x-wav" => "audio/wav",
    "audio/wav" => [
      "ext" => "wav",
      "type" => "audio"
    ]
  ];

  if (isset($list[$mime]) && is_string($list[$mime])) $mime = $list[$mime];
  if (!isset($list[$mime]) || $list[$mime]["type"] !== $allow_file_type) $err["error"] = "ファイルタイプが不正です";
  if (isset($err["error"])) return $err;

  return ["success" => true, "mime" => $mime, "ext" => $list[$mime]["ext"]];
}

function initStorage() {
  global $env;

  if ($env["storage"]["type"] === "s3") {
    $client = new \Aws\S3\S3Client([
      'version' => 'latest',
      'endpoint' => $env["storage"]["endpoint"],
      'region' => $env["storage"]["region"],
      'credentials' => new Aws\Credentials\Credentials($env["storage"]["key"], $env["storage"]["secret"]),
      'http' => [
        'verify' => !$env["is_testing"]
      ]
    ]);

    $adapter = new League\Flysystem\AwsS3v3\AwsS3Adapter($client, $env["storage"]["bucket"]);
  } else {
    $adapter = new League\Flysystem\Adapter\Local(__DIR__.'/../public/upload/');
  }

  return new League\Flysystem\Filesystem($adapter);
}

function uploadFlie($data, $file_type) {
  global $env;

  $check = checkFileInfo($data);
  if (!$check["success"]) return ["success" => false, "error" => $check["error"]];

  switch ($file_type) {
    case "voice":
      $type = "audio";
      break;
    case "emoji":
      $type = "image";
      break;
    default:
      $type = "unknown";
      break;
  }
  $mime = checkMime($data, $type);
  if (!$mime["success"]) return ["success" => false, "error" => $mime["error"]];

  $storage = initStorage();
  try {
    $id = generateHash();
    $file_name = $id . "." . $mime["ext"];

    /*
    $stream = fopen($data['tmp_name'], 'r');
    $result = $storage->writeStream($file_type . "/" . $file_name, $stream);
    fclose($stream);
    */

    $result = $storage->write($file_type . "/" . $file_name, file_get_contents($data['tmp_name']));

    return ["success" => $result, "file_name" => $file_name];
  } catch (\League\Flysystem\FileExistsException $e) {
    if ($env["is_testing"]) {
      echo $e;
    }
    return ["success" => false, "error" => "アップロードエラー"];
  }
}

function deleteFile($file_name, $file_type) {
  global $env;

  $path = $file_type . "/" . $file_name;
  $storage = initStorage();
  try {
    $result = true;
    if ($storage->has($path)) $result = $storage->delete($path);
  } catch (\League\Flysystem\FileExistsException $e) {
    if ($env["is_testing"]) {
      echo $e;
    }
    return false;
  }

  return $result;
}

