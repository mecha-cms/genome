<?php

class File extends Genome {

    protected static $path = "";
    protected static $content = "";

    public static $config = [
        'sizes' => [0, 2097152], // Range of allowed file size(s)
        'extensions' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    public static function inspect($path, $key = null, $fail = false) {
        $path = To::path($input);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = self::T($path);
        $update_date = $update !== null ? date('Y-m-d H:i:s', $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::url($path),
            'extension' => is_file($path) ? $x : null,
            'update' => $update_date,
            'size' => file_exists($path) ? self::size($path) : null,
            'is' => [
                // hidden file/folder only
                'hidden' => strpos($n, '__') === 0 || strpos($n, '.') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            ],
            '__update' => $update,
            '__size' => file_exists($path) ? filesize($path) : null
        ];
        return $key !== null ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $flat = false, $fail = false) {
        $folder = To::path($folder);
        $files = array_merge(
            glob($folder . DS . '*', GLOB_NOSORT),
            glob($folder . DS . '.*', GLOB_NOSORT)
        );
        $output = [];
        foreach ($files as $file) {
            $b = Path::B($file);
            if ($b && $b !== '.' && $b !== '..') {
                if (is_dir($file)) {
                    if (!$flat) {
                        $output[$file] = $deep ? self::explore($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, self::explore($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    public static function exist($input, $fail = false) {
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    public static function open($input) {
        self::$path = To::path($input);
        self::$content = "";
        return new static;
    }

    // Append `$data` to the file content
    public static function append($data) {
        if (is_array(self::$content)) {
            self::$content = array_merge(self::$content, $data);
            return new static;
        }
        self::$content = file_get_contents(self::$path) . $data;
        return new static;
    }

    // Prepend `$data` to the file content
    public static function prepend($data) {
        if (is_array(self::$content)) {
            self::$content = array_merge($data, self::$content);
            return new static;
        }
        self::$content = $data . file_get_contents(self::$path);
        return new static;
    }

    // Print the file content
    public static function read($fail = "") {
        return file_exists(self::$path) ? file_get_contents(self::$path) : $fail;
    }

    // Print the file content line by line
    public static function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($hand = fopen(self::$path, 'r')) {
            while (($chunk = fgets($hand, $ch)) !== false) {
                if (is_int($stop) && $stop === $i) break;
                $output .= $chunk;
                $i++;
                if (is_string($stop) && strpos($chunk, $stop) !== false || is_array($stop) && strpos($chunk, $stop[0]) === $stop[1]) break;
            }
            fclose($hand);
            return rtrim($output);
        }
        return $fail;
    }

    // Write `$data` before save
    public static function write($data) {
        self::$content = $data;
        return new static;
    }

    // Import the exported PHP file
    public static function import($fail = []) {
        if (!file_exists(self::$path)) return $fail;
        return include self::$path;
    }

    // Export value to a PHP file
    public static function export($data, $format = '<?php return %s;') {
        $r = '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')#';
        $data = preg_split($r, json_encode($data), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach ($data as $v) {
            if ($v[0] === '"' || $v[0] === "'") {
                $output .= $v;
            } else {
                $output .= str_replace(['{', '}', ':'], ['[', ']', '=>'], $v);
            }
        }
        self::$content = sprintf($format, $output);
        return new static;
    }

    // Serialize `$data` before save
    public static function serialize($data) {
        self::$content = serialize($data);
        return new static;
    }

    // Unserialize the serialized `$data` to output
    public static function unserialize($fail = []) {
        if (file_exists(self::$path)) {
            $data = file_get_contents(self::$path);
            return __is_serialize__($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    public static function save($consent = null) {
        self::saveTo(self::$path, $consent);
        return new static;
    }

    // Save the `$data` to ...
    public static function saveTo($input, $consent = null) {
        $input = To::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $hand = fopen($input, 'w') or die('Cannot open file: ' . $input);
        fwrite($hand, self::$content);
        fclose($hand);
        if ($consent !== null) {
            chmod($input, $consent);
        }
        self::$path = $input;
        return new static;
    }

    // Rename the file/folder
    public static function renameTo($name) {
        if (file_exists(self::$path)) {
            $a = Path::B(self::$path);
            $b = Path::D(self::$path) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            self::$path = $b . $name;
        }
        return new static;
    }

    // Move the file/folder to ...
    public static function moveTo($target = ROOT) {
        if (file_exists(self::$path)) {
            $target = To::path($input);
            if (is_dir($target) && is_file(self::$path)) {
                $target .= DS . Path::B(self::$path);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename(self::$path, $target);
            self::$path = $target;
        }
        return new static;
    }

    // Copy the file/folder to ...
    public static function copyTo($target = ROOT, $s = '.%s') {
        $i = 1;
        if (file_exists(self::$path)) {
            foreach ((array) $target as $v) {
                $v = To::path($input);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B(self::$path);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy(self::$path, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', sprintf($s, $i) . '.$1', $v);
                    copy(self::$path, $v);
                    $i++;
                }
                self::$path = $v;
            }
        }
        return new static;
    }

    // Delete the file
    public static function delete() {
        if (file_exists(self::$path)) {
            if (is_dir(self::$path)) {
                $a = new RecursiveDirectoryIterator(self::$path, FilesystemIterator::SKIP_DOTS);
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir(self::$path);
            } else {
                unlink(self::$path);
            }
        }
    }

    // Get file modification time
    public static function T($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    public static function consent($consent) {
        chmod(self::$path, $consent);
        return new static;
    }

    // Upload the file
    public static function upload($file, $target = ROOT, $fn = null, $fail = false) {
        $target = To::path($input);
        // Create a safe file name
        $file['name'] = To::safe('file.name', $file['name']);
        $x = Path::X($file['name']);
        $e = Language::notify_file_upload();
        // Something goes wrong
        if ($file['error'] > 0 && isset($e[$file['error']])) {
            Notify::error($e[$file['error']]);
        } else {
            // Unknown file type
            if (empty($file['type'])) {
                Notify::error('file_type');
            }
            // Bad file extension
            $x_ok = X . implode(X, self::$config['extensions']) . X;
            if (strpos($x_ok, X . $x . X) === false) {
                Notify::error('file_extension', $x);
            }
            // Too small
            if ($file['size'] < self::$config['sizes'][0]) {
                Notify::error('file_size.0', self::size($file['size']));
            }
            // Too large
            if ($file['size'] > self::$config['sizes'][1]) {
                Notify::error('file_size.1', self::size($file['size']));
            }
        }
        if (!Notify::errors()) {
            // Destination not found
            if (!file_exists($target)) Folder::create($target);
            // Move the upload(ed) file to the destination folder
            if (!file_exists($target . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $target . DS . $file['name']);
                // Create public asset URL to be hooked on file uploaded
                $file['url'] = To::url($target) . '/' . $file['name'];
                Notify::success('file_upload', $file['name']);
                if (is_callable($fn)) {
                    return call_user_func($fn, $file);
                }
                return $target . DS . $file['name'];
            }
            Notify::error('file_exist', $file['name']);
            return $fail;
        }
        return $fail;
    }

    // Convert file size to ...
    public static function size($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? Language::get('unknown') : trim($output . ' ' . $x[$u]);
    }

}