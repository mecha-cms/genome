<?php

class File extends Genome {

    const config = [
        // List of allowed file extension(s)
        'x' => [
            'gif' => 1,
            'htm' => 1,
            'html' => 1,
            'jpg' => 1,
            'jpeg' => 1,
            'json' => 1,
            'log' => 1,
            'png' => 1,
            'txt' => 1,
            'xml' => 1,
            'yaml' => 1,
            'yml' => 1
        ],
        'size' => [0, 2097152] // Range of allowed file size(s)
    ];

    protected function _set_(string $data) {
        $this->content = $data;
        return $this;
    }

    public $content;
    public $exist;
    public $path;

    public function __construct($path = null) {
        $this->content = "";
        if (is_string($path)) {
            $this->path = realpath($path) ?: null;
            $this->exist = $path && is_file($path);
        } else {
            $this->path = $path;
        }
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
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (!is_dir($folder)) {
                mkdir($folder, 0775, true);
            }
            if (is_file($v = $folder . DS . ($name ?? basename($path)))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                // Return `$v` on success, `null` on error
                $out[1] = rename($path, $v) ? $v : null;
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

    public function renameTo(string $name) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            $folder = dirname($path);
            $a = basename($path);
            $b = basename($name);
            if (is_file($v = $folder . DS . $b)) {
                // Return `false` if file already exists
                $out[1] = false;
            } else if ($a === $b) {
                // New name is the same as old name, do nothing!
                $out[1] = $v;
            } else {
                // Return `$v` on success, `null` on error
                $out[1] = rename($path, $v) ? $v : null;
            }
        }
        return $out;
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
        return false; // Return `false` if file does not exist
    }

    public static $config = self::config;

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

    public static function pull() {}

    public static function push(array $data, string $folder = ROOT) {
        if (!empty($data['error'])) {
            return $data['error']; // Return the error code
        }
        $folder = strtr($folder, '/', DS);
        if (is_file($path = $folder . DS . $data['name'])) {
            return false; // Return `false` if file already exist
        }
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }
        if (move_uploaded_file($data['tmp_name'])) {
            return $path; // Return `$path` on success
        }
        return null; // Return `null` on error
    }

    public static function sizer(float $size, string $unit = null, int $prec = 2) {
        $i = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor($i) : 0);
        $out = round($size / pow(1024, $u), $prec);
        return $out < 0 ? null : trim($out . ' ' . $x[$u]);
    }

}