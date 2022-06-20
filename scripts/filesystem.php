<?php
    class FileSystem {
        public function imageAccount($image) {
            $nameFile = $this->generateCode("account").pathinfo($image["name"], PATHINFO_EXTENSION);
            if (copy($image["tmp_name"], "./images/accounts/$nameFile")) return $nameFile; else return array('ok' => false, 'cause' => 'No se ha podido copiar el archivo de imagen.');
        }
        public function createAccountImage($file) {
            $ImgProcess = new ProcessImageSystem();
            $newName = 'account_'.random_int(11111111, 99999999).'.webp';
            $process = $ImgProcess->process($file['tmp_name'], $file['name'], 512, 512, true);
            $copy = imagewebp($process, './images/accounts/'.$newName, 70);
            return ($copy)? $newName: 'default.jpg';
        }

        private function generateCode(string $tag) {
            $letters = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
            $code = "";
            for ($i=0; $i < 6; $i++) { 
                $code = $code.random_int(0, 9).$letters[random_int(0, count($letters))];
            }
            return $tag."_".$code;
        }
    }
    
?>