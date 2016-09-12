<?php

class bii_instance extends bii_shared_item {

	protected $id;
	protected $url;
	protected $name;
	protected $color;
	protected $is_test;
	protected $is_demo;
	protected $is_market;
	protected $is_main;
	protected $version_bii;
	protected $host_bdd;
	protected $user_bdd;
	protected $name_bdd;
	protected $pwd_bdd;
	protected $prefix_bdd;

	function get_bdd() {
		$rpdo_host = $this->host_bdd;
		$rpdo_name = $this->user_bdd;
		$rpdo_user = $this->name_bdd;
		$rpdo_pwd = $this->pwd_bdd;

		$db = new PDO('mysql:host=' . $rpdo_host . ';dbname=' . $rpdo_name, $rpdo_user, $rpdo_pwd);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		return $db;
	}

	static function get_me() {
		$name = get_bloginfo("name");
//		pre($name);
		if (!static::name_exist($name)) {
			global $wpdb;
			$url = get_bloginfo("url");
			$prefix = $table_prefix;
			$wpextended = new wpdbExtended($wpdb);
			$connexionArray = $wpextended->connexionArray();


			$is_demo = 0;
			$is_market = 0;
			if (strpos($url, "demo.biilink.com") !== false || strpos($url, "demo.groupejador.fr") !== false) {
				$is_demo = 1;
			}
			if (strpos($url, "market") !== false) {
				$is_market = 1;
			}
			$arrayUpdate = [
				"url" => $url,
				"name" => $name,
				"is_demo" => $is_demo,
				"is_test" => 0,
				"is_main" => 0,
				"color" => "",
				"is_market" => $is_market,
				"host_bdd" => $connexionArray["host"],
				"user_bdd" => $connexionArray["name"],
				"name_bdd" => $connexionArray["user"],
				"pwd_bdd" => $connexionArray["pwd"],
				"prefix_bdd" => posts::prefix_bdd(),
				"version_bii" => Bii_plugin_version,
			];
			$item = new static();
			$item->insert();
			$item->updateChamps($arrayUpdate);
		} else {
			$item = static::from_name($name);
		}
		if ($item->version_bii != Bii_plugin_version) {
			$arrayUpdate = ["version_bii" => Bii_plugin_version];
			$item->updateChamps($arrayUpdate);
		}
		if ($item->prefix_bdd != $prefix) {
			$arrayUpdate = ["prefix_bdd" => $prefix];
			$item->updateChamps($arrayUpdate);
		}
//		pre($item,"blue");
		return $item;
	}

	static function get_my_id() {
		return static::get_me()->id();
	}

	static function url_exist($url) {
		return static::nb("url = '$url'");
	}

	static function name_exist($name) {
		return static::nb("name = '$name'");
	}

	static function from_url($url) {
		$items = static::all_items("url = '$url'");
		return $items[0];
	}

	static function from_name($name) {
		$items = static::all_items("name = '$name'");
		return $items[0];
	}

	/**
	 * Méthode de colorimétrie
	 * @param int $percent <p>
	 * Le pourcentage d'éclairsissemnt souhaité, par défaut 1
	 * </p>
	 * @return string la couleur en hexadécimal
	 */
	function color($percent = 1) {
		if ($percent == 1) {
			return $this->color;
		} else {
			return static::colourBrightness($this->color, $percent);
		}
	}

	function general_color() {
		return $this->color();
	}

	function emphasis_color() {
		return $this->color(0.8);
	}

	function secondary_color() {
		return $this->color(1.2);
	}

	function secondary_emphasis_color() {
		return $this->color(0.9);
	}

	static public function colourBrightness($hex, $percent) {
		// Work out if hash given
		$hash = '';
		if (stristr($hex, '#')) {
			$hex = str_replace('#', '', $hex);
			$hash = '#';
		}
		/// HEX TO RGB
		$rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
		//// CALCULATE 
		for ($i = 0; $i < 3; $i++) {
			// See if brighter or darker
			if ($percent > 0) {
				// Lighter
				$rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
			} else {
				// Darker
				$positivePercent = $percent - ($percent * 2);
				$rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
			}
			// In case rounding up causes us to go to 256
			if ($rgb[$i] > 255) {
				$rgb[$i] = 255;
			}
		}
		//// RBG to Hex
		$hex = '';
		for ($i = 0; $i < 3; $i++) {
			// Convert the decimal digit to hex
			$hexDigit = dechex($rgb[$i]);
			// Add a leading zero if necessary
			if (strlen($hexDigit) == 1) {
				$hexDigit = "0" . $hexDigit;
			}
			// Append to the hex string
			$hex .= $hexDigit;
		}
		return $hash . $hex;
	}

	function shortcode_name() {
		$name = $this->name();

		$name = str_ireplace("bii-", "Bii ", $name);
		$name = str_ireplace("bii_", "Bii ", $name);
//		pre($name);

		$name = ucwords($name);
		$name = str_ireplace("Bii ", "<strong>Bii</strong>", $name);

		return $name;
	}

	static function sluglangarray($lang = "fr") {
		$slugs = [
			"travel" => "voyages",
			"finance" => "finance-fr",
			"car" => "voiture",
			"art" => "art-fr",
			"beauty" => "beaute",
			"food" => "cuisine",
			"fashion" => "mode",
		];
		if ($lang == "en") {
			$slugs = [
				"travel" => "travel",
				"finance" => "finance",
				"car" => "car",
				"art" => "art",
				"beauty" => "beauty",
				"food" => "food",
				"fashion" => "fashion",
			];
		}
		return $slugs;
	}

	function slug($lang) {
		$name = $this->name();
		$name = str_ireplace("bii-", "", $name);
		$name = str_ireplace("bii_", "", $name);
		$name = str_ireplace("bii", "", $name);
		$lower = strtolower($name);
		$slugs = static::sluglangarray($lang);
		if (isset($slugs[$lower])) {
			return $slugs[$lower];
		} else {
			return "not-found";
		}
	}

	function postURL() {
		return $this->url() . "/wp-json/wc/v1/products";
	}

	function XMLRPCURL() {
		return $this->url() . "/xmlrpc.php";
	}

	function send_request($requestname, $params) {
		$request = xmlrpc_encode_request($requestname, $params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_URL, $this->XMLRPCURL());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		$results = curl_exec($ch);
		curl_close($ch);
		return $results;
	}

	function sayHello() {
		$params = array();
		return $this->send_request('demo.sayHello', $params);
	}

	function createProduct($data) {
		
	}

	protected static function do_curl($url, $data) {
		$datastring = "";

		foreach ($data as $key => $value) {
			$datastring .= $key . '=' . urlencode($value) . '&';
		}
		rtrim($datastring, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($data));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $datastring);

		//execute post
		$result = curl_exec($ch);
		//close connection
		curl_close($ch);
		return $result;
	}

	// */
}