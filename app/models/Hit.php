<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Hit extends Model {

	/**
	 * [protected description]
	 * @var [type]
	 */
	protected $table;

	/**
	 * [__construct description]
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'wpdriftio_hits';
	}
}
