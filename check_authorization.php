<?php

define('BOT_TOKEN', '50********:AAF9qlKa3XsYW1B********************'); // place bot token of your bot here

function checkTelegramAuthorization($auth_data)
{
    writeLog('input auth data', [
        'auth data' => $auth_data,
    ]);
    $check_hash = $auth_data['hash'];
    unset($auth_data['hash']);
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    writeLog('before sorting', [
        'data check arr' => $data_check_arr,
    ]);
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    writeLog('deb', [
        'data check arr' => $data_check_arr,
        'data check str' => $data_check_string,
    ]);
    $secret_key = hash('sha256', BOT_TOKEN, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);

    writeLog('cmp hashes', [
        'secret_key' => $secret_key,
        'hash' => $hash,
        'check hash' =>  $check_hash
    ]);
    if (strcmp($hash, $check_hash) !== 0) {
        throw new Exception('Data is NOT from Telegram');
    }
    if ((time() - $auth_data['auth_date']) > 86400) {
        throw new Exception('Data is outdated');
    }
    return $auth_data;
}

function saveTelegramUserData($auth_data)
{
    $auth_data_json = json_encode($auth_data);
    setcookie('tg_user', $auth_data_json);
}


try {
    $auth_data = checkTelegramAuthorization($_GET);
    saveTelegramUserData($auth_data);
} catch (Exception $e) {
    die ($e->getMessage());
}

header('Location: login_example.php');

function writeLog($description, $data = [])
{
    $logData = [
        "timestamp" =>  date('m-d-Y_h:i:sa', time()),
        "description" => $description,
        "data" => $data,
    ];
    file_put_contents(
        __DIR__ . '/log.txt',
        json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND
    );
}

?>
login_example.php