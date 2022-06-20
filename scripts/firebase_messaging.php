<?php
    include_once 'classes.php';

    class FirebaseMessagingSystem {
        private string $server_key = "AAAA_ZGpeFw:APA91bEa0e3fJrWhu9KDtrC10b6pF1UZprdLbnt3aKY2HKQWaghplT2eNfOfVjmtaULwtKvCSwWNT3vbxw_7fv3ixy463xGzg4uVyLrvOrcper8xFSqMfHtFJ1_9GXXzYJIAfRR3dHE4";
        public function send($data, string $to, bool $all) {
            $t = ($all)? '/topics/all': $to;
            $post = $this->post($data, $t);
            return $post;
        }
        private function post($notification, string $to) {
            try {
                $data = json_encode(array("notification" => $notification, "to" => $to));
                $headers = array("Content-Type:application/json", "Authorization:key=$this->server_key");
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($ch);
                curl_close($ch);
                return ($result !== false);
            } catch (\Throwable $th) {
                return false;
            }
        }
    }
    
?>