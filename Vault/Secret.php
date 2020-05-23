<?php

namespace Vault;

/**
  * A secret that holds the values from the vault.
  * This calss is usually created by vaults only.
  */
class Secret {

	private $metadata;
	private $data;

	/**
	  * Constructs the secret from the vault data.
	  * @param $data - the data from the vault
	  */
	public function __construct($data) {
		if (is_object($data)) $data = get_object_vars($data);
		if (is_array($data)) {
			foreach ($data AS $key => $value) {
				$this->$key = $value;
			}
		} else {
			echo "What???<br>\n";
			$this->data = $data;
		}
	}

	/**
	  * Returns a value from the secret.
	  * @param string $key - the key of the value to be retrieved.
	  * @return the value or NULL if not set.
	  */
	public function get(string $key) {
		if (isset($this->data->$key)) return $this->data->$key;
		return NULL;
	}

	/**
	  * Returns any metadata - if set - from the vault for this secret
	  * @return the metadata or NULL if not set
	  */
	public function getMeta() {
		return $this->metadata;
	}
}