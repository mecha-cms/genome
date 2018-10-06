<?php

class File extends Genome {

    public $path = "";
    public $content = "";
    public $c = [];

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
                    // `->get(7)`
                    is_int($stop) && $stop === $i ||
                    // `->get('$')`
                    is_string($stop) && strpos($chunk, $stop) !== false ||
                    // `->get(['$', 7])`
                    is_array($stop) && strpos($chunk, $stop[0]) === $stop[1] ||
                    // `->get(function($chunk, $i, $output) {})`
                    is_callable($stop) && call_user_func($stop, $chunk, $i, $output)
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
        if (!file_exists($d = dirname($path))) {
            mkdir($d, 0775, true);
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
            $b = basename($this->path);
            $d = dirname($this->path) . DS;
            if ($name !== $b && !file_exists($d . $name)) {
                rename($d . $b, $d . $name);
            }
            $this->path = $d . $name;
        }
        return $this;
    }

    // Move the file/folder to … (folder)
    public function moveTo($folder = ROOT, $as = null) {
        $path = $this->path;
        if ($path !== false) {
            $b = basename($path);
            $o = [];
            if (is_dir($path)) {
                foreach (self::open($path)->copyTo($folder)->path as $k => $v) {
                    $o[$k] = $v;
                    unlink($k);
                }
                self::open($path)->delete();
                if ($as !== null) {
                    rename($k = $folder . DS . $b, $v = $folder . DS . $as);
                    $o[$k] = $v;
                }
            } else {
                if (!is_dir($folder)) {
                    mkdir($folder, 0775, true);
                }
                if (rename($path, $to = $folder . DS . ($as ?: $b))) {
                    $o = [$path => $to];
                }
            }
            $this->path = $o;
        }
        return $this;
    }

    // Copy the file/folder to … (folder)
    public function copyTo($folder = ROOT, $pattern = '%{name}%.%{i}%.%{extension}%') {
        $i = 1;
        $path = $this->path;
        if ($path !== false) {
            $o = [];
            // Copy folder
            if (is_dir($path)) {
                foreach (self::explore([$path, 1], true, []) as $k => $v) {
                    $dir = dirname($folder . DS . basename($path) . DS . str_replace($path . DS, "", $k));
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    $o = array_replace($o, self::open($k)->copyTo($dir, $pattern)->path);
                }
                $this->path = $o;
                return $this;
            }
            // Copy file
            $b = DS . basename($path);
            foreach ((array) $folder as $v) {
                if (!is_dir($v)) {
                    mkdir($v, 0775, true);
                }
                $v .= $b;
                if (!file_exists($v)) {
                    if (copy($path, $v)) {
                        $o[$path] = $v;
                    }
                    $i = 1;
                } else if ($pattern) {
                    $v = dirname($v) . DS . __replace__($pattern, [
                        'name' => pathinfo($v, PATHINFO_FILENAME),
                        'i' => $i,
                        'extension' => pathinfo($v, PATHINFO_EXTENSION)
                    ]);
                    if (copy($path, $v)) {
                        $o[$path] = $v;
                    }
                    ++$i;
                } else {
                    if (copy($path, $v)) {
                        $o[$path] = $v;
                    }
                }
            }
            $this->path = $o;
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
        return $this;
    }

    // Set file permission
    public function consent($consent) {
        if ($this->path !== false) {
            chmod($this->path, $consent);
        }
        return $this;
    }

    // Upload a file
    public static function push($name, $path = ROOT, $fn = null) {
        $path = rtrim(str_replace('/', DS, $path), DS);
        if (!isset($_FILES[$name])) {
            return 4; // No file was uploaded
        }
        $data = $_FILES[$name];
        if (is_callable($fn)) {
            $data = call_user_func($fn, $data);
        }
        if (file_exists($f = $path . DS . $data['name'])) {
            return false; // File already exists
        } else if (isset($data['error']) && $data['error'] > 0) {
            return $data['error'];
        } else if ($data['size'] > self::$config['size'][1]) {
            return 1; // The uploaded file exceeds the `upload_max_filesize` directive in `php.ini`
        }
        // Destination folder does not exist
        if (!file_exists($path) || !is_dir($path)) {
            mkdir($path, 0775, true); // Create one!
        }
        move_uploaded_file($data['tmp_name'], $f);
        return $f; // There is no error, the file uploaded with success
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