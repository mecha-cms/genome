<?php

class File extends Genome implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

    const config = [
        // List of allowed file extension(s)
        'x' => [
            'css' => 1,
            'gif' => 1,
            'htm' => 1,
            'html' => 1,
            'jpe' => 1,
            'jpg' => 1,
            'jpeg' => 1,
            'js' => 1,
            'json' => 1,
            'log' => 1,
            'png' => 1,
            'txt' => 1,
            'xml' => 1
        ],
        'size' => [0, 2097152], // Range of allowed file size(s)
        'type' => [
            'application/javascript' => 1,
            'application/json' => 1,
            'application/xml' => 1,
            'image/gif' => 1,
            'image/jpeg' => 1,
            'image/png' => 1,
            'inode/x-empty' => 1, // Empty file
            'text/css' => 1,
            'text/html' => 1,
            'text/javascript' => 1,
            'text/plain' => 1,
            'text/xml' => 1
        ]
    ];

    public $exist;
    public $path;
    public $value;

    public function __construct($path = null) {
        $this->value[0] = "";
        if ($this->exist = $path && is_string($path) && strpos($path, ROOT) === 0) {
            if (!stream_resolve_include_path($path)) {
                if (!is_dir($d = dirname($path))) {
                    mkdir($d, 0775, true);
                }
                touch($path); // Create an empty file
            }
            $this->path = realpath($path) ?: null;
        }
    }

    public function __get(string $key) {
        if (method_exists($this, $key) && (new \ReflectionMethod($this, $key))->isPublic()) {
            return $this->{$key}();
        }
        return null;
    }

    public function __toString() {
        return $this->exist ? $this->path : "";
    }

    public function _seal() {
        return $this->exist ? fileperms($this->path) : null;
    }

    public function _size() {
        return $this->exist ? filesize($this->path) : null;
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function copy(string $to) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (!is_dir($to)) {
                mkdir($to, 0775, true);
            }
            if (is_file($v = $to . DS . basename($path))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                // Return `$v` on success, `null` on error
                $out[1] = copy($path, $v) ? $v : null;
            }
        }
        $this->value[1] = $out;
        return $this;
    }

    public function count() {
        return $this->exist ? 1 : 0;
    }

    public function directory(int $i = 1) {
        return $this->exist ? dirname($this->path, $i) : null;
    }

    public function get($i = null) {
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

    public function getIterator() {
        return $this->stream();
    }

    public function jsonSerialize() {
        return $this->path;
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

    public function move(string $to, string $as = null) {
        $out = [null];
        if ($this->exist && $path = $this->path) {
            $out[0] = $path;
            if (!is_dir($to)) {
                mkdir($to, 0775, true);
            }
            if (is_file($v = $to . DS . ($as ?? basename($path)))) {
                // Return `false` if file already exists
                $out[1] = false;
            } else {
                // Return `$v` on success, `null` on error
                $out[1] = rename($path, $v) ? $v : null;
            }
        }
        $this->value[1] = $out;
        return $this;
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

    public function offsetExists($i) {
        return !!$this->offsetGet($i);
    }

    public function offsetGet($i) {
        return $this->__get($i);
    }

    public function offsetSet($i, $value) {}
    public function offsetUnset($i) {}

    public function save($seal = null) {
        if ($path = $this->path) {
            if (isset($seal)) {
                $this->seal($seal);
            }
            // Return `$path` on success, `null` on error
            return file_put_contents($path, $this->value[0]) ? $path : null; 
        }
        if (defined('DEBUG') && DEBUG) {
            $c = static::class;
            throw new \Exception('Please provide a file path even if it does not exist. Example: `new ' . $c . '(\'' . ROOT . DS . c2f($c) . '.txt\')`');
        }
        return false; // Return `false` if `$this` is just a placeholder
    }

    public function seal($i = null) {
        if (isset($i) && $this->exist) {
            $i = is_string($i) ? octdec($i) : $i;
            // Return `$i` on success, `null` on error
            return chmod($this->path, $i) ? $i : null;
        }
        if (null !== ($i = $this->_seal())) {
            return substr(sprintf('%o', $i), -4);
        }
        // Return `false` if file does not exist
        return false;
    }

    public function set($content) {
        $this->value[0] = $content;
        return $this;
    }

    public function size(string $unit = null, $prec = 2) {
        if ($this->exist && is_file($path = $this->path)) {
            return self::sizer(filesize($path), $unit, $prec);
        }
        return null;
    }

    public function stream() {
        return $this->exist ? stream($this->path) : yield from [];
    }

    public function time(string $format = null) {
        if ($this->exist) {
            $t = filectime($this->path);
            return $format ? (new Date($t))($format) : $t;
        }
        return null;
    }

    public function type() {
        return $this->exist ? mime_content_type($this->path) : null;
    }

    public function update(string $format = null) {
        if ($this->exist) {
            $t = filemtime($this->path);
            return $format ? (new Date($t))($format) : $t;
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

    public static function pull() {}

    public static function push(array $blob, string $folder = ROOT) {
        if (!empty($blob['error'])) {
            return $blob['error']; // Return the error code
        }
        $folder = strtr($folder, '/', DS);
        if (is_file($path = $folder . DS . $blob['name'])) {
            return false; // Return `false` if file already exists
        }
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }
        if (move_uploaded_file($blob['tmp_name'], $path)) {
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