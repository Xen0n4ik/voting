<?php
session_start();

if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 0;
}

$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '0000',
    'name' => 'votes_db'
];

$mysqli = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name']);
if ($mysqli->connect_errno) {
    die("Ошибка подключения к БД: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
}

$votes = [];
$query = "SELECT v1, v2, v3, v4, v5 FROM votes WHERE ID = 1 LIMIT 1";
if ($result = $mysqli->query($query)) {
    $votes = $result->fetch_assoc();
    $result->free();
} else {
    die("Ошибка запроса: ({$mysqli->errno}) {$mysqli->error}");
}

if (isset($_POST['formSubmit'])) {
    if ($_SESSION['counter'] == 0) {
        $ansform = (int)$_POST['rb'];
        if ($ansform >= 1 && $ansform <= 5) {
            $field = "v{$ansform}";
            $votes[$field]++;

            $query = "UPDATE votes SET 
                      v1 = {$votes['v1']}, 
                      v2 = {$votes['v2']}, 
                      v3 = {$votes['v3']}, 
                      v4 = {$votes['v4']}, 
                      v5 = {$votes['v5']} 
                      WHERE ID = 1";

            if (!$mysqli->query($query)) {
                die("Ошибка обновления: ({$mysqli->errno}) {$mysqli->error}");
            }

            $_SESSION['counter'] = 1;
            header("Refresh:0");
            exit;
        }
    } else {
        echo "Вы уже проголосовали";
    }
}

$image = imagecreatetruecolor(500, 500);
$bg = imagecolorallocate($image, 200, 200, 200);
imagefill($image, 0, 0, $bg);
$col_text = imagecolorallocate($image, 0, 0, 0);

$cities = [
    ['name' => 'Moscow', 'x' => 6,   'value' => $votes['v1']],
    ['name' => 'SPb',    'x' => 68,  'value' => $votes['v2']],
    ['name' => 'Sochi',  'x' => 112, 'value' => $votes['v3']],
    ['name' => 'Kazan',  'x' => 162, 'value' => $votes['v4']],
    ['name' => 'Yalta',  'x' => 210, 'value' => $votes['v5']]
];

foreach ($cities as $i => $city) {
    $x1 = 10 + ($i * 50);
    $x2 = 50 + ($i * 50);

    $size = min($city['value'] * 10, 450);
    $col = min($city['value'] * 5, 255);

    $color = imagecolorallocate($image, 255 - $col, 255, 255);

    imagefilledrectangle($image, $x1, 490, $x2, 490 - $size, $color);
    imagestring($image, 4, $city['x'], 477, $city['name'], $col_text);
    imagestring($image, 5, ($x1 + $x2) / 2 - 5, 460, $city['value'], $col_text);
}

imagepng($image, './1.png');
imagedestroy($image);
$mysqli->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Голосование</title>
</head>
<body>
<h1>Голосование</h1>
<form method="post">
    Какой город России Вы бы хотели посетить?
    <p><input type="radio" name="rb" value="1" required> Москва</p>
    <p><input type="radio" name="rb" value="2"> Санкт-Петербург</p>
    <p><input type="radio" name="rb" value="3"> Сочи</p>
    <p><input type="radio" name="rb" value="4"> Казань</p>
    <p><input type="radio" name="rb" value="5"> Ялта</p>
    <input type="submit" name="formSubmit" value="Голосовать">
</form>
<hr>
<p>Результаты голосования: </p>
<p><img src="1.png?<?=time()?>" width="500" height="500" alt="Результаты голосования"></p>
</body>
</html>