<?php

class Image extends __ {

    public $open = null;
    public $origin = null;
    public $placeholder = null;

    public $GD = false;

    public static $config = array(
        'placeholder' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
    );

    public function gen($file = null) {
        if ($file === null) {
            if (!file_exists($this->placeholder)) {
                File::open($this->origin)->copyTo($this->placeholder);
            }
            $file = $this->placeholder;
        }
        switch (Path::X($file)) {
            case 'gif': $this->GD = imagecreatefromgif($file); break;
            case 'jpeg': $this->GD = imagecreatefromjpeg($file); break;
            case 'jpg': $this->GD = imagecreatefromjpeg($file); break;
            case 'png': $this->GD = imagecreatefrompng($file); break;
        }
        return $this;
    }

    public function twin($resource = null, $x = null) {
        $file = $this->placeholder;
        if ($resource === null) $resource = $this->GD;
        $o_x = Path::X($this->origin);
        $n_x = Path::X($file);
        if ($x !== null) {
            $file = preg_replace('#\.([\da-z]+)$#i', '.' . $x, $file);
            File::open($this->placeholder)->delete();
            $this->placeholder = $file;
            $n_x = $x;
        }
        switch ($n_x) {
            case 'gif': imagegif($resource, $file); break;
            case 'jpeg': imagejpeg($resource, $file, 100); break;
            case 'jpg': imagejpeg($resource, $file, 100); break;
            case 'png': imagepng($resource, $file); break;
        }
        return $this;
    }

    public function __construct($files, $fail = false) {
        if (!extension_loaded('gd')) {
            if ($fail === null) {
                exit('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &ndash; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
            }
            return $fail;
        }
        if (is_array($files)) {
            $this->open = [];
            foreach ($files as $file) {
                $this->open[] = URL::path($file);
            }
        } else {
            $this->open = URL::path($files);
        }
        $file = is_array($this->open) ? $this->open[0] : $this->open;
        $this->origin = $file;
        $this->placeholder = Path::D($file) . DS . '__' . Path::B($file);
        return $this;
    }

    public static function take($files) {
        return new Image($files);
    }

    // Generate a 1 x 1 pixel transparent image or a random image URL output from array
    public static function placeholder($url = null) {
        if (is_array($url)) {
            return Group::take($url)->shake()->give(0);
        }
        return self::$config['placeholder'];
    }

    public function saveTo($target) {
        if (is_dir($target)) {
            $target .= DS . Path::B($this->origin);
        }
        $o_x = Path::X($this->origin);
        $n_x = Path::X($target);
        if ($o_x !== $n_x || !file_exists($this->placeholder)) {
            $this->gen()->twin(null, $n_x);
        }
        File::open($this->placeholder)->moveTo($target);
        imagedestroy($this->GD);
    }

    public function saveAs($name = 'image-%d.png') {
        return $this->saveTo(Path::D($this->origin) . DS . sprintf($name, time()));
    }

    // Save anyway ...
    public function save() {
        return $this->saveTo($this->origin);
    }

    public function draw($save = false) {
        $this->gen();
        $image = file_get_contents($this->placeholder);
        if ($save !== false) {
            $save = URL::path($save);
            File::write($image)->saveTo($save);
        }
        header('Content-Type: ' . $this->inspect('mime'));
        File::open($this->placeholder)->delete();
        imagedestroy($this->GD);
        echo $image;
        exit;
    }

    public function inspect($key = null, $fail = false) {
        if (is_array($this->open)) {
            $results = [];
            foreach ($this->open as $file) {
                $data = getimagesize($file);
                $results[] = array_merge(File::inspect($file), [
                    'width' => $data[0],
                    'height' => $data[1],
                    'bits' => $data['bits'],
                    'mime' => $data['mime']
                ]);
            }
            if (!is_null($key)) {
                return $results[$key] ?? $fail;
            }
            return $results;
        } else {
            $data = getimagesize($this->open);
            $results = array_merge(File::inspect($this->open), [
                'width' => $data[0],
                'height' => $data[1],
                'bits' => $data['bits'],
                'mime' => $data['mime']
            ]);
            if (!is_null($key)) {
                return $results[$key] ?? $fail;
            }
            return $results;
        }
        return false;
    }

    public function resize($max_width = 100, $max_height = null, $proportional = true, $crop = false) {
        $this->gen();
        $info = $this->inspect();
        $old_width = $info['width'];
        $old_height = $info['height'];
        $new_width = $max_width;
        $new_height = $max_height ?? $max_width;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
        if ($proportional) {
            // Don't do anything if the new image size is bigger than the original image size
            if ($old_width < $max_width && $old_height < $max_height) {
                return $this->twin();
            }
            if ($crop) {
                // Wider than the thumbnail (in aspect ratio sense)
                if ($current_ratio > $desired_ratio_after) {
                    $new_width = $old_width * $max_height / $old_height;
                // Wider than the image
                } else {
                    $new_height = $old_height * $max_width / $old_width;
                }
                // Calculate where to crop based on the center of the image
                $width_ratio = $old_width / $new_width;
                $height_ratio = $old_height / $new_height;
                $x = floor((($new_width - $max_width) / 2) * $width_ratio);
                $y = round((($new_height - $max_height) / 2) * $height_ratio);
                $pallete = imagecreatetruecolor($max_width, $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $pallete = imagecreatetruecolor($new_width, $new_height);
            }
        } else {
            $pallete = imagecreatetruecolor($max_width, $max_height);
        }
        // Draw ...
        imagealphablending($pallete, false);
        imagesavealpha($pallete, true);
        imagecopyresampled($pallete, $this->GD, 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
    }

    public function crop($x = 72, $y = null, $width = null, $height = null) {
        if ($width === null) {
            return $this->resize($x, $y ?? $x, true, true);
        }
        $this->gen();
        $pallete = imagecreatetruecolor($width, $height ?? $width);
        imagecopy($pallete, $this->GD, 0, 0, $x, $y ?? $x, $width, $height ?? $width);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
    }

    public function brightness($level = 0) {
        $this->gen();
        // -255 = min brightness, 0 = no change, +255 = max brightness
        imagefilter($this->GD, IMG_FILTER_BRIGHTNESS, $level);
        return $this->twin();
    }

    public function contrast($level = 0) {
        $this->gen();
        // -100 = max contrast, 0 = no change, +100 = min contrast (it's inverted)
        imagefilter($this->GD, IMG_FILTER_CONTRAST, $level * -1);
        return $this->twin();
    }

    public function colorize($r = 255, $g = 255, $b = 255, $a = 1) {
        $this->gen();
        // For red, green and blue: -255 = min, 0 = no change, +255 = max
        if (is_array($r)) {
            if (count($r) === 3) {
                $r[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($r);
        } else {
            $bg = (string) $r;
            if ($bg[0] === '#' && $color = Converter::HEX2RGB($r)) {
                $a = $g;
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
            } else if ($color = Converter::RGB($r)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        // For alpha: 127 = transparent, 0 = opaque
        $a = 127 - ($a * 127);
        imagefilter($this->GD, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
        return $this->twin();
    }

    public function grayscale() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_GRAYSCALE);
        return $this->twin();
    }

    public function negate() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_NEGATE);
        return $this->twin();
    }

    public function emboss($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_EMBOSS);
            $this->twin();
        }
        return $this;
    }

    public function blur($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_GAUSSIAN_BLUR);
            $this->twin();
        }
        return $this;
    }

    public function sharpen($level = 1) {
        $level = round($level);
        $matrix = array(
            array(-1, -1, -1),
            array(-1, 16, -1),
            array(-1, -1, -1),
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imageconvolution($this->GD, $matrix, $divisor, 0);
            $this->twin();
        }
        return $this;
    }

    public function pixelate($level = 1, $advance = false) {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_PIXELATE, $level, $advance);
        return $this->twin();
    }

    public function rotate($angle = 0, $bg = false, $alpha_for_hex = 1) {
        $this->gen();
        if (!$bg) {
            $bg = array(0, 0, 0, 0); // transparent
        }
        if (is_array($bg)) {
            if (count($bg) === 3) {
                $bg[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if ($bg[0] === '#' && $color = Converter::HEX2RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $alpha_for_hex;
            } else if ($color = Converter::RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        $a = 127 - ($a * 127);
        $bg = imagecolorallocatealpha($this->GD, $r, $g, $b, $a);
        imagealphablending($this->GD, false);
        imagesavealpha($this->GD, true);
        // The angle value in `imagerotate` function is also inverted
        $rotated = imagerotate($this->GD, (floor($angle) * -1), $bg, 0);
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        $this->twin($rotated);
        imagedestroy($rotated);
        return $this;
    }

    public function flip($dir = 'horizontal') {
        $this->gen();
        $type = Group::alter(strtolower($dir[0]), [
            'h' => IMG_FLIP_HORIZONTAL,
            'v' => IMG_FLIP_VERTICAL,
            'b' => IMG_FLIP_BOTH
        ], IMG_FLIP_HORIZONTAL);
        imageflip($this->GD, $type);
        return $this->twin();
    }

    public function merge($gap = 0, $orientation = 'vertical', $bg = false, $alpha_for_hex = 1) {
        $bucket = [];
        $width = $height = 0;
        $bucket = $max_width = $max_height = [];
        $orientation = strtolower($orientation);
        $this->open = (array) $this->open;
        foreach ($this->inspect() as $info) {
            $bucket[] = [
                'width' => $info['width'],
                'height' => $info['height']
            ];
            $max_width[] = $info['width'];
            $max_height[] = $info['height'];
            $width += $info['width'] + $gap;
            $height += $info['height'] + $gap;
        }
        if (!$bg) {
            $bg = array(0, 0, 0, 0); // transparent
        }
        if (is_array($bg)) {
            if (count($bg) === 3) {
                $bg[] = 1; // missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if ($bg[0] === '#' && $color = Converter::HEX2RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $alpha_for_hex;
            } else if ($color = Converter::RGB($bg)) {
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $a = $color['a'];
            }
        }
        $a = 127 - ($a * 127);
        if ($orientation[0] === 'v') {
            $pallete = imagecreatetruecolor(max($max_width), $height - $gap);
        } else {
            $pallete = imagecreatetruecolor($width - $gap, max($max_height));
        }
        $bg = imagecolorallocatealpha($pallete, $r, $g, $b, $a);
        imagefill($pallete, 0, 0, $bg);
        imagealphablending($pallete, true);
        imagesavealpha($pallete, true);
        $start_width_from = 0;
        $start_height_from = 0;
        for ($i = 0, $count = count($this->open); $i < $count; ++$i) {
            $this->gen($this->open[$i]);
            imagealphablending($this->GD, false);
            imagesavealpha($this->GD, true);
            imagecopyresampled($pallete, $this->GD, $start_width_from, $start_height_from, 0, 0, $bucket[$i]['width'], $bucket[$i]['height'], $bucket[$i]['width'], $bucket[$i]['height']);
            $start_width_from += $orientation[0] === 'h' ? $bucket[$i]['width'] + $gap : 0;
            $start_height_from += $orientation[0] === 'v' ? $bucket[$i]['height'] + $gap : 0;
        }
        return $this->twin($pallete, 'png');
    }

}