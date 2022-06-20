<?php
    class ProcessImageSystem {
        public function process($file, $fileName, $w, $h, $crop=FALSE) {
            list($width, $height) = getimagesize($file);
            $r = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width-($width*abs($r-$w/$h)));
                } else {
                    $height = ceil($height-($height*abs($r-$w/$h)));
                }
                $newwidth = $w;
                $newheight = $h;
            } else {
                if ($w/$h > $r) {
                    $newwidth = $h*$r;
                    $newheight = $h;
                } else {
                    $newheight = $w/$r;
                    $newwidth = $w;
                }
            }
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'png':
                    $src = imagecreatefrompng($file);
                    break;
                case 'jpg' || 'jpeg':
                    $src = imagecreatefromjpeg($file);
                    break;
                case 'webp':
                    $src = imagecreatefromwebp($file);
                    break;
            }
            if ($this->isVertical($width, $height)) {
                $dst = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                $dst2 = imagerotate($dst, -90, 0);
                return $dst2;
            } else {
                $dst = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                return $dst;
            }
        }
        private function isVertical($w, $h) {
            $width = ($w < 0)? ($w * -1): $w;
            $height = ($h < 0)? ($h * -1): $h;
            return ($width < $height);
        }
    }
?>