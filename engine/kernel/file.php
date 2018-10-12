<?php

class File extends Genome {

    public $path = null;
    public $content = null;

    // Cache!
    private static $inspect = [];
    private static $explore = [];

    const config = [
        'size' => [0, 2097152], // Range of allowed file size(s)
        'extension' => ['txt'] // List of allowed file extension(s)
    ];

    public static $config = self::config;

    // Inspect file path
    public static function inspect(string $input, $key = null, $fail = false) {
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
            $x = $folder[1] ?? null;
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
        if (isset($this->path)) {
            $content = filesize($this->path) > 0 ? file_get_contents($this->path) : "";
            return $content !== false ? $content : $fail;
        }
        return $fail;
    }

    // Write `$data` before save
    protected function Genome_set(string $data) {
        $this->content = $data;
        return $this;
    }

    // Print the file content line by line
    public function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if (isset($this->path) && filesize($this->path) > 0 && ($hand = fopen($this->path, 'r'))) {
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
        $path = $this->path;
        if (!$path || !is_file($path)) {
            return $fail;
        }
        return include $path;
    }

    // Export value to a PHP file
    public static function export(array $data, $format = '<?php return %{0}%;') {
        $self = new static;
        $self->content = replace($format, z($data));
        return $self;
    }

    // Save the `$data`
    public function save($consent = null) {
        $this->saveTo($this->path, $consent);
    }

    // Save the `$data` to …
    public function saveTo(string $path, $consent = null) {
        $this->path = $path;
        $path = To::path($path);
        if (!file_exists($d = dirname($path))) {
            mkdir($d, 0775, true);
        }
        file_put_contents($path, $this->content);
        if (isset($consent)) {
            chmod($path, $consent);
        }
        return $path;
    }

    // Rename the file/folder
    public function renameTo(string $name) {
        $path = $this->path;
        if (isset($path)) {
            $b = basename($path);
            $d = dirname($path) . DS;
            $v = $d . $name;
            if ($name !== $b && !file_exists($v)) {
                rename($path, $v);
            }
            $this->path = $v;
        }
        return [$path, $v];
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

    // Copy the file/folder to … (folder)
    public function copyTo(string $folder = ROOT, string $pattern = '%{name}%.%{i}%.%{extension}%') {
        $i = 1;
        $path = $this->path;
        $out = [];
        if (isset($path)) {
            $b = basename($path);
            // Copy folder
            if (is_dir($path)) {
                foreach (self::explore([$path, 1], true, []) as $k => $v) {
                    $dir = dirname($folder . DS . $b . DS . str_replace($path . DS, "", $k));
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    $out = array_replace($out, self::open($k)->copyTo($dir, $pattern));
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
                if (!file_exists($v)) {
                    if (copy($path, $v)) {
                        $out[$path] = $v;
                    }
                    $i = 1;
                } else if ($pattern) {
                    $v = dirname($v) . DS . replace($pattern, [
                        'name' => pathinfo($v, PATHINFO_FILENAME),
                        'i' => $i,
                        'extension' => pathinfo($v, PATHINFO_EXTENSION)
                    ]);
                    if (copy($path, $v)) {
                        $out[$path] = $v;
                    }
                    ++$i;
                } else {
                    if (copy($path, $v)) {
                        $out[$path] = $v;
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
                    $out[$p] = 1;
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
            $out[$path] = 1;
        }
        return $out;
    }

    // Set file permission
    public function consent($consent) {
        $path = $this->path;
        if (isset($path)) {
            chmod($path, $consent);
        }
        return $path;
    }

    // Upload a file
    public static function push($name, string $path = ROOT, $fn = null) {
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
    public static function pull(string $file, $mime = null) {
        HTTP::header([
            'Content-Description' => 'File Transfer',
            'Content-Type' => $mime ?: 'application/octet-stream',
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
        return $output < 0 ? null : trim($output . ' ' . $x[$u]);
    }

    public function __construct($path = true, $c = []) {
        $this->path = file_exists($path) ? realpath($path) : null;
        $this->content = "";
        parent::__construct();
    }

}