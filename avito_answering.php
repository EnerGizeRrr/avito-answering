<?php
//Получение токена авторизации
$url = 'https://api.avito.ru/token/'; // url, на который отправляется запрос
$post_data = [ // поля нашего запроса
    'grant_type' => 'client_credentials',
    'client_id' => 'xxxxxxxxxxxx', //Получение  на авито в разделе 
    'client_secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxx', //Получение токена авторизации
];

$headers = ['Content-Type: application/x-www-form-urlencoded']; // заголовки запроса

$post_data = http_build_query($post_data);

$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true); // true - означает, что отправляется POST запрос

$res_auth = curl_exec($curl);

//Декодирование json, получение токена авторизации

$json_token = json_decode($res_auth);

$token_auth = $json_token->access_token; // получение токена из объекта


// Получение информации по чатам
$user_id = "9999999999"; // Номер профиля аккаунта в Авито

$headers_auth = ['Authorization: Bearer ' . $token_auth];

$ch = curl_init('https://api.avito.ru/messenger/v2/accounts/' . $user_id . '/chats');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_auth);
$res_chats = curl_exec($ch);



//Вебхук
$ch_u = curl_init("https://api.avito.ru/messenger/v3/webhook");
curl_setopt($ch_u, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $token_auth,
]);
curl_setopt($ch_u, CURLOPT_POST, true);

curl_setopt($ch_u, CURLOPT_POSTFIELDS, json_encode([
    "url" => "https://courier-krd.ru/avito_answering.php",
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch_u);


//Принимаем в веб хук информацию оповещения в JSON, декодируем ее, записываем в файл.
$json = file_get_contents('php://input');
$data = json_decode($json, TRUE);
file_put_contents(__DIR__ . '/message.txt', print_r($data, true), FILE_APPEND);

//Веб хук телеграм бота, который необходимо поставить, что бы приходили сообщения с Телеграма.
//https://api.telegram.org/bot<token>/setWebhook?url=https://-vash-site.ru/avito_answering.php


//Если приходит сообщение от пользователя на авито, то направить оповещение в телеграм и сообщение, что он написал.
if (($data['payload']['value']['type'] == "text") && ($data['payload']['value']['author_id'] !== <id профиля Авито>)) { //вместо <id профиля Авито> ввести id профиля на Авито

    $tg_user = '999999999'; // id пользователя, которому отправиться сообщения
    $bot_token = 'xxxxxxxxxxxxxxxxx'; // токен бота
    
    $text_user = $data['payload']['value']['content']['text'];
    $text = "<b>Новое сообщение от пользователя Авито!</b> \n". $text_user;
     
    // параметры, которые отправятся в api телеграмм
    $params = array(
        'chat_id' => $tg_user, // id получателя сообщения
        'text' => $text, // текст сообщения
        'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
    );
     
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $bot_token . '/sendMessage'); // адрес api телеграмм
    curl_setopt($curl, CURLOPT_POST, true); // отправка данных методом POST
    curl_setopt($curl, CURLOPT_TIMEOUT, 10); // максимальное время выполнения запроса
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // параметры запроса
    $result = curl_exec($curl); // запрос к api
    curl_close($curl);

};

//Оповещение в телеграм боте, что кандидат откликнулся на вакансию
if (strpos($data['payload']['value']['content']['text'], 'Кандидат откликнулся') !== false) {

    $tg_user = '99999999999999'; // id пользователя, которому отправиться сообщения
    $bot_token = '999999999999999999999999'; // токен бота
    

    $text = "<b>Новый кандидат откликнулся на вакансию!</b> \n";
     
    // параметры, которые отправятся в api телеграмм
    $params = array(
        'chat_id' => $tg_user, // id получателя сообщения
        'text' => $text, // текст сообщения
        'parse_mode' => 'HTML', // режим отображения сообщения, не обязательный параметр
    );
     
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $bot_token . '/sendMessage'); // адрес api телеграмм
    curl_setopt($curl, CURLOPT_POST, true); // отправка данных методом POST
    curl_setopt($curl, CURLOPT_TIMEOUT, 10); // максимальное время выполнения запроса
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // параметры запроса
    $result = curl_exec($curl); // запрос к api
    curl_close($curl);

};

//Отправка сообщения при отклике
if (strpos($data['payload']['value']['content']['text'], 'Кандидат откликнулся') !== false) {

    $chat_id = $data['payload']['value']['chat_id'];
    $user_id = $data['payload']['value']['user_id'];
    $text_otklik ="Текст"; // текст если кандидат откликнулся на вакансию
     
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.avito.ru/messenger/v1/accounts/'.$user_id.'/chats/'.$chat_id.'/messages'); // адрес api телеграмм
    curl_setopt($curl, CURLOPT_POST, true); // отправка данных методом POST
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token_auth,
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
        "message" => [
              "text" => $text_otklik 
           ], 
        "type" => "text" 
     ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    curl_setopt($curl, CURLOPT_TIMEOUT, 5); // максимальное время выполнения запроса
    $result = curl_exec($curl); // запрос к api
    curl_close($curl);

};


//