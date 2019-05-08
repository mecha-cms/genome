<?php

class Folder extends Genome {

    public $exist;
    public $path;

    public function __construct($path = null) {
        if (is_string($path)) {
            $this->path = realpath($path) ?: null;
            $this->exist = $path && is_dir($path);
        } else {
            $this->path = $path;
        }
        parent::__construct();
    }

    public function __toString() {
        return $this->exist ? $this->path : "";
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function get($x = null, $deep = false): \Generator {
        if ($this->exist && $folder = $this->path) {
            $content = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
            $content = new \RecursiveCallbackFilterIterator($content, function($v, $k, $a) use($deep, $x) {
                if ($deep > 0 && $a->hasChildren()) {
                    return true;
                }
                // Filter by function
                if (is_callable($x)) {
                    return call_user_func($x, $v->getPathname());
                }
                // Filter by type (`0` for folder and `1` for file)
                if ($x === 0 || $x === 1) {
                    return $v->{'is' . ($x === 0 ? 'Dir' : 'File')}();
                }
                // Filter file(s) by extension
                if (is_string($x)) {
                    $x = ',' . $x . ',';
                    return $v->isFile() && strpos($x, ',' . $v->getExtension() . ',') !== false;
                }
                // No filter
                return true;
            });
            $content = new \RecursiveIteratorIterator($content, !isset($x) || $x === 0 ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::LEAVES_ONLY);
            $content->setMaxDepth($deep === true ? -1 : (is_int($deep) ? $deep : 0));
            foreach ($content as $k => $v) {
                yield $k => $v->isDir() ? 0 : 1;
            }
        }
        return yield from [];
    }

    public function name() {
        return $this->exist ? basename($this->path) : null;
    }

    public function directory(int $i = 1) {
        return $this->exist ? dirname($this->path, $i) : null;
    }

    public function type() {
        return null;
    }

    public function x() {
        return null;
    }

    public function _consent() {
        return $this->exist ? fileperms($this->path) : null;
    }

    public function _size() {
        if ($this->exist) {
            // Empty folder
            if (!glob($this->path . DS . '*', GLOB_NOSORT)) {
                return 0;
            }
            // Scan all file(s) and count the file size
            $size = 0;
            foreach ($this->content(1, true) as $k => $v) {
                $size += filesize($k);
            }
            return $size;
        }
        return null;
    }

    // Set folder permission
    public function consent($i = null) {
        if (isset($consent) && is_dir($path = $this->path)) {
            chmod($path, is_string($i) ? octdec($i) : $i);
            return $this;
        }
        if (null !== ($i = $this->_consent())) {
            return substr(sprintf('%o', $i), -4);
        }
        return null;
    }

    public function copyTo() {}

    public function moveTo() {}

    public function size(string $unit = null, int $prec = 2) {
        if (null !== ($size = $this->_size())) {
            return File::sizer($size, $unit, $prec);
        }
        return File::sizer(0, $unit, $prec);
    }

    public static function set($path, $consent = 0775) {
        if (is_string($consent)) {
            $consent = octdec($consent);
        }
        if (is_array($path)) {
            $out = [];
            foreach ($path as $v) {
                $vv = self::create($v = realpath($v), $consent);
                // Return `0` on success
                $out[$v] = is_string($vv) ? 0 : $vv;
            }
            return $out;
        }
        if (!is_dir($path)) {
            if (mkdir($path = realpath($path), $consent, true)) {
                return $path; // Return `$path` on success
            }
            return null; // Return `null` on error
        }
        return false; // Return `false` if folder already exists
    }

    public function let($any = true) {
        $out = [];
        if ($this->exist) {
            $path = $this->path;
            if ($any === true) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $k) {
                    $v = $k->getPathname();
                    if ($k->isDir()) {
                        $out[$v] = rmdir($v) ? 0 : null;
                    } else {
                        $out[$v] = unlink($v) ? 1 : null;
                    }
                }
                $out[$path] = rmdir($path) ? 0 : null;
            } else if (is_array($any)) {
                foreach ($any as $v) {
                    $v = $path . DS . strtr($v, '/', DS);
                    if (is_file($v)) {
                        $out[$v] = unlink($v) ? 1 : null;
                    } else {
                        $out[$v] = (new static($v))->let() ? 0 : null;
                    }
                }
            }
        }
        return $out;
    }

    public static function exist($path) {
        if (is_array($path)) {
            foreach ($path as $v) {
                if ($v && is_dir($v)) {
                    return realpath($v);
                }
            }
            return false;
        }
        return is_dir($path) ? realpath($path) : false;
    }

}