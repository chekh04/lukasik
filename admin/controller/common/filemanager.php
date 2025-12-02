public function upload() {
    $this->load->language('common/filemanager');

    $json = array();

    // Check user has permission
    if (!$this->user->hasPermission('modify', 'common/filemanager')) {
        $json['error'] = $this->language->get('error_permission');
    }

    // Make sure we have the correct directory
    if (isset($this->request->get['directory'])) {
        $directory = rtrim(DIR_IMAGE . 'catalog/' . $this->request->get['directory'], '/');
    } else {
        $directory = DIR_IMAGE . 'catalog';
    }

    // Check directory
    if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
        $json['error'] = $this->language->get('error_directory');
    }

    if (!$json) {
        $files = array();

        // Multiple / single uploads
        if (!empty($this->request->files['file']['name']) && is_array($this->request->files['file']['name'])) {
            foreach (array_keys($this->request->files['file']['name']) as $key) {
                $files[] = array(
                    'name'     => $this->request->files['file']['name'][$key],
                    'type'     => $this->request->files['file']['type'][$key],
                    'tmp_name' => $this->request->files['file']['tmp_name'][$key],
                    'error'    => $this->request->files['file']['error'][$key],
                    'size'     => $this->request->files['file']['size'][$key]
                );
            }
        }

        foreach ($files as $file) {
            if (is_file($file['tmp_name'])) {
                $filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
                    $json['error'] = $this->language->get('error_filename');
                }

                // Allowed extensions
                $allowed_ext = array(
                    'jpg', 'jpeg', 'gif', 'png', 'webp'
                );

                if (!in_array(utf8_strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed_ext)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                // Allowed MIME types
                $allowed_mime = array(
                    'image/jpeg',
                    'image/pjpeg',
                    'image/png',
                    'image/x-png',
                    'image/gif',
                    'image/webp',
                    'image/x-webp'
                );

                if (!in_array($file['type'], $allowed_mime)) {
                    $json['error'] = $this->language->get('error_filetype');
                }

                if ($file['size'] > $this->config->get('config_file_max_size')) {
                    $json['error'] = $this->language->get('error_filesize');
                }

                if ($file['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $file['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_upload');
            }

            if (!$json) {
                move_uploaded_file($file['tmp_name'], $directory . '/' . $filename);
            }
        }
    }

    if (!$json) {
        $json['success'] = $this->language->get('text_uploaded');
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
