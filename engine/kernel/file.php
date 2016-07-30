<?php

class File extends DNA {

    protected $open = "";
    protected $cache = "";

    public $config = [
        'file_size_min_allow' => 0, // Minimum allowed file size
        'file_size_max_allow' => 2097152, // Maximum allowed file size
        'file_extension_allow' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    public function inspect($path, $key = null, $fail = false) {
        $path = URL::path($path);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = $this->T($path);
        $update_date = $update !== null ? date('Y-m-d H:i:s', $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => Path::url($path),
            'extension' => is_file($path) ? $x : null,
            'update_raw' => $update,
            'update' => $update_date,
            'size_raw' => file_exists($path) ? filesize($path) : null,
            'size' => file_exists($path) ? $this->size($path) : null,
            'is' => [
                // hidden file/folder only
                'hidden' => strpos($n, '__') === 0 || strpos($n, '.') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            ]
        ];
        return $key !== null ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public function explore($folder = ROOT, $deep = false, $flat = false, $fail = false) {
        $folder = rtrim(URL::path($folder), DS);
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
                        $output[$file] = $deep ? $this->explore($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, $this->explore($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    public function exist($input, $fail = false) {
        $input = URL::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    public function open($input) {
        $this->open = URL::path($input);
        return $this;
    }

    // Append `$data` to the file content
    public function append($data) {
        $this->cache = file_get_contents($this->open) . $data;
        return $this;
    }

    // Prepend `$data` to the file content
    public function prepend($data) {
        $this->cache = $data . file_get_contents($this->open);
        return $this;
    }

    // Print the file content
    public function read($fail = "") {
        return file_exists($this->open) ? file_get_contents($this->open) : $fail;
    }

    // Print the file content line by line
    public function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($hand = fopen($this->open, 'r')) {
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
    public function write($data) {
        $this->cache = $data;
        return $this;
    }

    // Serialize `$data` before save
    public function serialize($data) {
        $this->cache = serialize($data);
        return $this;
    }

    // Unserialize the serialized `$data` to output
    public function unserialize($fail = []) {
        if (file_exists($this->open)) {
            $data = file_get_contents($this->open);
            return Is::serialize($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    public function save($consent = null) {
        $this->saveTo($this->open, $consent);
        return $this;
    }

    // Save the `$data` to ...
    public function saveTo($input, $consent = null) {
        $input = URL::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $hand = fopen($input, 'w') or die('Cannot open file: ' . $input);
        fwrite($hand, $this->cache);
        fclose($hand);
        if ($consent !== null) {
            chmod($input, $consent);
        }
        $this->open = $input;
        return $this;
    }

    // Rename the file/folder
    public function renameTo($name) {
        if (file_exists($this->open)) {
            $a = Path::B($this->open);
            $b = Path::D($this->open) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            $this->open = $b . $name;
        }
        return $this;
    }

    // Move the file/folder to ...
    public function moveTo($target = ROOT) {
        if (file_exists($this->open)) {
            $target = URL::path($target);
            if (is_dir($target) && is_file($this->open)) {
                $target .= DS . Path::B($this->open);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename($this->open, $target);
            $this->open = $target;
        }
        return $this;
    }

    // Copy the file/folder to ...
    public function copyTo($target = ROOT, $s = '.%s') {
        $i = 1;
        if (file_exists($this->open)) {
            foreach ((array) $target as $v) {
                $v = URL::path($v);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B($this->open);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy($this->open, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', sprintf($s, $i) . '.$1', $v);
                    copy($this->open, $v);
                    $i++;
                }
                $this->open = $v;
            }
        }
        return $this;
    }

    // Delete the file
    public function delete() {
        if (file_exists($this->open)) {
            if (is_dir($this->open)) {
                $a = new RecursiveDirectoryIterator($this->open, FilesystemIterator::SKIP_DOTS);
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir($this->open);
            } else {
                unlink($this->open);
            }
        }
    }

    // Get file modify time
    public function T($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    public function consent($consent) {
        chmod($this->open, $consent);
        return $this;
    }

    // Upload the file
    public function upload($file, $target = ROOT, $fn = null, $fail = false) {
        $target = URL::path($target);
        $errors = [
            'There is no error, the file uploaded with success.',
            'The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>.',
            'The uploaded file exceeds the <code>MAX_FILE_SIZE</code> directive that was specified in the <abbr title="Hyper Text Markup Language">HTML</abbr> form.',
            'The uploaded file was only partially uploaded.',
            'No file was uploaded.',
            '?',
            'Missing a temporary folder.',
            'Failed to write file to disk.',
            'A <abbr>PHP</abbr> extension stopped the file upload.'
        ];
        // Create a safe file name
        $file['name'] = To::safe('name.file', $file['name']);
        $x = Path::X($file['name']);
        // Something goes wrong
        if ($file['error'] > 0 && isset($errors[$file['error']])) {
            Notify::error($errors[$file['error']]);
        } else {
            // Unknown file type
            if (empty($file['type'])) {
                Notify::error('Unknown file type.');
            }
            // Bad file extension
            $x_ok = X . implode(X, $this->config['file_extension_allow']) . X;
            if (strpos($x_ok, X . $x . X) === false) {
                Notify::error('Extension ' . $x . ' is not allowed.');
            }
            // Too small
            if ($file['size'] < $this->config['file_size_min_allow']) {
                Notify::error('File too small.');
            }
            // Too large
            if ($file['size'] > $this->config['file_size_max_allow']) {
                Notify::error('File too large.');
            }
        }
        if (!Notify::errors()) {
            // Destination not found
            if (!file_exists($target)) Folder::create($target);
            // Move the uploaded file to the destination folder
            if (!file_exists($target . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $target . DS . $file['name']);
                // Create public asset URL to be hooked on file uploaded
                $url = Path::url($target) . '/' . $file['name'];
                Notify::success('File uploaded.');
                if (is_callable($fn)) {
                    return call_user_func($fn, $file['name'], $file['type'], $file['size'], $url);
                }
                return $target . DS . $file['name'];
            }
            Notify::error('File already exist.');
            return $fail;
        }
        return $fail;
    }

    // Convert file size to ...
    public function size($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? 'Unknown' : trim($output . ' ' . $x[$u]);
    }

}