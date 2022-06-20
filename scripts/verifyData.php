<?php
    class VerifyData {
        public function issetDataPost($array) {
            $final = true;
            for ($i=0; $i < count($array); $i++) {
                if (isset($_POST[$array[$i]])) {
                    if (empty($_POST[$array[$i]])) {
                        $final = false;
                    }
                } else {
                    $final = false;
                }
            }
            return $final;
        }
        public function issetFilePost($file) {
            if (isset($_FILES[$file])) {
                if ($_FILES[$file]['name'] !== null) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        public function issetPosts($array) {
            $final = true;
            for ($i=0; $i < count($array); $i++) {
                if (!isset($_POST[$array[$i]])) $final = false;
            }
            return $final;
        }
        public function is_empty($value) {
            return ($value == NULL || $value == "");
        }
    }
    
?>