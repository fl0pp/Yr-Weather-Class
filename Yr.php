<?

/**
 * Yr class
 * For getting weather information from the The Norwegian Meteorological Institute
 * @author Marius S. Eriksrud <marius@konsept-it.no>
 * @version 1.0
 * @copyright Konsept-IT, 2012
 */
class Yr {
	
	/**
	 * Yr.no URL
	 * Eks1: http://www.yr.no/place/Norway/Østfold/Halden/Halden/varsel.xml
	 * Eks2: /place/Norway/Østfold/Halden/Halden/
	 * Eks3: http://www.yr.no/sted/Norway/Østfold/Halden/Halden
	 */
	private $url;
	
	/**
	 * Language
	 * ISO 639-1
	 */
	private $language;
	
	/**
	 * YR URL for a specific location
	 * Eks: /Norway/Østfold/Halden/Halden/
	 */
	private $location_url;
	
	/**
	 * Cache timeout in seconds
	 */
	private $cache_timeout;
	
	/**
	 * Cache directory
	 */
	private $cache_directory;
	
	/**
	 * Yr XML
	 */
	private $xml;
	
	/**
	 * Ouput date format
	 * @link http://php.net/manual/en/datetime.format.php
	 */
	private $date_format;
	
	/**
	 * @param $url string Yr.no URL
	 * @param @options array Set options. Ex: array('cache_timeout' => 3600, 'cache_directory' => '/my/cache/dir') If cache_directory is ommitted, caching is disabled.
	 */
	public function __construct($url = false, $options = array()) {
		// Set default language Norsk Bokmål
		$this->language = 'nb';
		// Default cache timeout
		$this->cache_timeout = 3600; // One hour
		$this->cache_directory = false;
		// Default date format
		$this->date_format = 'Y-m-d H:i:s';
		
		if(array_key_exists('cache_directory', $options)) $this->cache_directory = $options['cache_directory'];
		if(array_key_exists('cache_timeout', $options)) $this->cache_timeout = $options['cache_timeout'];
		if(array_key_exists('date_format', $options)) $this->date_format = $options['date_format'];

		$this->url = $url;
		if(!$this->url) throw new Exception('Class Yr initiated with no URL. Ex: \$yr = new Yr(\'http://www.yr.no/sted/Norway/Østfold/Halden/Halden\');');
		$this->checkURLFormat();
	}
	
	private function checkURLFormat() {
		if(!preg_match("/^(http\:\/\/(www\.)?yr\.no)?\/(place|sted|stad)([a-z\/æøåÆØÅ]+)(varsel\.xml)?$/i", $this->url, $regs)) throw new Exception('Invalid URL');
		$this->determineLanguage($regs[3]);
		$this->location_url = (substr($regs[4], -1) == '/' ? $regs[4] : substr($regs[4], 0, strlen($regs[4])-1));
	}
	
	/**
	 * Determine language from Yr URL
	 * @param string string
	 * @return null
	 */
	private function determineLanguage($string) {
		if($string == 'sted') $this->setLanguage('nb');
		elseif($string == 'place') $this->setLanguage('en');
		elseif($string == 'stad') $this->setLanguage('nn');
	}
	
	/**
	 * Return string to build YR URL
	 * @return string
	 */
	private function getYrLanguageString() {
		switch($this->language) {
			case 'nb':
				return 'sted';
				break;
			case 'nn':
				return 'stad';
				break;
			case 'en':
				return 'place';
				break;
			default:
				return 'sted';
				break;
		}
	}
	
	/**
	 * Download XML or load from cache, and init XML DOM
	 */
	private function processRequest() {
		$url = 'http://www.yr.no/'.$this->getYrLanguageString().$this->location_url.'varsel.xml';
		$contents = $this->getCache($url);
		if(!$contents) {
			if(in_array('curl', get_loaded_extensions()) || !function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$contents = $this->validateXML(curl_exec($ch));
				if(curl_errno($ch)) throw new Exception(curl_error($ch));
				curl_close($ch);
			} elseif(ini_get('allow_url_fopen') === true) {
				$contents = $this->validateXML(file_get_contents($url));
			} else {
				throw new Exception('Curl and allow_url_fopen is disabled in PHP. Could not get contents from yr.no');
			}
			$this->setCache($url, $contents);
		}
		$this->xml = simplexml_load_string($contents);
		return true;
	}
	
	/**
	 * Save XML to cache
	 * @param string $url YR URL to save
	 * @param string $contents XML contents
	 */
	private function setCache($url, $contents) {
		if($this->cache_directory) {
			$cache_filename = md5($url);
			if(!file_exists($this->cache_directory)) throw new Exception('Cache directory does not exists ('.$this->cache_directory.')');
			$fp = fopen($this->cache_directory.'/'.$cache_filename, 'w');
			if(!$fp) throw new Exception('Cache file not writable ('.$this->cache_directory.'/'.$cache_filename.')');
			fputs($fp, $contents);
			fclose($fp);
		}
	}
	
	/**
	 * Load contents from cache
	 * @param $url string YR URL to load
	 * @return string|false
	 */
	private function getCache($url) {
		if($this->cache_directory) {
			$cache_path = $this->cache_directory.'/'.md5($url);
			if(!file_exists($cache_path)) return false;
			if(filemtime($cache_path) < (time()-$this->cache_timeout)) return false;
			$fp = fopen($cache_path, 'r');
			if(!$fp) throw new Exception('Could not read cache file ('.$this->cache_path.')');
			$contents = fread($fp, filesize($cache_path));
			fclose($fp);
			return $contents;
		} else {
			return false;
		}
	}
	
	/**
	 * Validates XML
	 * @param $contents string XML contents
	 * @return string
	 */
	private function validateXML($contents) {
		$xml = simplexml_load_string($contents);
		if($xml === false) throw new Exception('Return contents is not valid XML');
		return $contents;
	}
	
	/**
	 * Format date output
	 * @param $date string Input date string from YR XML
	 * @param $format string Define a date format, if false default is used
	 * @return datetime
	 */
	private function formatDate($date, $format = false) {
		$date = new \DateTime($date);
		return $date->format(($format ? $format : $this->date_format));
	}
	
	/**
	 * Set Language (ISO 639-1)
	 * Available languages:
	 *  - Norwegian Bokmål: nb
	 *  - Norwegian Nynorsk: nn
	 *  - English: en
	 * @param string language
	 * @return null
	 */
	public function setLanguage($language) {
		$languages = array('nn', 'nb', 'en');
		if(!in_array($language, $languages)) throw new Exception('Language not valid');
		$this->language = $language;
	}
	
	/**
	 * Get language
	 * @return string nb|nn|en
	 */
	public function getLanguage() {
		return $this->language;
	}
	
	/**
	 * Get location name
	 * @return string
	 */
	public function getName() {
		$this->processRequest();
		return $this->xml->location[0]->name;
	}
	
	/**
	 * Get location type
	 * @return string
	 */
	public function getType() {
		$this->processRequest();
		return $this->xml->location[0]->type;
	}
	
	/**
	 * Get location county
	 * @return string
	 */
	public function getCountry() {
		$this->processRequest();
		return $this->xml->location[0]->country;
	}
	
	/**
	 * Get location timezone
	 * Array
		(
		    [id] => Europe/Oslo
		    [utcoffsetMinutes] => 120
		)
	 * @return array
	 */
	public function getTimezone() {
		$this->processRequest();
		$data = array();
		$data['id'] = (string) $this->xml->location[0]->timezone['id'];
		$data['utcoffsetMinutes'] = (string) $this->xml->location[0]->timezone['utcoffsetMinutes'];
		return $data;
	}
	
	/**
	 * Get location info
	 * Array
		(
		    [altitude] => 7
		    [latitude] => 59.1245978642888
		    [longitude] => 11.3873828303074
		    [geobase] => ssr
		    [geobaseid] => 34643
		)
	 * @return array
	 */
	public function getLocation() {
		$this->processRequest();
		$data = array();
		$data['altitude'] = (string) $this->xml->location[0]->location['altitude'];
		$data['latitude'] = (string) $this->xml->location[0]->location['latitude'];
		$data['longitude'] = (string) $this->xml->location[0]->location['longitude'];
		$data['geobase'] = (string) $this->xml->location[0]->location['geobase'];
		$data['geobaseid'] = (string) $this->xml->location[0]->location['geobaseid'];
		return $data;
	}
	
	/**
	 * Get Sunrise time
	 * @param $format string|false Dateformat, if false use default
	 * @return string
	 */
	public function getSunrise($format = false) {
		$this->processRequest();
		return $this->formatDate($this->xml->sun['rise'], $format);
	}
	
	/**
	 * Get Sunset time
	 * @param $format string|false Dateformat, if false use default
	 * @return string
	 */
	public function getSunset($format = false) {
		$this->processRequest();
		return $this->formatDate($this->xml->sun['set'], $format);
	}
	
	/**
	 * Get Raw forecast from Yr.no
	 * Converts XML to array
	 * @return array
	 */
	private function getForecast() {
		$this->processRequest();
		$data = array();
		//print_r($this->xml->forecast->tabular);
		foreach($this->xml->forecast->tabular->time as $t) {
			$weekday = new \DateTime((string) $t['from']);
			$weekday_id = date('w', $weekday->getTimestamp());
			$weekday_name = date('l', $weekday->getTimestamp());
			
			$data[] = array(
				'from' => (string) $t['from'],
				'to' => (string) $t['to'],
				'weekday_id' => $weekday_id, // 0 = Sunday, 6 = Saturday
				'weekday_name' => $weekday_name,
				'period' => (int) $t['period'],
				'symbol' => array(
					'number' => (int) $t->symbol['number'],
					'name' => (string) $t->symbol['name'],
					'var' => (string) $t->symbol['var'],
				),
				'precipitation' => (int) $t->precipitation['value'],
				'windDirection' => array(
					'deg' => (float) $t->windDirection['deg'],
					'code' => (string) $t->windDirection['code'],
					'name' => (string) $t->windDirection['name'],
				),
				'windSpeed' => array(
					'mps' => (float) $t->windSpeed['mps'],
					'name' => (string) $t->windSpeed['name'],
				),
				'temperature' => array(
					'value' => (float) $t->temperature['value'],
					'unit' => (string) $t->temperature['unit'],
				),
				'pressure' => array(
					'value' => (float) $t->pressure['value'],
					'unit' => (string) $t->pressure['unit'],
				),
			);
		}
		return $data;
	}

	/**
	 * Get forecast for three days ahead
	 * @see getForecast()
	 * @return array
	 */
	public function getForecastNextThreeDays() {
		$forecast = $this->getForecast();
		$data = array();
		$counter = 0;
		foreach($forecast as $f) {
			if($f['period'] == 2) {
				// From 12.00 - 18.00
				$data[] = $f;
				if($counter == 2) break;
				$counter++;
			}
		}
		return $data;
	}
	
	/**
	 * Get PDF forecast URL
	 * @return string
	 */
	public function getPDF() {
		$url = 'http://www.yr.no/'.$this->getYrLanguageString().$this->location_url.'varsel.pdf';
		return $url;
	}
	
	/**
	 * Get forecast output in table
	 * @param $default_style boolean If true, we will add some CSS to style the table. If false, output raw table.
	 * @param $options array Options for styling the table. Available options is thead_bg, thead_color
	 * @see getForecast()
	 * @return string
	 */
	public function getForecastTable($default_style = true, $options = array()) {
		$forecast = $this->getForecast();
		$temp_day = false;
		$html = array();
		
		$default_options = array(
			'thead_bg' => '#016B6D',
			'thead_color' => '#ffffff',
		);
		
		$options = array_merge($default_options, $options);
		
		if($default_style) {
			$html[] = '<style type="text/css">
			table.weather-table > * {
				text-align: left;
				margin: 0;
				padding: 0;
			}
			table.weather-table {
				border-collapse: collapse;
				text-valign: top;
				width: 100%;
				margin-bottom: 20px;
			}
			table.weather-table caption {
				
			}
			table.weather-table thead th, table.weather-table tbody td {
				padding: 2px;
			}
			table.weather-table thead th {
				background: '.$options['thead_bg'].';
				color: '.$options['thead_color'].';
			}
			table.weather-table thead th.time { width: 15%; }
			table.weather-table thead th.forecast { width: 15%; }
			table.weather-table thead th.temp { width: 15%; }
			table.weather-table thead th.precipitation { width: 15%; }
			table.weather-table thead th.wind { width: 40%; }
			
			table.weather-table tbody tr.odd {

			}
			table.weather-table tbody tr.even {
				background: #f0f0f0;
			}
			table.weather-table tbody td {
				border: 1px solid #ddd;
			}
			table.weather-table tbody td.temp .plus {
				color: red;
			}
			table.weather-table tbody td.temp .minus {
				color: blue;
			}
			</style>';
		}
		foreach($forecast as $f) {
			$date = new \DateTime($f['from']);
			$current_day = date('Ymd', $date->getTimestamp());
			if(!$temp_day || $current_day != $temp_day) {
				if($temp_day) {
					$html[] = '</tbody>';
					$html[] = '</table>';
				}
				if(date('Ymd', $date->getTimestamp()) == date('Ymd')) $day = '<span class="day">'.$this->_t('Today').',</span> <span class="date">'.$date->format('d.m.Y').'</span>';
				elseif(date('Ymd', $date->getTimestamp()) == date('Ymd', strtotime('+1 day'))) $day = '<span class="day">'.$this->_t('Tomorrow').',</span> <span class="date">'.$date->format('d.m.Y').'</span>';
				else $day = '<span class="day">'.$this->_t(date('l', $date->getTimestamp())).',</span> <span class="date">'.$date->format('d.m.Y').'</span>';
				$html[] = '<table class="weather-table">';
				$html[] = '<caption>'.$day.'</caption>';
				$html[] = '<thead><tr><th class="time">'.$this->_t('Time').'</th><th class="forecast">'.$this->_t('Forecast').'</th><th class="temp">'.$this->_t('Temp.').'</th><th class="precipitation">'.$this->_t('Precipitation').'</th><th class="wind">'.$this->_t('Wind').'</th></tr></thead>';
				$html[] = '<tbody>';
				
				$temp_day = $current_day;
				$counter = 0;
			}
			
			$from = new \DateTime($f['from']);
			$to = new \DateTime($f['to']);
			$wind_text = $f['windSpeed']['name'].', '.$f['windSpeed']['mps'].' '.$this->_t('m/s').' '.$this->_t('from').' '.strtolower($f['windDirection']['name']); 
			$html[] = '<tr class="'.($counter%2 ? 'even' : 'odd').'"><td class="time">'.$from->format('H:i').' - '.$to->format('H:i').'</td><td class="forecast"></td><td class="temp"><span class="'.($f['temperature']['value'] > 0 ? 'plus' : 'minus').'">'.$f['temperature']['value'].' &#8451;</span></td><td class="precipitation">'.$f['precipitation'].' mm</td><td class="wind">'.$wind_text.'</td></tr>';
			$counter++;
		}
		$html[] = '</tbody>';
		$html[] = '</table>';
		return join("\n", $html);
	}
	
	/**
	 * Translate strings
	 * @param $str string String to translate
	 * @param $lang string|false Language to translate to. Allowed values: en|nb|nn. If false, default language is used
	 * @return string
	 */
	private function _t($str, $lang = false) {
		if(!$lang) $lang = $this->language;
		switch($lang) {
			case 'nb':
				$t = array(
					'm/s' => 'm/s',
					'from' => 'fra',
					'Time' => 'Tid',
					'Forecast' => 'Varsel',
					'Temp.' => 'Temp.',
					'Precipitation' => 'Nedbør',
					'Wind' => 'Vind',
					'Monday' => 'Mandag',
					'Tuesday' => 'Tirsdag',
					'Wednesday' => 'Onsdag',
					'Thursday' => 'Torsdag',
					'Friday' => 'Fredag',
					'Saturday' => 'Lørdag',
					'Sunday' => 'Søndag',
					'Today' => 'Idag',
					'Tomorrow' => 'I morgen',
				);
				if(array_key_exists($str, $t)) return $t[$str];
				else return $str;
				break;
			case 'nn':
				$t = array(
					'm/s' => 'm/s',
					'from' => 'frå',
					'Time' => 'Tid',
					'Forecast' => 'Varsel',
					'Temp.' => 'Temp.',
					'Precipitation' => 'Nedbør',
					'Wind' => 'Vind',
					'Monday' => 'Måndag',
					'Tuesday' => 'Tysdag',
					'Wednesday' => 'Onsdag',
					'Thursday' => 'Torsdag',
					'Friday' => 'Fredag',
					'Saturday' => 'Laurdag',
					'Sunday' => 'Sundag',
					'Today' => 'Idag',
					'Tomorrow' => 'I morgon',
				);
				if(array_key_exists($str, $t)) return $t[$str];
				else return $str;
				break;
			case 'en':
			default:
				return $str;
				break;
		}
	}
	
}

?>
