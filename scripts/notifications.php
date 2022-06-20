<?php

    include_once 'classes.php';

    class NotificationSystem {
        public function suscribe($idAccount, string $token) {
            try {
                $db = new DBSystem();
                $date = date("d/m/Y");
                if ($this->is_suscribe($idAccount)) {
                    $consult = $db->Query("UPDATE `notifications` SET `date`='$date',`token`='$token' WHERE `id_account`=$idAccount");
                    return ($consult)? array('ok' => true, 'cause' => ''): array('ok' => false, 'cause' => 'Ocurrio un error al consultar la base de datos.');
                } else {
                    $consult = $db->Query("INSERT INTO `notifications`(`id`, `id_account`, `date`, `token`) VALUES (NULL, '$idAccount', '$date', '$token')");
                    return ($consult)? array('ok' => true, 'cause' => ''): array('ok' => false, 'cause' => 'Ocurrio un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function is_suscribe($idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `notifications` WHERE `id_account`=$idAccount");
                return ($consult)? $consult->num_rows !== 0: false;
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function getToken(string $idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `notifications` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $data = $consult->fetch_array();
                    return $data['token'];
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function send($idAccount, string $title, string $body) {
            $firebase = new FirebaseMessagingSystem();
            $notification = array("title" => $title, "body" => $body);
            if ($this->is_suscribe($idAccount)) {
                $userToken = $this->getToken($idAccount);
                if ($userToken) $firebase->send($notification, $userToken, false);
            }
            
        }
    }
?>