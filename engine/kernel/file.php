<?php

class File extends Genome {

    const config = [
        'x' => ['txt'], // List of allowed file extension(s)
        'size' => [0, 2097152] // Range of allowed file size(s)
    ];

    public $content;
    public $exist;
    public $path;

    public static $config = self::config;

    // Append `$data` before save
    protected function _append_(string $data) {
        $this->content .= $data;
        return $this;
    }

    // Prepend `$data` before save
    protected function _prepend_(string $data) {
        $this->content = $data . $this->content;
        return $this;
    }

    // Write `$data` before save
    protected function _put_(string $data) {
        $this->content = $data;
        return $this;
    }

    // Alias for `put`
    protected function _set_(string $data) {
        return $this->_put_($data);
    }

    public function URL() {
        return $this->exist ? To::URL($this->path) : null;
    }

    public function __construct($path = null) {
        $this->content = "";
        if (is_string($path)) {
            $this->path = realpath($path) ?: null;
            $this->exist = !!$this->path;
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

    // Set file permission
    public function consent($consent = null) {
        if ($this->exist) {
            $path = $this->path;
            if (!isset($consent)) {
                return substr(sprintf('%o', fileperms($path)), -4);
            }
            chmod($path, is_string($consent) ? octdec($consent) : $consent);
        }
        return $this;
    }

    // Copy the file/folder to … (folder)
    public function copyTo($folder = ROOT, string $pattern = '%{name}%.%{i}%.%{x}%') {
        $i = 1;
        $path = $this->path;
        $out = [];
        if (isset($path)) {
            $out[0] = $path;
            $b = basename($path);
            // Copy folder
            if (is_dir($path)) {
                foreach (self::explore([$path, 1], true, []) as $k => $v) {
                    $dir = dirname($folder . DS . $b . DS . str_replace($path . DS, "", $k));
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    $out[1][] = self::open($k)->copyTo($dir, $pattern)[1][0];
                }
                $this->path = $folder . DS . $b;
                return $out;
            }
            // Copy file
            foreach ((array) $folder as $v) {
                if (!is_dir($v)) {
                    mkdir($v, 0775, true);
                }
                $v .= DS . $b;
                if (!is_file($v)) {
                    if (copy($path, $v)) {
                        $out[1][] = $v;
                    }
                    $i = 1;
                } else if ($pattern) {
                    $v = dirname($v) . DS . candy($pattern, [
                        'name' => pathinfo($v, PATHINFO_FILENAME),
                        'i' => $i,
                        'x' => pathinfo($v, PATHINFO_EXTENSION)
                    ]);
                    if (copy($path, $v)) {
                        $out[1][] = $v;
                    }
                    ++$i;
                } else {
                    if (copy($path, $v)) {
                        $out[1][] = $v;
                    }
                }
                $this->path = $v;
            }
        }
        return $out;
    }

    // Delete the file
    public function delete() {
        $path = $this->path;
        $out = [];
        if (isset($path)) {
            if (is_dir($path)) {
                $a = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
                $b = new \RecursiveIteratorIterator($a, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $v) {
                    $p = $v->getPathname();
                    $out[] = $p;
                    if ($v->isFile()) {
                        unlink($p);
                    } else {
                        rmdir($p);
                    }
                }
                rmdir($path);
            } else {
                unlink($path);
            }
            $out[] = $path;
        }
        return $out;
    }

    // Print the file content line by line
    public function get($stop = null, $ch = 1024) {
        $i = 0;
        $out = "";
        if (isset($this->path) && filesize($this->path) > 0 && ($hand = fopen($this->path, 'r'))) {
            while (false !== ($chunk = fgets($hand, $ch))) {
                $out .= $chunk;
                if (
                    // `->get(7)`
                    is_int($stop) && $stop === $i ||
                    // `->get('$')`
                    is_string($stop) && strpos($chunk, $stop) !== false ||
                    // `->get(['$', 7])`
                    is_array($stop) && strpos($chunk, $stop[0]) === $stop[1] ||
                    // `->get(function($chunk, $i, $out) {})`
                    is_callable($stop) && fn($stop, [$chunk, $i, $out], $this, static::class)
                ) break;
                ++$i;
            }
            fclose($hand);
            return rtrim($out);
        }
        return null;
    }

    // Import the exported PHP file
    public function import() {
        $path = $this->path;
        if (!$path || !is_file($path)) {
            return [];
        }
        return include $path;
    }

    // Move the file/folder to … (folder)
    public function moveTo(string $folder = ROOT, $as = null) {
        $path = $this->path;
        $out = [];
        if (isset($path)) {
            $b = basename($path);
            if (is_dir($path)) {
                foreach (self::open($path)->copyTo($folder) as $k => $v) {
                    $out[$k] = $v;
                    unlink($k);
                }
                self::open($path)->delete();
                $this->path = $k = $folder . DS . $b;
                if ($as !== null) {
                    rename($k, $v = $folder . DS . $as);
                    $this->path = $out[$k] = $v;
                }
            } else {
                if (!is_dir($folder)) {
                    mkdir($folder, 0775, true);
                }
                if (rename($path, $to = $folder . DS . ($as ?: $b))) {
                    $out = [$path => $to];
                }
                $this->path = $to;
            }
        }
        return $out;
    }

    public function name($x = false) {
        if ($this->exist) {
            $path = $this->path;
            if ($x === true) {
                return basename($path);
            }
            return pathinfo($path, PATHINFO_FILENAME) . (is_string($x) ? '.' . $x : "");
        }
        return null;
    }

    // Download a file
    public function pull(string $name = null, string $type = null) {
        if ($this->exist) {
            $path = $this->path;
            HTTP::header([
                'Content-Description' => 'File Transfer',
                'Content-Type' => $type ?? 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . ($name ?? basename($path)) . '"',
                'Content-Length' => filesize($path),
                'Expires' => 0,
                'Pragma' => 'public'
            ]);
            // Show the browser saving dialog!
            readfile($path);
            exit;
        }
        return $this;
    }

    // Print the file content
    public function content() {
        if ($this->exist) {
            $content = filesize($this->path) > 0 ? file_get_contents($this->path) : "";
            return $content !== false ? $content : null;
        }
        return null;
    }

    // Rename the file/folder
    public function renameTo(string $name) {
        $path = $this->path;
        if (isset($path)) {
            $b = basename($path);
            $d = dirname($path) . DS;
            $v = $d . $name;
            if ($name !== $b && !is_file($v)) {
                rename($path, $v);
            }
            $this->path = $v;
        }
        return [$path, $v];
    }

    // Alias for `delete`
    public function reset() {
        return $this->delete();
    }

    // Save the `$data`
    public function save($consent = null) {
        return $this->saveTo($this->path, $consent);
    }

    // Save the `$data` as …
    public function saveAs(string $name, $consent = null) {
        return $this->exist ? $this->saveTo(dirname($this->path) . DS . basename($name), $consent) : false;
    }

    // Save the `$data` to …
    public function saveTo(string $path, $consent = null) {
        $this->path = $path;
        $path = To::path($path);
        if (!is_dir($d = dirname($path))) {
            mkdir($d, 0775, true);
        }
        file_put_contents($path, $this->content);
        if (isset($consent)) {
            chmod($path, $consent);
        }
        return $path;
    }

    // Convert file size to …
    public function size(string $unit = null, $prec = 2) {
        return self::sizer($this->exist ? filesize($this->path) : 0, $unit, $prec);
    }

    public function time(string $format = null) {
        if ($this->exist) {
            $i = filectime($this->path);
            return $format ? date($format, $i) : $i;
        }
        return null;
    }

    public function type() {
        return $this->exist ? mime_content_type($this->path) : null;
    }

    public function update(string $format = null) {
        if ($this->exist) {
            $i = filemtime($this->path);
            return $format ? date($format, $i) : $i;
        }
        return null;
    }

    public function x() {
        if ($this->exist) {
            $path = $this->path;
            if (strpos($path, '.') === false)
                return null;
            $x = pathinfo($path, PATHINFO_EXTENSION);
            return $x ? strtolower($x) : null;
        }
        return null;
    }

    // Check if file/folder does exist
    public static function exist($path) {
        if (is_array($path)) {
            foreach ($path as $v) {
                if ($v = stream_resolve_include_path($v)) {
                    return $v;
                }
            }
            return false;
        }
        return stream_resolve_include_path($path);
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $fail = []) {
        $id = json_encode(func_get_args());
        if (isset(self::$explore[$id])) {
            $out = self::$explore[$id];
            return !empty($out) ? $out : $fail;
        }
        $x = null;
        if (is_array($folder)) {
            $x = $folder[1] ?? null;
            $folder = $folder[0];
        }
        $folder = strtr($folder, '/', DS);
        $out = [];
        if ($deep) {
            if (!is_dir($folder)) {
                return $fail;
            }
            $a = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
            $b = $x === 1 || is_string($x) ? \RecursiveIteratorIterator::LEAVES_ONLY : \RecursiveIteratorIterator::SELF_FIRST;
            $c = new \RecursiveIteratorIterator($a, $b);
            if (is_callable($x)) {
                foreach ($c as $v) {
                    $xx = $v->getExtension();
                    $vv = $v->getPathname();
                    if (call_user_func($x, $vv, $v)) {
                        $out[$vv] = $v->isDir() ? 0 : 1;
                    }
                }
            } else {
                foreach ($c as $v) {
                    $xx = $v->getExtension();
                    $vv = $v->getPathname();
                    if ($v->isDir()) {
                        $out[$vv] = 0;
                    } else if ($x === null || $x === 1 || (is_string($x) && strpos(',' . $x . ',', ',' . $xx . ',') !== false)) {
                        $out[$vv] = 1;
                    }
                }
            }
        } else {
            if ($x === 1 || is_string($x)) {
                if ($x === 1) {
                    $x = '*.*';
                } else {
                    $x = '*.{' . $x . '}';
                }
                $files = is(concat(
                    glob($folder . DS . $x, GLOB_BRACE | GLOB_NOSORT),
                    glob($folder . DS . substr($x, 1), GLOB_BRACE | GLOB_NOSORT)
                ), 'is_file');
            } else if ($x === 0) {
                $files = concat(
                    glob($folder . DS . '*', GLOB_ONLYDIR | GLOB_NOSORT),
                    glob($folder . DS . '.*', GLOB_ONLYDIR | GLOB_NOSORT)
                );
            } else {
                $files = concat(
                    glob($folder . DS . '*', GLOB_NOSORT),
                    glob($folder . DS . '.*', GLOB_NOSORT)
                );
            }
            if (is_callable($x)) {
                foreach ($files as $file) {
                    $b = basename($file);
                    if ($b === '.' || $b === '..') {
                        continue;
                    }
                    if (call_user_func($fn, $file, null)) {
                        $out[$file] = is_file($file) ? 1 : 0;
                    }
                }
            } else {
                foreach ($files as $file) {
                    $b = basename($file);
                    if ($b === '.' || $b === '..') {
                        continue;
                    }
                    $out[$file] = is_file($file) ? 1 : 0;
                }
            }
        }
        self::$explore[$id] = $out;
        return !empty($out) ? $out : $fail;
    }

    // Export value to a PHP file
    public static function export($data, string $format = '<?php return %{0}%;') {
        $self = new static;
        $self->content = candy($format, z($data));
        return $self;
    }

    // Open a file
    public static function open(...$lot) {
        return new static(...$lot);
    }

    // Upload a file
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

    public static function sizer(float $size, string $unit = null, $prec = 2) {
        $i = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        $u = $unit ? array_search($unit, $x) : ($size > 0 ? floor($i) : 0);
        $out = round($size / pow(1024, $u), $prec);
        return $out < 0 ? null : trim($out . ' ' . $x[$u]);
    }

}