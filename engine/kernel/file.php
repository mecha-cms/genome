<?php

class File extends Genome {

    const config = [
        'x' => ['txt'], // List of allowed file extension(s)
        'size' => [0, 2097152] // Range of allowed file size(s)
    ];

    protected function _set_(string $data) {
        $this->content = $data;
        return $this;
    }

    public $content;
    public $exist;
    public $path;

    public static $config = self::config;

    public function __construct($path = null) {
        $this->content = "";
        if (is_string($path)) {
            $this->path = realpath($path) ?: null;
            $this->exist = $path && is_file($path);
        } else {
            $this->path = $path;
        }
        parent::__construct();
    }

    public function __toString() {
        return $this->exist ? $this->path : "";
    }

    public function _consent() {
        return $this->exist ? fileperms($this->path) : null;
    }

    public function _size() {
        return $this->exist ? filesize($this->path) : null;
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function append(string $data) {
        $this->content .= $data;
        return $this;
    }

    public function consent($i = null) {
        if (isset($consent) && $this->exist) {
            chmod($this->path, is_string($i) ? octdec($i) : $i);
            return $this;
        }
        if (null !== ($i = $this->_consent())) {
            return substr(sprintf('%o', $i), -4);
        }
        return null;
    }

    public function copyTo(string $folder) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (!is_dir($folder)) {
                mkdir($folder, 0775, true);
            }
            if (is_file($v = $folder . DS . basename($path))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                // Return `$v` on success, `null` on error
                $out[1] = copy($path, $v) ? $v : null;
            }
        }
        return $out;
    }

    public function directory(int $i = 1) {
        return $this->exist ? dirname($this->path, $i) : null;
    }

    public function get(int $i = null) {
        if ($this->exist) {
            if (isset($i)) {
                foreach ($this->stream() as $k => $v) {
                    if ($k === $i) {
                        return $v;
                    }
                }
                return null;
            }
            return content($this->path);
        }
        return null;
    }

    public function let() {
        if ($this->exist) {
            if (unlink($path = $this->path)) {
                return $path; // Return `$path` on success
            }
            return null; // Return `null` on error
        }
        return false; // Return `false` if file does not exist
    }

    public function moveTo(string $folder, string $name = null) {
        $out = [];
        if ($this->exist && $path = $this->path) {
            if (!is_dir($folder)) {
                mkdir($folder, 0775, true);
            }
            if (is_file($v = $folder . DS . ($name ?? basename($path)))) {
                // Return `false` if file already exists
                $out[$path] = false;
            } else {
                // Return `$v` on success, `null` on error
                $out[$path] = rename($path, $v) ? $v : null;
            }
        }
        return $out;
    }

    public function name($x = false) {
        if ($this->exist && $path = $this->path) {
            if ($x === true) {
                return basename($path);
            }
            return pathinfo($path, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
        }
        return null;
    }

    public function prepend(string $data) {
        $this->content = $data . $this->content;
        return $this;
    }

    public function save($consent = null) {
        return $this->saveTo($this->path, $consent);
    }

    public function saveAs(string $name, $consent = null) {
        return $this->exist ? $this->saveTo(dirname($this->path) . DS . basename($name), $consent) : false;
    }

    public function saveTo(string $path, $consent = null) {
        $this->path = $path = strtr($path, '/', DS);
        if (!is_dir($d = dirname($path))) {
            mkdir($d, 0775, true);
        }
        if (file_put_contents($path, $this->content) !== false) {
            if (isset($consent)) {
                chmod($path, $consent);
            }
            return $path; // Return `$path` on success
        }
        return null; // Return `null` on error
    }

    public function size(string $unit = null, $prec = 2) {
        if ($this->exist && is_file($path = $this->path)) {
            return self::sizer(filesize($path), $unit, $prec);
        }
        return null;
    }

    public function stream() {
        return stream($this->path);
    }

    public function time(string $format = null) {
        if ($this->exist) {
            $t = filectime($this->path);
            return $format ? strftime($format, $t) : $t;
        }
        return null;
    }

    public function type() {
        return $this->exist ? mime_content_type($this->path) : null;
    }

    public function update(string $format = null) {
        if ($this->exist) {
            $t = filemtime($this->path);
            return $format ? strftime($format, $t) : $t;
        }
        return null;
    }

    public function x() {
        if ($this->exist) {
            $path = $this->path;
            if (strpos($path, '.') === false) {
                return null;
            }
            $x = pathinfo($path, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return null;
    }

    public static function exist($path) {
        if (is_array($path)) {
            foreach ($path as $v) {
                if ($v && is_file($v)) {
                    return realpath($v);
                }
            }
            return false;
        }
        return is_file($path) ? realpath($path) : false;
    }

    public static function from(string $path) {
        return new static($path);
    }

    public static function open(string $path) {
        return new static($path);
    }

    public static function pull(string $path, string $name = null, string $type = null) {
        if (is_file($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . ($type ?? 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . ($name ?? basename($path)) . '"');
            header('Content-Length: ' . filesize($path));
            header('Expires: 0');
            header('Pragma: public');
            readfile($path);
            exit;
        }
        return false; // Return `false` if file does not exist
    }

    public static function push(array $blob, string $path = ROOT) {
        $path = rtrim(strtr($path, '/', DS), DS);
        if (!empty($blob['error'])) {
            return $blob['error']; // Has error, abort!
        }
        if (is_file($f = $path . DS . $blob['name'])) {
            return false; // File already exists
        }
        // Destination folder does not exist
        if (!is_dir($path)) {
            mkdir($path, 0775, true); // Create one!
        }
        move_uploaded_file($blob['tmp_name'], $f);
        return $f; // There is no error, the file uploaded with success
    }

    public static function sizer(float $size, string $unit = null, int $prec = 2) {
        $i = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor($i) : 0);
        $out = round($size / pow(1024, $u), $prec);
        return $out < 0 ? null : trim($out . ' ' . $x[$u]);
    }

}