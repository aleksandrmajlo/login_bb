<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Models\User;
use Illuminate\Http\Request;


define("SMSC_LOGIN", "billiardcity");            // логин клиента
define("SMSC_PASSWORD", "090c8d82d0c0c9efbecc4eebb0b7845e3872a0ad");    // пароль
define("SMSC_POST", 0);                    // использовать метод POST
define("SMSC_HTTPS", 0);                // использовать HTTPS протокол
define("SMSC_CHARSET", "windows-1251");    // кодировка сообщения: utf-8, koi8-r или windows-1251 (по умолчанию)
define("SMSC_DEBUG", 0);                // флаг отладки
define("SMTP_FROM", "api@smsc.ua");     // e-mail адрес отправителя

class SmsController extends Controller
{
    private $socket;
    private $sequence_number = 1;

    static function generateCode(Request $request)
    {

        $phones = $request->phones;
        $code = rand(1000, 9999);
        $message = $code;
        if ($request->typesms == 1) {
            $format = 9;
        } else {
            $format = 1;
        }
        $ajax = false;
        if ($request->has('ajaxmy')) {
            $ajax = true;
        }
        if ($ajax) {
            $id_sms = SmsController::send_sms($phones, $message, $code, 0, 0, 0, $format, false, '', [], $ajax);
            return response()->json([
                'success' => true,
                'id_sms' => $id_sms
            ], 200);

        } else {
            SmsController::send_sms($phones, $message, $code, 0, 0, 0, $format, false, '', [], $ajax);
        }
    }
    static function send_sms($phones, $message, $code, $translit = 0, $time = 0, $id = 0, $format, $sender = false, $query = "", $files = array(), $ajax = false)
    {
        static $formats = array(1 => "flash=1", "push=1", "hlr=1", "bin=1", "bin=2", "ping=1", "mms=1", "mail=1", "call=1", "viber=1", "soc=1");
        if ($format == 9) {
            $m = SmsController::_smsc_send_cmd("send", "cost=3&phones=" . urlencode($phones) . "&mes=" . urlencode($message) .
                "&translit=$translit&id=$id&mes=code&call=1" .
                ($sender === false ? "" : "&sender=" . urlencode($sender)) .
                ($time ? "&time=" . urlencode($time) : "") . ($query ? "&$query" : ""), $files);
        } else {
            $m = SmsController::_smsc_send_cmd("send", "cost=3&phones=" . urlencode($phones) . "&mes=" . urlencode($message) .
                "&translit=$translit&id=$id" . ($format > 0 ? "&" . $formats[$format] : "") .
                ($sender === false ? "" : "&sender=" . urlencode($sender)) .
                ($time ? "&time=" . urlencode($time) : "") . ($query ? "&$query" : ""), $files);
        }

        if (SMSC_DEBUG) {
            if ($m[1] > 0)
                echo "Стоимость рассылки: $m[0]. Всего SMS: $m[1]\n";
            else
                echo "Ошибка №", -$m[1], "\n";
        }

        $saveSms = new Sms();
        $saveSms->id_sms = $m[0];
        if ($format == 9) {
            $saveSms->code = $m[4];
        } else {
            $saveSms->code = $code;
        }
        $saveSms->type = 'register';
        $saveSms->save();
        if ($ajax) {
            return $m[0];
        } else {
            echo $m[0];
        }
    }

    public function checkCode(Request $request)
    {
        $ajax = false;
        if ($request->has('ajaxmy')) {
            $ajax = true;
        }
        //$checkCode = Sms::where('id_sms', '=', $request->cod)->first();
        $checkCode = Sms::where('id_sms', '=', $request->cod)->orderBy('id', 'desc')->first();
        if ($ajax) {
            if ($checkCode->code == $request->codes) {
                $res = 1;
                if ($request->has('order_id')) {
                    // если код правильный код то обновляем заказ
                    $order = \App\Order::find($request->order_id);
                    $order->customer_id = $request->user;
                    $order->save();
                }
            } else {
                $res = 2;
            }
            return response()->json([
                'success' => true,
                'res' => $res
            ], 200);

        } else {
            if ($checkCode->code == $request->codes) {
                echo 1;
            } else {
                echo 2;
            }
        }

    }

    // SMTP версия функции отправки SMS
    static function send_sms_mail($phones, $message, $translit = 0, $time = 0, $id = 0, $format = 0, $sender = "")
    {
        return mail("send@send.smsc.ua", "", SMSC_LOGIN . ":" . SMSC_PASSWORD . ":$id:$time:$translit,$format,$sender:$phones:$message", "From: " . SMTP_FROM . "\nContent-Type: text/plain; charset=" . SMSC_CHARSET . "\n");
    }

    //Отправка кода логин
    public function generateCodeLogin(Request $request)
    {
        $phone = $request->phone;
        $user_count = User::where('phone', $phone)->active()->count();
        if ($user_count) {
            $digits = 6;
            $random_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            $user = User::where('phone', $phone)->select('phone')->first();
            // получить исключенные логины
            $loginSelected = config('auth.loginSelected');
            if (isset($loginSelected[$phone])) {
                return response()->json([
                    'suc' => true,
                    'id_sms' => -1
                ]);
            }
            // получить исключенные логины end
            $format = 1;
            $ajax = true;
            $id_sms = SmsController::send_sms($user->phone, $random_number, $random_number, 0, 0, 0, $format, false, '', [], $ajax);
            return response()->json([
                'suc' => true,
                'id_sms' => $id_sms
            ]);
        } else {
            return response()->json(['suc' => false, 'notUser' => true]);
        }

    }



// Функция получения стоимости SMS
//
// обязательные параметры:
//
// $phones - список телефонов через запятую или точку с запятой
// $message - отправляемое сообщение
//
// необязательные параметры:
//
// $translit - переводить или нет в транслит (1,2 или 0)
// $format - формат сообщения (0 - обычное sms, 1 - flash-sms, 2 - wap-push, 3 - hlr, 4 - bin, 5 - bin-hex, 6 - ping-sms, 7 - mms, 8 - mail, 9 - call, 10 - viber, 11 - soc)
// $sender - имя отправителя (Sender ID)
// $query - строка дополнительных параметров, добавляемая в URL-запрос ("list=79999999999:Ваш пароль: 123\n78888888888:Ваш пароль: 456")
//
// возвращает массив (<стоимость>, <количество sms>) либо массив (0, -<код ошибки>) в случае ошибки

    static function get_sms_cost($phones, $message, $translit = 0, $format = 0, $sender = false, $query = "")
    {
        static $formats = array(1 => "flash=1", "push=1", "hlr=1", "bin=1", "bin=2", "ping=1", "mms=1", "mail=1", "call=1", "viber=1", "soc=1");

        $m = _smsc_send_cmd("send", "cost=1&phones=" . urlencode($phones) . "&mes=" . urlencode($message) .
            ($sender === false ? "" : "&sender=" . urlencode($sender)) .
            "&translit=$translit" . ($format > 0 ? "&" . $formats[$format] : "") . ($query ? "&$query" : ""));

        // (cost, cnt) или (0, -error)

        if (SMSC_DEBUG) {
            if ($m[1] > 0)
                echo "Стоимость рассылки: $m[0]. Всего SMS: $m[1]\n";
            else
                echo "Ошибка №", -$m[1], "\n";
        }

        return $m;
    }

// Функция проверки статуса отправленного SMS или HLR-запроса
//
// $id - ID cообщения или список ID через запятую
// $phone - номер телефона или список номеров через запятую
// $all - вернуть все данные отправленного SMS, включая текст сообщения (0,1 или 2)
//
// возвращает массив (для множественного запроса двумерный массив):
//
// для одиночного SMS-сообщения:
// (<статус>, <время изменения>, <код ошибки доставки>)
//
// для HLR-запроса:
// (<статус>, <время изменения>, <код ошибки sms>, <код IMSI SIM-карты>, <номер сервис-центра>, <код страны регистрации>, <код оператора>,
// <название страны регистрации>, <название оператора>, <название роуминговой страны>, <название роумингового оператора>)
//
// при $all = 1 дополнительно возвращаются элементы в конце массива:
// (<время отправки>, <номер телефона>, <стоимость>, <sender id>, <название статуса>, <текст сообщения>)
//
// при $all = 2 дополнительно возвращаются элементы <страна>, <оператор> и <регион>
//
// при множественном запросе:
// если $all = 0, то для каждого сообщения или HLR-запроса дополнительно возвращается <ID сообщения> и <номер телефона>
//
// если $all = 1 или $all = 2, то в ответ добавляется <ID сообщения>
//
// либо массив (0, -<код ошибки>) в случае ошибки

    static function get_status($id, $phone, $all = 0)
    {
        $m = _smsc_send_cmd("status", "phone=" . urlencode($phone) . "&id=" . urlencode($id) . "&all=" . (int)$all);

        // (status, time, err, ...) или (0, -error)

        if (!strpos($id, ",")) {
            if (SMSC_DEBUG)
                if ($m[1] != "" && $m[1] >= 0)
                    echo "Статус SMS = $m[0]", $m[1] ? ", время изменения статуса - " . date("d.m.Y H:i:s", $m[1]) : "", "\n";
                else
                    echo "Ошибка №", -$m[1], "\n";

            if ($all && count($m) > 9 && (!isset($m[$idx = $all == 1 ? 14 : 17]) || $m[$idx] != "HLR")) // ',' в сообщении
                $m = explode(",", implode(",", $m), $all == 1 ? 9 : 12);
        } else {
            if (count($m) == 1 && strpos($m[0], "-") == 2)
                return explode(",", $m[0]);

            foreach ($m as $k => $v)
                $m[$k] = explode(",", $v);
        }

        return $m;
    }

// Функция получения баланса
//
// без параметров
//
// возвращает баланс в виде строки или false в случае ошибки

    static function get_balance()
    {
        $m = SmsController::_smsc_send_cmd("balance"); // (balance) или (0, -error)

        if (SMSC_DEBUG) {
            if (!isset($m[1]))
                echo "Сумма на счете: ", $m[0], "\n";
            else
                echo "Ошибка №", -$m[1], "\n";
        }

        return isset($m[1]) ? false : $m[0];
    }


// ВНУТРЕННИЕ ФУНКЦИИ

// Функция вызова запроса. Формирует URL и делает 5 попыток чтения через разные подключения к сервису

    static function _smsc_send_cmd($cmd, $arg = "", $files = array())
    {
        $url = $_url = (SMSC_HTTPS ? "https" : "http") . "://smsc.ua/sys/$cmd.php?login=" . urlencode(SMSC_LOGIN) . "&psw=" . urlencode(SMSC_PASSWORD) . "&fmt=1&charset=" . SMSC_CHARSET . "&" . $arg;

        $i = 0;
        do {
            if ($i++)
                $url = str_replace('://smsc.ua/', '://www' . $i . '.smsc.ua/', $_url);

            $ret = SmsController::_smsc_read_url($url, $files, 3 + $i);
        } while ($ret == "" && $i < 5);

        if ($ret == "") {
            if (SMSC_DEBUG)
                echo "Ошибка чтения адреса: $url\n";

            $ret = ","; // фиктивный ответ
        }

        $delim = ",";

        if ($cmd == "status") {
            parse_str($arg, $m);

            if (strpos($m["id"], ","))
                $delim = "\n";
        }


        return explode($delim, $ret);
    }

// Функция чтения URL. Для работы должно быть доступно:
// curl или fsockopen (только http) или включена опция allow_url_fopen для file_get_contents

    static function _smsc_read_url($url, $files, $tm = 5)
    {
        $ret = "";
        $post = SMSC_POST || strlen($url) > 2000 || $files;

        if (function_exists("curl_init")) {
            static $c = 0; // keepalive

            if (!$c) {
                $c = curl_init();
                curl_setopt_array($c, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => $tm,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER => array("Expect:")
                ));
            }

            curl_setopt($c, CURLOPT_POST, $post);

            if ($post) {
                list($url, $post) = explode("?", $url, 2);

                if ($files) {
                    parse_str($post, $m);

                    foreach ($m as $k => $v)
                        $m[$k] = isset($v[0]) && $v[0] == "@" ? sprintf("\0%s", $v) : $v;

                    $post = $m;
                    foreach ($files as $i => $path)
                        if (file_exists($path))
                            $post["file" . $i] = function_exists("curl_file_create") ? curl_file_create($path) : "@" . $path;
                }

                curl_setopt($c, CURLOPT_POSTFIELDS, $post);
            }

            curl_setopt($c, CURLOPT_URL, $url);

            $ret = curl_exec($c);
        } elseif ($files) {
            if (SMSC_DEBUG)
                echo "Не установлен модуль curl для передачи файлов\n";
        } else {
            if (!SMSC_HTTPS && function_exists("fsockopen")) {
                $m = parse_url($url);

                if (!$fp = fsockopen($m["host"], 80, $errno, $errstr, $tm))
                    $fp = fsockopen("212.24.33.196", 80, $errno, $errstr, $tm);

                if ($fp) {
                    stream_set_timeout($fp, 60);

                    fwrite($fp, ($post ? "POST $m[path]" : "GET $m[path]?$m[query]") . " HTTP/1.1\r\nHost: smsc.ua\r\nUser-Agent: PHP" . ($post ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($m['query']) : "") . "\r\nConnection: Close\r\n\r\n" . ($post ? $m['query'] : ""));

                    while (!feof($fp))
                        $ret .= fgets($fp, 1024);
                    list(, $ret) = explode("\r\n\r\n", $ret, 2);

                    fclose($fp);
                }
            } else
                $ret = file_get_contents($url);
        }

        return $ret;
    }
}


