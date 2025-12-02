<?php
namespace Cache;

class Redis {
	private $expire;
	private $cache;

	public function __construct($expire) {
		$this->expire = $expire;
		
		// Перевірка наявності класу Redis
		if (!class_exists('Redis')) {
			trigger_error('Error: Class \'Redis\' not found! Redis extension must be installed for OpenCart to use the Redis cache driver.', E_USER_ERROR);
		}

		$this->cache = new \Redis();

		// Підключення до Redis-сервера
		if (defined('CACHE_PORT') && CACHE_PORT) {
			// Якщо CACHE_PORT задано, використовуємо TCP-підключення
			$this->cache->pconnect(defined('CACHE_HOSTNAME') ? CACHE_HOSTNAME : '127.0.0.1', defined('CACHE_PORT') ? CACHE_PORT : 6379);
		} else {
			// Якщо CACHE_PORT не задано (або 0), використовуємо UNIX-сокет
			$this->cache->pconnect(defined('CACHE_HOSTNAME') ? CACHE_HOSTNAME : '/var/run/redis/redis.sock'); 
		}

		// Авторизація, якщо встановлено пароль
		if (defined('CACHE_PASSWORD') && CACHE_PASSWORD) {
			$this->cache->auth(CACHE_PASSWORD);
		}

		// Вибір бази даних, якщо встановлено
		if (defined('CACHE_DATABASE') && CACHE_DATABASE) {
			$this->cache->select(CACHE_DATABASE);
		}

	}

	public function get($key) {
		$data = $this->cache->get(CACHE_PREFIX . $key);

		return json_decode($data, true);
	}

	public function set($key, $value) {
		$status = $this->cache->set(CACHE_PREFIX . $key, json_encode($value));

		if ($status) {
			$this->cache->expire(CACHE_PREFIX . $key, $this->expire);
		}

		return $status;
	}

	public function delete($key) {
		$this->cache->delete(CACHE_PREFIX . $key);
	}
	
	public function add($key, $value) {
		return $this->set($key, $value);
	}
	
	public function count() {
		return $this->cache->dbSize();
	}
}