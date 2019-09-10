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
    if (isset($err["error"])) {
        return $err;
    }

    return ["success" => true];
}

function checkMime($data, $allow_file_type) {
    $err = ["success" => false];

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

    if (isset($list[$mime]) && is_string($list[$mime])) {
        $mime = $list[$mime];
    }
    if (!isset($list[$mime]) || $list[$mime]["type"] !== $allow_file_type) {
        $err["error"] = "ファイルタイプが不正です";
    }
    if (isset($err["error"])) {
        return $err;
    }

    return ["success" => true, "mime" => $mime, "ext" => $list[$mime]["ext"]];
}

function resizeImage($data) {
    $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
    $img = $manager->make($data['tmp_name']);
    $width = $img->width();
    $height = $img->height();

    if ($width > 1920) {
        $img = $img->resize(1920, null);
    }
    if ($height > 1080) {
        $img = $img->resize(null, 1080);
    }

    return $img->encode(); // this is blob
}

function convertAudio($data) {
    global $env;

    if (!empty($env["ignore_audio_check"])) {
        $file = $data['tmp_name'];
    } else {
        $video = FFMpeg\FFMpeg::create()->open($data['tmp_name']);
        $audio_format = new FFMpeg\Format\Audio\Mp3();

        $file = sys_get_temp_dir() . '/' . generateHash() . '.mp3';
        $video->save($audio_format, $file);
    }

    return file_get_contents($file); // this is blob
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

        $adapter = new League\Flysystem\AwsS3v3\AwsS3Adapter($client, $env["storage"]["bucket"], '', [
            'visibility' => 'public'
        ]);
    } else {
        $adapter = new League\Flysystem\Adapter\Local(__DIR__ . '/../public/upload/');
    }

    return new League\Flysystem\Filesystem($adapter);
}

function uploadFlie($data, $file_type, $option = []) {
    global $env;

    if (empty($option["ignore_check"])) {
        $check = checkFileInfo($data);
        if (!$check["success"]) {
            return ["success" => false, "error" => $check["error"]];
        }
    }

    switch ($file_type) {
        case "voice":
            $type = "audio";
            break;
        case "emoji":
            $type = "image";
            break;
        case "thumbnail":
            $type = "image";
            break;
        default:
            $type = "unknown";
            break;
    }
    $mime = checkMime($data, $type);
    if (!$mime["success"]) {
        return ["success" => false, "error" => $mime["error"]];
    }

    switch ($type) {
        case "audio":
            $blob = convertAudio($data);
            break;
        case "image":
            $blob = resizeImage($data);
            break;
        default:
            $err = true;
            break;
    }
    if (isset($err)) {
        return ["success" => false, "error" => "ファイル形式"];
    }

    $storage = initStorage();
    try {
        $file_name = empty($option["file_name"]) ? generateHash() . "." . $mime["ext"] : $option["file_name"];

        /*
        $stream = fopen($data['tmp_name'], 'r');
        $result = $storage->writeStream($file_type . "/" . $file_name, $stream);
        fclose($stream);
        */

        if (empty($option["allow_already_exist"])) {
            $result = $storage->write($file_type . "/" . $file_name, $blob);
        } else {
            $result = $storage->put($file_type . "/" . $file_name, $blob);
        }

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
        if ($storage->has($path)) {
            $result = $storage->delete($path);
        }
    } catch (\League\Flysystem\FileExistsException $e) {
        if ($env["is_testing"]) {
            echo $e;
        }
        return false;
    }

    return $result;
}
