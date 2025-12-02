<?php
class ControllerExtensionModuleTorgsoftImport extends Controller {
    private $error = array();
    private $log_file;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->log_file = DIR_STORAGE . 'logs/torgsoft.log';
    }

    public function index() {
        $this->load->language('extension/module/torgsoft_import');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Обработка action-параметра (кнопки сверху)
        if (isset($this->request->get['action'])) {
            $action = $this->request->get['action'];

            if ($action === 'import') {
                return $this->import();
            }

            if ($action === 'test') {
                return $this->test();
            }

            if ($action === 'refresh_log') {
                return $this->refreshLog();
            }

            if ($action === 'clear_log') {
                return $this->clearLog();
            }

            if ($action === 'download_log') {
                return $this->downloadLog();
            }
        }

        // Сохранение настроек
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('module_torgsoft_import', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            // если нужно — запуск импорта после сохранения настроек
            // можно выключить, если не нравится автозапуск:
            $this->import(true);
            return;
        }

        $data = array();

        // Тексты из языкового файла
        $texts = array(
            'heading_title',
            'text_edit',
            'text_enabled',
            'text_disabled',
            'text_button_import',
            'text_button_test',
            'text_button_refresh_log',
            'text_button_clear_log',
            'text_button_download_log',
            'text_tab_settings',
            'text_tab_fields',
            'text_tab_log',
            'text_tab_help',
            'text_log_empty',
            'entry_status',
            'entry_xml_path',
            'entry_images_path',
            'entry_brand_fallback',
            'entry_image_limit',
            'entry_update_categories',
            'entry_update_manufacturers',
            'entry_update_products',
            'entry_update_name',
            'entry_update_description',
            'entry_update_price',
            'entry_update_quantity',
            'entry_update_image',
            'entry_update_category',
            'entry_update_manufacturer',
            'entry_update_model',
            'entry_update_attributes',
            'entry_update_special',
            'entry_update_options'
        );

        foreach ($texts as $t) {
            $data[$t] = $this->language->get($t);
        }

        // Ошибки / успех
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Хлебные крошки
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=module',
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                'extension/module/torgsoft_import',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        // URL формы настроек
        $data['action'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        // Кнопки действий
        $data['import_url'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'] . '&action=import',
            true
        );

        $data['test_url'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'] . '&action=test',
            true
        );

        $data['refresh_log_url'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'] . '&action=refresh_log',
            true
        );

        $data['clear_log_url'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'] . '&action=clear_log',
            true
        );

        $data['download_log_url'] = $this->url->link(
            'extension/module/torgsoft_import',
            'user_token=' . $this->session->data['user_token'] . '&action=download_log',
            true
        );

        // Значения по умолчанию для настроек
        $defaults = array(
            'module_torgsoft_import_status'              => 1,
            'module_torgsoft_import_xml_path'           => '',
            'module_torgsoft_import_images_path'        => '',
            'module_torgsoft_import_brand_fallback'     => '',
            'module_torgsoft_import_image_limit'        => 10,
            'module_torgsoft_import_update_categories'  => 1,
            'module_torgsoft_import_update_manufacturers' => 1,
            'module_torgsoft_import_update_products'    => 1,
            'module_torgsoft_import_update_name'        => 1,
            'module_torgsoft_import_update_description' => 1,
            'module_torgsoft_import_update_price'       => 1,
            'module_torgsoft_import_update_quantity'    => 1,
            'module_torgsoft_import_update_image'       => 1,
            'module_torgsoft_import_update_category'    => 1,
            'module_torgsoft_import_update_manufacturer'=> 1,
            'module_torgsoft_import_update_attributes'  => 1,
            'module_torgsoft_import_update_special'     => 1,
            'module_torgsoft_import_update_options'     => 1
        );

        foreach ($defaults as $k => $v) {
            if (isset($this->request->post[$k])) {
                $data[$k] = $this->request->post[$k];
            } else {
                $cfg = $this->config->get($k);
                $data[$k] = ($cfg !== null ? $cfg : $v);
            }
        }

        // Пример ссылки для CRON (если потом сделаешь отдельный маршрут под cron)
        $data['module_torgsoft_import_cron_url'] =
            HTTP_CATALOG . 'index.php?route=extension/module/torgsoft_import/cron&key=' .
            md5(HTTP_CATALOG . $this->session->data['user_token']);

        // Предпросмотр лога
        $data['log_created'] = file_exists($this->log_file);
        if ($data['log_created']) {
            $data['log_content'] = htmlspecialchars(
                file_get_contents($this->log_file),
                ENT_QUOTES,
                'UTF-8'
            );
        } else {
            $data['log_content'] = '';
        }

        $data['user_token'] = $this->session->data['user_token'];

        // Общие части шаблона
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/module/torgsoft_import', $data)
        );
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/torgsoft_import')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Запуск импорта
     *
     * @param bool $after_save – вызывается ли после сохранения настроек (для будущей логики, если нужно)
     */
    public function import($after_save = false) {
        $this->load->language('extension/module/torgsoft_import');

        if (!$this->user->hasPermission('modify', 'extension/module/torgsoft_import')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect(
                $this->url->link(
                    'extension/module/torgsoft_import',
                    'user_token=' . $this->session->data['user_token'],
                    true
                )
            );
            return;
        }

        $this->load->model('extension/module/torgsoft_import');

        $start = microtime(true);

        // ВАЖНО: твой runImport() сейчас НИЧЕГО не возвращает (void),
        // поэтому НЕ трогаем $result['added'] и т.п., не будет Notice'ов.
        $this->model_extension_module_torgsoft_import->runImport();

        $elapsed = round(microtime(true) - $start, 2);

        // Простое сообщение об успешном импорте
        // Если в языковом файле есть text_import_done — можно использовать его.
        $text_import_done = $this->language->get('text_import_done');
        if ($text_import_done == 'text_import_done') {
            // ключ не определён в языке — пишем руками
            $msg = 'Імпорт з TorgSoft завершено за ' . $elapsed . ' сек. Деталі дивіться в torgsoft.log.';
        } else {
            // если есть ключ, подставим только время
            $msg = sprintf($text_import_done, $elapsed);
        }

        $this->session->data['success'] = $msg;

        $this->response->redirect(
            $this->url->link(
                'extension/module/torgsoft_import',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );
    }

    /**
     * Тест – безопасный, даже если в модели нет метода test()
     */
    public function test() {
        $this->load->model('extension/module/torgsoft_import');

        if (method_exists($this->model_extension_module_torgsoft_import, 'test')) {
            $ok = $this->model_extension_module_torgsoft_import->test();
            $this->session->data['success'] = $ok
                ? 'Тест пройден: файл читается, структура распознана.'
                : 'Тест не пройден: проверьте путь к файлу и права.';
        } else {
            // На случай, если test() в модели не реализован
            $this->session->data['success'] = 'Тест: метод test() в моделі не реалізовано, але модуль підключено.';
        }

        $this->response->redirect(
            $this->url->link(
                'extension/module/torgsoft_import',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );
    }

    public function refreshLog() {
        // Просто перезагружаем страницу модуля — лог перечитается
        $this->response->redirect(
            $this->url->link(
                'extension/module/torgsoft_import',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );
    }

    public function clearLog() {
        if (file_exists($this->log_file)) {
            @unlink($this->log_file);
        }

        $this->session->data['success'] = 'Лог очищен.';

        $this->response->redirect(
            $this->url->link(
                'extension/module/torgsoft_import',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );
    }

    public function downloadLog() {
        if (!file_exists($this->log_file)) {
            $this->session->data['error_warning'] = 'Лог не найден.';
            $this->response->redirect(
                $this->url->link(
                    'extension/module/torgsoft_import',
                    'user_token=' . $this->session->data['user_token'],
                    true
                )
            );
            return;
        }

        $this->response->addHeader('Content-Type: text/plain; charset=UTF-8');
        $this->response->addHeader('Content-Disposition: attachment; filename="torgsoft.log"');

        $this->response->setOutput(file_get_contents($this->log_file));
    }
}
