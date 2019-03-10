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
    "image/jpeg" => [
      "ext" => "jpg",
      "type" => "image"
    ],
    "image/png" => [
      "ext" => "png",
      "type" => "image"
    ],
    "audio/mp3" => [
      "ext" => "mp3",
      "type" => "audio"
    ],
    "audio/wav" => [
      "ext" => "wav",
      "type" => "audio"
    ]
  ];

  if (!isset($list[$mime]) || $list[$mime]["type"] !== $allow_file_type) $err["error"] = "ファイルタイプが不正です";
  if (isset($err["error"])) return $err;

  return ["success" => true, "mime" => $mime, "ext" => $list[$mime]["ext"]];
}

function initS3() {
  global $env;
  return new \Aws\S3\S3Client([
    'version' => 'latest',
    'endpoint' => $env["storage"]["endpoint"],
    'region' => $env["storage"]["region"],
    'credentials' => new Aws\Credentials\Credentials($env["storage"]["key"], $env["storage"]["secret"]),
    'http' => [
      'verify' => !$env["is_debug"]
    ]
  ]);
}

function uploadFlie($data, $file_type, $user_id) {
  global $env;

  $check = checkFileInfo($data);
  if (!$check["success"]) return ["success" => false, "error" => $check["error"]];

  switch ($file_type) {
    case "item_sound":
      $type = "audio";
      break;
    case "custom_emoji":
      $type = "image";
      break;
    default:
      $type = "unknown";
      break;
  }
  $mime = checkMime($data, $type);
  if (!$mime["success"]) return ["success" => false, "error" => $mime["error"]];

  $s3 = initS3();
  try {
    $id = generateHash();
    $file_name = $id . "." . $data["ext"];
    $result = $s3->putObject([
      'Bucket' => $env["storage"]["bucket"],
      'Key'    => $file_type . "/" . $file_name,
      'Body'   => file_get_contents($data['tmp_name']),
      'ContentType' => $mime["mime"],
      'ACL'    => 'public-read',
      'Metadata' => [
        'CRS-Uploaded-By' => $user_id
      ]
    ]);

    if ($result["ObjectURL"]) {
      return ["success" => true, "file_name" => $file_name];
    } else {
      return ["success" => false, "error" => "データベースエラー"];
    }
  } catch (\Aws\S3\Exception\S3Exception $e) {
    if ($env["is_debug"]) {
      echo $e->getMessage() . PHP_EOL;
    }
    return ["success" => false, "error" => "アップロードエラー"];
  }
}

function deleteFile($file_name, $file_type) {
  global $env;

  $s3 = initS3();
  try {
    $result = $s3->deleteObject([
      'Bucket' => $env["storage"]["bucket"],
      'Key'    => $file_type . "/" . $file_name,
    ]);
  } catch (\Aws\S3\Exception\S3Exception $e) {
    if ($env["is_debug"]) {
      echo $e->getMessage() . PHP_EOL;
    }
    return false;
  }

  return true;
}

