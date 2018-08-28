<?php

class File extends Genome {

    protected $path = "";
    protected $content = "";
    protected $c = [];

    // Cache!
    private static $inspect = [];
    private static $explore = [];

    const config = [
        'size' => [0, 2097152], // Range of allowed file size(s)
        'extension' => ['txt'] // List of allowed file extension(s)
    ];

    public static $config = self::config;

    // Inspect file path
    public static function inspect($input, $key = null, $fail = false) {
        $id = json_encode(func_get_args());
        if (isset(self::$inspect[$id])) {
            $output = self::$inspect[$id];
            return isset($key) ? Anemon::get($output, $key, $fail) : $output;
        }
        $path = To::path($input);
        $n = Path::N($path);
        $x = Path::X($path);
        $exist = file_exists($path);
        $create = $exist ? filectime($path) : null;
        $update = $exist ? filemtime($path) : null;
        $create_date = $create ? date(DATE_WISE, $create) : null;
        $update_date = $update ? date(DATE_WISE, $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::URL($path),
            'extension' => is_file($path) ? $x : null,
            'create' => $create_date,
            'update' => $update_date,
            'size' => $exist ? self::size($path) : null,
            'is' => [
                'exist' => $exist,
                // Hidden file/folder only
                'hidden' => $n === "" || strpos($n, '.') === 0 || strpos($n, '_') === 0,
                'file' => is_file($path),
                'files' => is_dir($path),
                'folder' => is_dir($path) // alias for `is.files`
            ],
            '_create' => $create,
            '_update' => $update,
            '_size' => $exist ? filesize($path) : null
        ];
        self::$inspect[$id] = $output;
        return isset($key) ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $fail = []) {
        $id = json_encode(func_get_args());
        if (isset(self::$explore[$id])) {
            $output = self::$explore[$id];
            return !empty($output) ? $output : $fail;
        }
        $x = null;
        if (is_array($folder)) {
            $x = isset($folder[1]) ? $folder[1] : null;
            $folder = $folder[0];
        }
        $folder = str_replace('/', DS, $folder);
        $output = [];
        if ($deep) {
            $a = new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS);
            $b = $x === 1 || is_string($x) ? \RecursiveIteratorIterator::LEAVES_ONLY : \RecursiveIteratorIterator::SELF_FIRST;
            $c = new \RecursiveIteratorIterator($a, $b);
            if (is_callable($x)) {
                foreach ($c as $v) {
                    $xx = $v->getExtension();
                    $vv = $v->getPathname();
                    if (call_user_func($x, $vv, $v)) {
                        $output[$vv] = $v->isDir() ? 0 : 1;
                    }
                }
            } else {
                foreach ($c as $v) {
                    $xx = $v->getExtension();
                    $vv = $v->getPathname();
                    if ($v->isDir()) {
                        $output[$vv] = 0;
                    } else if ($x === null || $x === 1 || (is_string($x) && strpos(',' . $x . ',', ',' . $xx . ',') !== false)) {
                        $output[$vv] = 1;
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
                $files = array_filter(array_merge(
                    glob($folder . DS . $x, GLOB_BRACE | GLOB_NOSORT),
                    glob($folder . DS . substr($x, 1), GLOB_BRACE | GLOB_NOSORT)
                ), 'is_file');
            } else if ($x === 0) {
                $files = array_merge(
                    glob($folder . DS . '*', GLOB_ONLYDIR | GLOB_NOSORT),
                    glob($folder . DS . '.*', GLOB_ONLYDIR | GLOB_NOSORT)
                );
            } else {
                $files = array_merge(
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
                        $output[$file] = is_file($file) ? 1 : 0;
                    }
                }
            } else {
                foreach ($files as $file) {
                    $b = basename($file);
                    if ($b === '.' || $b === '..') {
                        continue;
                    }
                    $output[$file] = is_file($file) ? 1 : 0;
                }
            }
        }
        self::$explore[$id] = $output;
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    public static function exist($input, $fail = false) {
        if (is_array($input)) {
            foreach ($input as $v) {
                $v = To::path($v);
                if (file_exists($v)) {
                    return $v;
                }
            }
            return $fail;
        }
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    public static function open(...$lot) {
        return new static(...$lot);
    }

    // Print the file content
    public function read($fail = null) {
        if ($this->path !== false) {
            $content = filesize($this->path) > 0 ? file_get_contents($this->path) : "";
            return $content !== false ? $content : $fail;
        }
        return $fail;
    }

    // Write `$data` before save
    public static function set($data) {
        $self = new static;
        $self->content = $data;
        return $self;
    }

    // Print the file content line by line
    public function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($this->path !== false && filesize($this->path) > 0 && ($hand = fopen($this->path, 'r'))) {
            while (($chunk = fgets($hand, $ch)) !== false) {
                $output .= $chunk;
                if (
                    is_int($stop) && $stop === $i ||
                    is_string($stop) && strpos($chunk, $stop) !== false ||
                    is_array($stop) && strpos($chunk, $stop[0]) === $stop[1] ||
                    is_callable($stop) && call_user_func([$chunk, $i], $output)
                ) break;
                ++$i;
            }
            fclose($hand);
            return rtrim($output);
        }
        return $fail;
    }

    // Reserved
    public function reset() {}

    // Import the exported PHP file
    public function import($fail = []) {
        if ($this->path === false) {
            return $fail;
        }
        return include $this->path;
    }

    // Export value to a PHP file
    public static function export($data, $format = '<?php return %{0}%;') {
        $self = new static;
        $self->content = __replace__($format, z($data));
        return $self;
    }

    // Save the `$data`
    public static function save($consent = null) {
        $this->saveTo($this->path, $consent);
        return $this;
    }

    // Save the `$data` to …
    public function saveTo($path, $consent = null) {
        $this->path = $path;
        $path = To::path($path);
        if (!file_exists($d = Path::D($path))) {
            mkdir($d, 0777, true);
        }
        file_put_contents($path, $this->content);
        if (isset($consent)) {
            chmod($path, $consent);
        }
        return $this;
    }

    // Rename the file/folder
    public function renameTo($name) {
        if ($this->path !== false) {
            $a = Path::B($this->path);
            $b = Path::D($this->path) . DS;
            if ($name !== $a && !file_exists($b . $name)) {
                rename($b . $a, $b . $name);
            }
            $this->path = $b . $name;
        }
        return $this;
    }

    // Move the file/folder to …
    public function moveTo($path = ROOT) {
        if ($this->path !== false) {
            $p = $this->path;
            $path = To::path($path);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if (is_file($p)) {
                $path .= DS . Path::B($p);
            }
            if ($p !== $path) {
                self::open($path)->delete();
                rename($p, $path);
                $this->path = $path;
            }
        }
        return $this;
    }

    // Copy the file/folder to …
    public function copyTo($path = ROOT, $s = '.%{0}%') {
        // TODO: make it possible to copy folder with its content(s)
        $i = 1;
        if ($this->path !== false) {
            $b = DS . Path::B($this->path);
            $o = [];
            foreach ((array) $path as $v) {
                $v = To::path($v);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= $b;
                } else {
                    if (!file_exists($d = Path::D($v))) {
                        mkdir($d, 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy($this->path, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', __replace__($s, $i) . '.$1', $v);
                    copy($this->path, $v);
                    ++$i;
                }
                $o[] = $v;
            }
            // Return sring if singular and array if plural…
            $this->path = count($o) === 1 ? $o[0] : $o;
        }
        return $this;
    }

    // Delete the file
    public function delete() {
        $path = $this->path;
        if ($path !== false) {
            if (is_dir($path)) {
                $a = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
                $b = new \RecursiveIteratorIterator($a, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $v) {
                    if ($v->isFile()) {
                        unlink($v->getPathname());
                    } else {
                        rmdir($v->getPathname());
                    }
                }
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    // Set file permission
    public function consent($consent) {
        if ($this->path !== false) {
            chmod($this->path, $consent);
        }
        return $this;
    }

    // Upload the file
    public static function push($f, $path = ROOT, $fn = null, $fail = false, $c = []) {
        global $language;
        $path = To::path($path);
        $c = !empty($c) ? $c : self::$config;
        if (!is_array($f)) {
            $f = isset($_FILES[$f]) ? $_FILES[$f] : [
                'error' => 4 // No file was uploaded
            ];
        }
        // Sanitize file name
        $f['name'] = To::file($f['name']);
        $x = Path::X($f['name']);
        $e = $language->message_info_file_push;
        // Something goes wrong
        if ($f['error'] > 0 && isset($e[$f['error']])) {
            Message::error($e[$f['error']]);
        } else {
            // Unknown file type
            if (empty($f['type'])) {
                Message::error('file_type');
            }
            // Bad file extension
            $xx = X . implode(X, $c['extension']) . X;
            if (strpos($xx, X . $x . X) === false) {
                Message::error('file_x', '<em>' . $x . '</em>');
            }
            // Too small
            if ($f['size'] < $c['size'][0]) {
                Message::error('file_size.0', self::size($f['size']));
            // Too large
            } else if ($f['size'] > $c['size'][1]) {
                Message::error('file_size.1', self::size($f['size']));
            }
        }
        if (!Message::$x) {
            // Destination not found
            if (!file_exists($path)) {
                Folder::set($path, 0700);
            }
            // Move the uploaded file to the destination folder
            if (!file_exists($path . DS . $f['name'])) {
                // Move…
                $path .= DS . $f['name'];
                move_uploaded_file($f['tmp_name'], $path);
                Message::success('file_push', '<code>' . $f['name'] . '</code>');
                if (is_callable($fn)) {
                    return call_user_func($fn, self::inspect($path));
                }
                return $path;
            }
            Message::error('file_exist', '<code>' . $f['name'] . '</code>');
            return $fail;
        }
        return $fail;
    }

    // Download the file
    public static function pull($file, $mime = null) {
        HTTP::header([
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($file) . '"',
            'Content-Length' => filesize($file),
            'Expires' => 0,
            'Pragma' => 'public'
        ]);
        // Show the browser saving dialog!
        readfile($file);
        exit;
    }

    // Convert file size to …
    public static function size($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? Language::unknown() : trim($output . ' ' . $x[$u]);
    }

    public function __construct($path = true, $c = []) {
        $this->path = file_exists($path) ? realpath($path) : false;
        $this->content = "";
        $this->c = !empty($c) ? $c : self::$config;
        parent::__construct();
    }

}