<?php

namespace Vault;

/**
  * Base class for all vault implementations here.
  * It provides the logging mechanism only.
  */
class BaseVault implements Vault {

	protected $logger;
	private   $prefix;

	/**
	  * Default constructor that only takes an optiona logger object.
	  * @param Logger $logger - the logger object.
	  */
	public function __construct(Logger $logger = NULL) {
		$this->logger   = $logger;
	}

	/**
	  * Returns the secret at the given path.
	  * Must be overridden by subclasses.
	  * @param string $path - an arbitrary path that uniquely identifies a secret in the vault.
	  * @return the Secret
	  * @throws an exception when the secret cannot be found or retrieved.
	  */
	public function getSecret(string $path) {
		throw new VaultException(get_class().'::getSecret() must be implemented.', VAULT_ERR_INTERNAL);
	}

	/**
	  * Set the logger and log all information via this object.
	  * @param Logger - the logging object.
	  */
	public function setLogger(Logger $logger) {
		$this->logger = $logger;
	}

	/**
	  * Log in debug level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function debug(string $s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$this->logger->debug($this->getLoggerPrefix().$s, $object);
		}
	}

	/**
	  * Log in warn level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function warn(string $s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$this->logger->warn($this->getLoggerPrefix().$s, $object);
		}
	}

	/**
	  * Log in info level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function info(string $s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$this->logger->info($this->getLoggerPrefix().$s, $object);
		}
	}

	/**
	  * Log in error level.
	  * @see Logger interface
	  * @param $s      - the string to be logged
	  * @param $object - the object to be logged
	  */
	protected function error(string $s, $object = NULL) {
		if ($this->logger != NULL) {
			$object = self::cleanObject($object);
			$this->logger->error($this->getLoggerPrefix().$s, $object);
		}
	}

	/**
	  * Returns a prefix for the logging string.
	  * Default implementation uses the short class name.
	  * @return string for prefixing logging strings.
	  */
	protected function getLoggerPrefix() {
		if ($this->prefix == NULL) {
			$helper = new \ReflectionClass($this);
			$this->prefix = '['.$helper->getShortName().'] ';
		}
		return $this->prefix;
	}

	/**
	  * Copies the given object an redacts any sensitive strings, such as
	  * passwords, usernames and tokens. The decision is based on object
	  * attribute names. Arrays are not redacted (!).
	  * @param object $o - the object to clean
	  * @return a cleaned object
	  */
	public static function cleanObject($o) {
		if ($o == NULL) return NULL;
		if (!is_object($o)) return $o;
		$copy = new \stdClass;
		foreach (get_object_vars($o) AS $key => $value) {
			if (is_object($value)) {
				$copy->$key = self::cleanObject($value);
			} else if (is_string($key)) {
				switch ($key) {
				case 'username':
				case 'password':
				case 'passwd':
				case 'client_token':
				case 'accessor':
					$copy->$key = '***REDACTED***';
					break;
				default:
					if (strpos($key, 'pass') !== FALSE) {
						$copy->$key = '***REDACTED***';
					} else {
						$copy->$key = $value;
					}
				}
			} else {
				$copy->$key = $value;
			}
		}
		return $copy;
	}
}
