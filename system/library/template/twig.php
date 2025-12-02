<?php
namespace Template;
final class Twig {
	private $data = array();

	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function render($filename, $code = '') {
		if (!$code) {
			$file = DIR_TEMPLATE . $filename . '.twig';

			if (is_file($file)) {
				$code = file_get_contents($file);
			} else {
				throw new \Exception('Error: Could not load template ' . $file . '!');
				exit();
			}
		}

		// Make sure Twig is loaded
		if (!class_exists('Twig_Environment')) {
			if (defined('DIR_STORAGE') && is_file(DIR_STORAGE . 'vendor/autoload.php')) {
				require_once(DIR_STORAGE . 'vendor/autoload.php');
			}
		}

		// initialize Twig environment
		$config = array(
			'autoescape'  => false,
			'debug'       => false,
			'auto_reload' => true,
			'cache'       => DIR_CACHE . 'template/'
		);

		try {
			// Use old Twig v1 classes for backwards compatibility
			$loader1 = new \Twig_Loader_Array(array($filename . '.twig' => $code));
            $loader2 = new \Twig_Loader_Filesystem(DIR_TEMPLATE); // to find further includes
            $loader = new \Twig_Loader_Chain(array($loader1, $loader2));

			$twig = new \Twig_Environment($loader, $config);

			return $twig->render($filename . '.twig', $this->data);
		} catch (Exception $e) {
			trigger_error('Error: Could not load template ' . $filename . '!');
			exit();
		}	
	}	
}
