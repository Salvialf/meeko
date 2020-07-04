<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class meeko extends eqLogic
{
	/*     * *************************Attributs****************************** */

	private	$meekoCategories = [
		'day' => ['Jour', '<i class="fas fa-calendar-day"></i>'],
		'presences' => ['Pointage', '<i class="fab fa-creative-commons-by"></i>'],
		'observations' => ['Observation', '<i class="far fa-eye"></i>'],
		'diapers' => ['Hygiène', '<i class="fas fa-bath"></i>'],
		'activities' => ['Activité', '<i class="fas fa-dice"></i>'],
		'meals' => ['Repas', '<i class="fas fa-utensils"></i>'],
		'naps' => ['Sommeil', '<i class="fas fa-bed"></i>'],
		'temperatures' => ['Température', '<i class="fas fa-temperature-high"></i>'],
		'drugs' => ['Médicament', '<i class="fas fa-prescription-bottle-alt"></i>'],
		'weights' => ['Pesée', '<i class="fas fa-weight"></i>'],
		'photos' => ['Photo', '<i class="fas fa-camera"></i>']
	];
	private $diapers_en = array('diaper', 'potty', 'toilet', 'liquid', 'soft', 'normal', 'hard', 'null');
	private $diapers_fr = array('dans la couche ', 'sur le pot ', 'aux toilettes ', 'selles liquides ', 'selles molles ', 'selles normales ', 'selles dures ', 'null');
	private $meals_en = array('null', '0', '1', '2');
	private $meals_fr = array('n\'a rien mangé ', 'a peu mangé ', 'a bien mangé ', 'a tout mangé ');

//	public static $_widgetPossibility = array('custom' => true, 'custom::layout' => false);

	/*     * ***********************Methode static*************************** */

	public static function getToken()
	{
		$email = config::byKey('email', 'meeko');
		$password = config::byKey('password', 'meeko');
		if (empty($email) || empty($password))
		{
			log::add('meeko', 'info', 'L\'adresse email et/ou le mot de passe ne sont pas correctement renseignés');
			throw new Exception(__('Veuillez renseigner les éléments de configuration du plugin', __FILE__));
		}
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.meeko.app/family/v1/login",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\"email\":\"$email\",\"password\":\"$password\"}",
			CURLOPT_HTTPHEADER => array(
				"authority: api.meeko.app",
				"accept: application/json",
				"x-requested-with: XMLHttpRequest",
				"authorization: Bearer null",
				"user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Mobile Safari/537.36",
				"content-type: application/json;charset=UTF-8",
				"origin: https://family.meeko.app",
				"sec-fetch-site: same-site",
				"sec-fetch-mode: cors",
				"sec-fetch-dest: empty",
				"referer: https://family.meeko.app/",
				"accept-language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7"
			) ,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$return = json_decode($response);
		log::add('meeko', 'debug', 'Token : ' . $return->token);
		config::save('token', $return->token, 'meeko');
		return $return->token;
	}

	public static function pull($type, $params = null)
	{
		log::add('meeko', 'debug', 'pull : ' . $type . $params);
		$today = date('Y-m-d');
		$file_exists = file_exists('/var/www/html/plugins/meeko/data/'.$type.'_'.strtotime($today.'T00:00:01 UTC').'.json');

		if ($file_exists && empty($params))
		{
			log::add('meeko', 'debug', 'Utilisation du fichier '.ls(__DIR__ . '/../../data', $type.'_*.json')[0].' existant');
			$returnJson = file_get_contents(__DIR__ . '/../../data/' . ls(__DIR__ . '/../../data', $type.'_*.json')[0]);
			return json_decode($returnJson, true);
		}

		$token = config::byKey('token', 'meeko');
		$token = (empty($token)) ? meeko::getToken() : $token;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.meeko.app/family/v1/'.$type.$params,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"authority: api.meeko.app",
				"accept: application/json",
				"x-requested-with: XMLHttpRequest",
				"user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.106 Safari/537.36",
				"authorization: Bearer " . $token,
				"origin: https://family.meeko.app",
				"sec-fetch-site: same-site",
				"sec-fetch-mode: cors",
				"sec-fetch-dest: empty",
				"referer: https://family.meeko.app/",
				"accept-language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7"
			) ,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$return = json_decode($response, true);

		if ($type == 'kids' && !empty($params))
		{
			foreach ($return as $key => $value)
			{
				foreach ($value as $type => $activities)
				{
					if (is_array($activities) && !empty($activities))
					{
						$filtered[$return[$key]['id']][$type] = $activities;
					}
				}
			}
			$jsonFile = '/var/www/html/plugins/meeko/data/activities_'.str_replace(['?','=','&'],'',$params).'.json';
			file_put_contents($jsonFile, json_encode($filtered, JSON_PRETTY_PRINT));

			return $filtered;
		}
		else
		{
			if (!$file_exists)
			{
				$previousJsonFile = ls(__DIR__ . '/../../data', $type.'_*.json')[0];
				log::add('meeko', 'debug', 'Fichier Json précédent : ' . $previousJsonFile);
				if (!empty($previousJsonFile))
				{
					unlink(__DIR__ . '/../../data/'.$previousJsonFile);
				}
				$jsonFile = '/var/www/html/plugins/meeko/data/'.$type.'_'.strtotime($today.'T00:00:01 UTC').'.json';
				file_put_contents($jsonFile, json_encode($return, JSON_PRETTY_PRINT));
			}
			return $return;
		}
	}

	public static function cron15($_eqLogic_id = null)
	{
		if (date('G') < 7 || date('G') >= 22)
		{
			if(date('Gi') == 115) $this->checkAndUpdateCmd('day', date('Y-m-d'));
			return;	
		}
		if ($_eqLogic_id == null)
		{
			$eqLogics = self::byType('meeko', true);
		}
		else
		{
			$eqLogics = array(
				self::byId($_eqLogic_id)
			);
		}
		foreach ($eqLogics as $eqLogic)
		{
			try
			{
				$eqLogic->updateCategories();
			}
			catch(Exception $e)
			{
				log::add('meeko', 'info', $e->getMessage());
			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	public function postUpdate()
	{
		if (strpos($this->getLogicalId() , 'kid') !== false)
		{
			$order = 1;

			$refreshCmd = $this->getCmd(null, 'refresh');
			if (!is_object($refreshCmd))
			{
				$refreshCmd = new meekoCmd();
				$refreshCmd->setName(__('Rafraichir', __FILE__));
				$refreshCmd->setOrder($order);
				$order ++;
			}
			$refreshCmd->setEqLogic_id($this->getId());
			$refreshCmd->setLogicalId('refresh');
			$refreshCmd->setType('action');
			$refreshCmd->setSubType('other');
			$refreshCmd->save();

			$presenceCmd = $this->getCmd(null, 'presence');
			if (!is_object($presenceCmd))
			{
				$presenceCmd = new meekoCmd();
				$presenceCmd->setName(__('Présence', __FILE__));
				$presenceCmd->setTemplate('dashboard', 'presence');
				$presenceCmd->setTemplate('mobile', 'presence');
	      $presenceCmd->setOrder($order);
	      $order ++;
				$presenceCmd->setIsHistorized(1);
			}
			$presenceCmd->setLogicalId('presence');
			$presenceCmd->setEqLogic_id($this->getId());
			$presenceCmd->setType('info');
			$presenceCmd->setSubType('binary');
			$presenceCmd->save();

			foreach ($this->meekoCategories as $category => $catOptions)
			{
				$categoryCmd = '';
				$categoryCmd = $this->getCmd(null, $category);
				if (!is_object($categoryCmd))
				{
					$categoryCmd = new meekoCmd();
					$categoryCmd->setName(__($catOptions[0], __FILE__));
					$categoryCmd->setDisplay('icon', $catOptions[1]);
					$categoryCmd->setDisplay('showIconAndNamedashboard', 1);
					$categoryCmd->setDisplay('showIconAndNamemobile', 1);
					$categoryCmd->setTemplate('dashboard', 'meeko::meekoLines');
					$categoryCmd->setTemplate('mobile', 'meeko::meekoLines');
		      $categoryCmd->setOrder($order);
		      $order ++;
					$categoryCmd->setLogicalId($category);
					$categoryCmd->setEqLogic_id($this->getId());
					$categoryCmd->setType('info');
					$categoryCmd->setSubType('string');
					$categoryCmd->save();
				}

				$selectCategoryCmd = '';
				$selectCategoryCmd = $this->getCmd(null, 'select_'.$category);
				if (!is_object($selectCategoryCmd))
				{
					$selectCategoryCmd = new meekoCmd();
					$selectCategoryCmd->setName(__('Choisir '.$catOptions[0], __FILE__));
		      $selectCategoryCmd->setOrder($order);
		      $order ++;
					if ($category == 'day') {
					$selectCategoryCmd->setTemplate('dashboard', 'meeko::meekoInputDate');
					$selectCategoryCmd->setTemplate('mobile', 'meeko::meekoInputDate');
				} else {
					$selectCategoryCmd->setTemplate('dashboard', 'meeko::meekoSelect');
					$selectCategoryCmd->setTemplate('mobile', 'meeko::meekoSelect');
				}
					$selectCategoryCmd->setValue($categoryCmd->getId());
					$selectCategoryCmd->setLogicalId('select_'.$category);
					$selectCategoryCmd->setEqLogic_id($this->getId());
					$selectCategoryCmd->setType('action');
					$selectCategoryCmd->setSubType('select');
					$selectCategoryCmd->save();
				}

				}
			}
		if ($this->getIsEnable() == 1) {
    	$this->updateCategories();
		}
	}

	public function getPhotoById($id) {
	//	$photosObj = meeko::pull('photos');

		$photosJson = file_get_contents('/var/www/html/plugins/meeko/data/photos.json');
		$photos = json_decode($photosJson, true);

		for ($i = 0; $i < count($photos['data']); $i++)
		{
			if ($photos['data'][$i]['id'] == $id)
			{
				$return = array('thumbnail_url' => $photos['data'][$i]['thumbnail_url'], 'photo_url' => $photos['data'][$i]['photo_url']);
				return $return;
			}
		}
	}

	public function toHtml($_version = 'dashboard') {
		if ($this->getConfiguration('widgetApp') != 1)
		{
			return parent::toHtml();
		}

		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$_version = jeedom::versionAlias($_version);

		$replace['#day#'] = $this->getCmd(null, 'day')->execCmd();
		$replace['#picture#'] = $this->getConfiguration('avatar_url');
		$replace['#select_day#'] = $this->getCmd(null, 'select_day')->toHtml();
		$replace['#presence#'] = $this->getCmd(null, 'presence')->execCmd();

		$activity_template = getTemplate('core', $_version, 'activity', 'meeko');
		$activity_display = '';

		$cmdsList = $this->getCmd('info');
		foreach ($cmdsList as $cmd) {
			$cmdLogicalId = $cmd->getLogicalId();

			if ($cmdLogicalId != 'day' && $cmdLogicalId != 'presence') {
				$cmdValue = $cmd->execCmd();
				if (!empty($cmdValue)) {
					$cmdValues = explode(" : ", $cmdValue);
					$replaceCmd = array(
						'#logicalId#' => $cmdLogicalId,
						'#activityid#' => $cmd->getId(),
						'#activitylist#' => $this->getCmd('action', 'select_' . $cmdLogicalId)->getConfiguration('listValue')
					);
					if (!empty($cmdValues[1]))
					{
						$replaceCmd['#activitytime#'] = $cmdValues[0];
						$replaceCmd['#activitycontent#'] = $cmdValues[1];
					}
					else {
						$replaceCmd['#activitytime#'] = '';
						$replaceCmd['#activitycontent#'] = $cmdValues[0];
					}
					if ($cmd->getDisplay('showIconAndName' . $_version, 0) == 1) {
						$replaceCmd['#name_display#'] = $cmd->getDisplay('icon') . ' ' . $cmd->getName();
					}
					else {
						$replaceCmd['#name_display#'] = $cmd->getName();
					}
					$activity_display .= template_replace($replaceCmd, $activity_template);
				}
			}
		}
		$replace['#activities#'] = $activity_display;
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $_version, 'tmplMeeko', 'meeko')));
	}

	/*
	    * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
	   public static function postConfig_<Variable>() {
	   }
	*/

	/*
	    * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
	   public static function preConfig_<Variable>() {
	   }
	*/

	public function updateCategories()
	{
		if (!empty($this->getCmd(null, 'day')->execCmd()))
		{
			$date = $this->getCmd(null, 'day')->execCmd();
		}
		else {
			$date = date('Y-m-d');
			$this->getCmd(null, 'day')->event($date);
		}
		log::add('meeko', 'debug', 'Récupération des données en date du '.date('d/m/Y', strtotime($date)).' pour '.$this->getName());
		$dateParams = '?from=' . strtotime($date.'T00:00:01 UTC') . '&to=' . strtotime($date.'T23:59:59 UTC');
		($dateParams == '?from=' . strtotime(date('Y-m-d').'T00:00:01 UTC') . '&to=' . strtotime(date('Y-m-d').'T23:59:59 UTC')) ? $today = true : $today = false;
		if (!file_exists('/var/www/html/plugins/meeko/data/activities_'.str_replace(['?','=','&'],'',$dateParams).'.json') || $today == true)
		{
			$activities = $this::pull('kids', $dateParams);
		}
		else
		{
			$data = file_get_contents('/var/www/html/plugins/meeko/data/activities_'.str_replace(['?','=','&'],'',$dateParams).'.json');
			$activities = json_decode($data, true);
		}
		$kid = $this->getConfiguration('id');
		$firstName = $this->getConfiguration('first_name');


		$presenceCmd = $this->getCmd(null, 'presence');
		if (!empty($activities[$kid]['presences'])) {
			$countPresences = count($activities[$kid]['presences']) - 1;
		//			log::add('meeko', 'debug', 'Presences : '.print_r($activities[$kid]['presences'][$countPresences], true));
			if (empty($activities[$kid]['presences'][$countPresences]['picked_up_at']))
			{
				$presenceCmd->event(1, date('Y-m-d H:i:s',$activities[$kid]['presences'][$countPresences]['droped_at']));
			}
			else
			{
				$presenceCmd->event(0, date('Y-m-d H:i:s',$activities[$kid]['presences'][$countPresences]['picked_up_at']));
			}
		}
		else
		{
			$presenceCmd->event(0);
		}

		$selectCmdsList = $this->getCmd('action');
			//	log::add('meeko', 'debug', 'Liste diapers : '.count($selectCmdsList));
		foreach ($selectCmdsList as $cmd) {
			$cmdLogicalId = $cmd->getLogicalId();

			if (strpos($cmdLogicalId, 'select_') !== false && $cmdLogicalId != 'select_day') {
				$category = str_replace('select_', '', $cmdLogicalId);
				$countCategory = count($activities[$kid][$category]);
				//	log::add('meeko', 'debug', $category.' : '.$countCategory);
				if ($countCategory == 0) {
					$cmd->setIsVisible(0)->save();
					$infoCmd = $this->getCmd('info',$category);
					($category != 'presences') ? $infoCmd->event(null) : $infoCmd->event('Absent de la crèche');
				}
				else if ($countCategory == 1)
				{
					switch ($category)
					{
						case 'presences':
							$droped_at = $activities[$kid][$category][0]['droped_at'];
							$picked_up_at = $activities[$kid][$category][0]['picked_up_at'];
							$drop_note = (!empty($activities[$kid][$category][0]['drop_note'])) ? ' (' . $activities[$kid][$category][0]['drop_note'] . ')' : null;
							$pick_up_note = (!empty($activities[$kid][$category][0]['pick_up_note'])) ? ' (' . $activities[$kid][$category][0]['pick_up_note'] . ')' : null;
							if (empty($picked_up_at))
							{
								$value = ' Arrivée à ' . date('H:i', $droped_at) . $drop_note;
							}
							else
							{
								$value = ' Arrivée à '. date('H:i', $droped_at) . $drop_note .' - Départ à ' .date('H:i', $picked_up_at) . $pick_up_note . '<br>(durée '. gmdate('H:i', $picked_up_at - $droped_at) . ')';
							}
							break;
						case 'diapers':
							$type = str_replace($this->diapers_en, $this->diapers_fr, $activities[$kid][$category][0]['type']);
							$poop = str_replace($this->diapers_en, $this->diapers_fr, $activities[$kid][$category][0]['poop']);
							$pee = ($activities[$kid][$category][0]['pee'] == true) ? 'pipi ' : null;
							$peeAndPoop = (!empty($poop) && !empty($pee)) ? ' et ' : '';
							$ear = ($activities[$kid][$category][0]['ear'] == true) ? '+ oreilles ' : null;
							$eyes = ($activities[$kid][$category][0]['eyes'] == true) ? '+ yeux ' : null;
							$cream = ($activities[$kid][$category][0]['cream'] == true) ? '+ crème ' : null;
							$nose = ($activities[$kid][$category][0]['nose'] == true) ? '+ nez ' : null;
							$done_at = date('H:i', $activities[$kid][$category][0]['done_at']);
							$note = (!empty($activities[$kid][$category][0]['note'])) ? ' (' . $activities[$kid][$category][0]['note'] . ')' : null;
							$value = ' à '. $done_at.' : '. $pee . $peeAndPoop . $poop . $type . $ear . $eyes . $cream . $nose . $note;
							break;
						case 'activities':
							$name = $activities[$kid][$category][0]['name'];
							$cat = $activities[$kid][$category][0]['category'];
							$done_at = date('H:i', $activities[$kid][$category][0]['done_at']);
							$note = (!empty($activities[$kid][$category][0]['note'])) ? ' (' . $activities[$kid][$category][0]['note'] . ')' : null;
							$value = ' à '. $done_at.' : ' . $name.' ('.$cat.')' . $note;
							break;
						case 'meals':
							$rating = str_replace($this->meals_en, $this->meals_fr, $activities[$kid][$category][0]['rating']);
							$note = (!empty($activities[$kid][$category][0]['note'])) ? ' (' . $activities[$kid][$category][0]['note'] . ')' : null;
							$done_at = date('H:i', $activities[$kid][$category][0]['done_at']);
							$value = ' à '. $done_at.' : ' . $firstName . ' ' .  $rating . $note;
							break;
						case 'naps':
							$started_at = $activities[$kid][$category][0]['started_at'];
							$ended_at = $activities[$kid][$category][0]['ended_at'];
							$rating = ($activities[$kid][$category][0]['rating'] == null) ? 'n\'a pas dormi' : 'a dormi';
							$note = (!empty($activities[$kid][$category][0]['note'])) ? ' (' . $activities[$kid][$category][0]['note'] . ')' : null;
							if (empty($ended_at))
							{
								$value = $firstName . ' dort depuis ' . date('H:i', $started_at) . $note;
							}
							else
							{
								$value = $firstName . ' ' . $rating . ' de '. date('H:i', $started_at) .' à ' .date('H:i', $ended_at) . '<br>(durée ' . gmdate('H:i', $ended_at - $started_at) .')' . $note;
							}
							break;
						case 'photos':
						/*	if ($today == false) {
								$id = $activities[$kid][$category][0]['id'];
								$photo = $this->getPhotoById($id);
								$thumbnail_url = $photo['thumbnail_url'];
								$photo_url = $photo['photo_url'];
							}
							else
							{*/
								$thumbnail_url = $activities[$kid][$category][0]['thumbnail_url'];
								$photo_url = $activities[$kid][$category][0]['photo_url'];
					//		}
							$description = $activities[$kid][$category][0]['description'];
							$taken_at = date('H:i', $activities[$kid][$category][0]['taken_at']);
							$value = ($description != null) ? $description: '' . ' à '. $taken_at.' : <a href='.$photo_url.' target=_blank><img width=80 class="img-thumbnail" src='.$thumbnail_url.'></a>';
							break;
						case 'temperatures':
							$degree = $activities[$kid][$category][0]['degree'].' °C';
							$note = (!empty($activities[$kid][$category][0]['note'])) ? ' (' . $activities[$kid][$category][0]['note'] . ')' : null;
							$done_at = date('H:i', $activities[$kid][$category][0]['done_at']);
							$value = ' à '. $done_at.' : '.$degree . $note;
							break;
						case 'observations':
							$note = $activities[$kid][$category][0]['note'];
							$done_at = date('H:i', $activities[$kid][$category][0]['done_at']);
							$value = ' à '. $done_at.' : '.$note;
							break;
						case 'drugs':
							foreach ($activities[$kid][$category][0] as $key => $val)
							{
								$log .= $key . '=>' . $val;
							}
							$value = $log;
							break;
						case 'weights':
							foreach ($activities[$kid][$category][0] as $key => $val)
							{
								$log .= $key . '=>' . $val;
							}
							$value = $log;
							break;
					}
					$cmd->setIsVisible(0)->save();
					$infoCmd = $this->getCmd('info',$category);
					$infoCmd->event($value);
				}
				else
				{
					$listValues = '';
					for ($i = 0; $i < $countCategory; $i++) {
						switch ($category)
						{
							case 'presences':
								$droped_at = $activities[$kid][$category][$i]['droped_at'];
								$picked_up_at = $activities[$kid][$category][$i]['picked_up_at'];
								$drop_note = (!empty($activities[$kid][$category][$i]['drop_note'])) ? ' (' . $activities[$kid][$category][$i]['drop_note'] . ')' : null;
								$pick_up_note = (!empty($activities[$kid][$category][$i]['pick_up_note'])) ? ' (' . $activities[$kid][$category][$i]['pick_up_note'] . ')' : null;
								if (empty($picked_up_at))
								{
									$value = ' Arrivée à ' . date('H:i', $droped_at) . $drop_note;
									$listValues .= $value. '| Arrivée à '. date('H:i', $droped_at).';';
								}
								else
								{
									$value = ' Arrivée à '. date('H:i', $droped_at) . $drop_note .' - Départ à ' .date('H:i', $picked_up_at) . $pick_up_note . '<br>(durée '. gmdate('H:i', $picked_up_at - $droped_at) .')';
									$listValues .= $value. '| Arrivée à '. date('H:i', $droped_at).' - Départ à '.date('H:i', $picked_up_at).';';
								}
								break;
							case 'diapers':
								$type = str_replace($this->diapers_en, $this->diapers_fr, $activities[$kid][$category][$i]['type']);
								$poop = str_replace($this->diapers_en, $this->diapers_fr, $activities[$kid][$category][$i]['poop']);
								$pee = ($activities[$kid][$category][$i]['pee'] == true) ? 'pipi ' : null;
								$peeAndPoop = (!empty($poop) && !empty($pee)) ? ' et ' : '';
								$ear = ($activities[$kid][$category][$i]['ear'] == true) ? '+ oreilles ' : null;
								$eyes = ($activities[$kid][$category][$i]['eyes'] == true) ? '+ yeux ' : null;
								$cream = ($activities[$kid][$category][$i]['cream'] == true) ? '+ crème ' : null;
								$nose = ($activities[$kid][$category][$i]['nose'] == true) ? '+ nez ' : null;
								$done_at = date('H:i', $activities[$kid][$category][$i]['done_at']);
								$note = (!empty($activities[$kid][$category][$i]['note'])) ? ' (' . $activities[$kid][$category][$i]['note'] . ')' : null;
								$value = ' à '. $done_at.' : '. $pee . $peeAndPoop . $poop . $type . $ear . $eyes . $cream . $nose . $note;
								$listValues .= $value. '|'. $pee . $peeAndPoop . $poop .' à '. $done_at.';';
								break;
							case 'activities':
								$name = $activities[$kid][$category][$i]['name'];
								$cat = $activities[$kid][$category][$i]['category'];
								$done_at = date('H:i', $activities[$kid][$category][$i]['done_at']);
								$note = (!empty($activities[$kid][$category][$i]['note'])) ? ' (' . $activities[$kid][$category][$i]['note'] . ')' : null;
								$value = ' à '. $done_at.' : ' . $name.' - '.$cat . $note;
								$listValues .= $value. '|'. $cat .' à '. $done_at.';';
								break;
							case 'meals':
								$rating = str_replace($this->meals_en, $this->meals_fr, $activities[$kid][$category][$i]['rating']);
								$note = (!empty($activities[$kid][$category][$i]['note'])) ? ' (' . $activities[$kid][$category][$i]['note'] . ')' : null;
								$done_at = date('H:i', $activities[$kid][$category][$i]['done_at']);
								$value = ' à '. $done_at.' : ' .$firstName . ' ' .  $rating . $note;
								$listValues .= $value. '|' . $rating . ' à '. $done_at.';';
								break;
							case 'naps':
								$started_at = $activities[$kid][$category][$i]['started_at'];
								$ended_at = $activities[$kid][$category][$i]['ended_at'];
								$rating = ($activities[$kid][$category][0]['rating'] == null) ? 'n\'a pas dormi' : 'a dormi';
								$note = (!empty($activities[$kid][$category][$i]['note'])) ? ' (' . $activities[$kid][$category][$i]['note'] . ')' : null;
								if (empty($ended_at))
								{
									$value = $firstName . ' dort depuis ' . date('H:i', $started_at) . $note;
								}
								else
								{
									$value = $firstName . ' ' . $rating . ' de '. date('H:i', $started_at) .' à ' .date('H:i', $ended_at) . '<br>(durée ' . gmdate('H:i', $ended_at - $started_at) .')' . $note;
								}
								$listValues .= $value. '| à '. date('H:i', $started_at).';';
								break;
							case 'photos':
								if ($today == false) {
									$id = $activities[$kid][$category][$i]['id'];
									$photo = $this->getPhotoById($id);
									$thumbnail_url = $photo['thumbnail_url'];
									$photo_url = $photo['photo_url'];
								}
								else
								{
									$thumbnail_url = $activities[$kid][$category][$i]['thumbnail_url'];
									$photo_url = $activities[$kid][$category][$i]['photo_url'];
								}
								$description = $activities[$kid][$category][$i]['description'];
								$taken_at = date('H:i', $activities[$kid][$category][$i]['taken_at']);
								$value = ($description != null) ? $description: '' . ' à '. $taken_at.' : <a href='.$photo_url.' target=_blank><img width=80 src='.$thumbnail_url.'></a>';
								$listValues .= $value.'| Prise à '. $taken_at.';';
								break;
							case 'temperatures':
								$degree = $activities[$kid][$category][$i]['degree'].' °C';
								$note = (!empty($activities[$kid][$category][$i]['note'])) ? ' (' . $activities[$kid][$category][$i]['note'] . ')' : null;
								$done_at = date('H:i', $activities[$kid][$category][$i]['done_at']);
								$value = ' à '. $done_at.' : '.$degree . $note;
								$listValues .= $value. '|' . $degree . ' à '. $done_at.';';
								break;
							case 'observations':
								$note = $activities[$kid][$category][$i]['note'];
								$done_at = date('H:i', $activities[$kid][$category][$i]['done_at']);
								$value = ' à '. $done_at.' : '.$note;
								$listValues .= $value. '| à '. $done_at.';';
								break;
							case 'drugs':
								foreach ($activities[$kid][$category][0] as $key => $val)
								{
									$log .= $key . '=>' . $val;
								}
								$value = $log;
								break;
							case 'weights':
								foreach ($activities[$kid][$category][0] as $key => $val)
								{
									$log .= $key . '=>' . $val;
								}
								$value = $log;
								break;
						}
					}
					$cmd->setIsVisible(1);
					$cmd->setConfiguration('listValue', substr($listValues,0,-1))->save();
					$infoCmd = $this->getCmd('info',$category);
					$infoCmd->event($value);
				}
			}
		}
		$this->refreshWidget();
	}

	/*     * **********************Getteur Setteur*************************** */
}

class meekoCmd extends cmd
{
	/*     * *************************Attributs****************************** */

//	public static $_widgetPossibility = array('custom' => true);

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	/*
	    * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
	     public function dontRemoveCmd() {
	     return true;
	     }
	*/

	public function execute($_options = array())
	{
		if ($this->getType() == 'info') {
			return;
		}
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'refresh')
		{
			log::add('meeko', 'debug', 'Rafraichissement des commandes de l\'équipement '. $eqLogic->getName());
			$eqLogic->updateCategories();
		}
		if ($this->getSubType() == 'select')
		{
			$category = str_replace('select_', '', $this->getLogicalId());
			if ($category == 'day')
			{
				$eqLogic->checkAndUpdateCmd($category, $_options['selectedDate']);
				log::add('meeko', 'debug', 'Sélection jour : '.date('d/m/Y', strtotime($_options['selectedDate'])).' pour '.$eqLogic->getName() );
				$eqLogic->updateCategories();
			}
			else
			{
				$eqLogic->checkAndUpdateCmd($category, $_options['select']);
				log::add('meeko', 'debug', $category.' : '.$_options['select'] );
			}
		}
		return false;
	}

	/*     * **********************Getteur Setteur*************************** */
}
