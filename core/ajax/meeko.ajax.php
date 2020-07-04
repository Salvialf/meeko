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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init(true);

    if (init('action') == 'syncMeeko') {
      $kids = meeko::pull('kids');
      for ($i = 0; $i < count($kids); $i++)
  		{
  			$eqLogic = eqLogic::byLogicalId('kid' . $kids[$i]['id'], 'meeko');
  			if (!is_object($eqLogic))
  			{
  				log::add('meeko', 'debug', 'Création de l\'Enfant n°' . $kids[$i]['id'] . ' : ' . $kids[$i]['first_name'] . ' ' . $return[$i]['last_name']);
  				$eqLogic = new meeko();
    			$eqLogic->setName('Crèche ' . $kids[$i]['first_name']);
    			$eqLogic->setIsEnable(1);
    			$eqLogic->setIsVisible(1);
          $eqLogic->setDisplay('height','532px');
          $eqLogic->setDisplay('width', '312px');
    			$eqLogic->setConfiguration('widgetApp', 1);
  			}
  			$eqLogic->setLogicalId('kid' . $kids[$i]['id']);
  			$eqLogic->setEqType_name('meeko');
  			$eqLogic->setConfiguration('id', $kids[$i]['id']);
  			$eqLogic->setConfiguration('first_name', $kids[$i]['first_name']);
  			$eqLogic->setConfiguration('last_name', $kids[$i]['last_name']);
  			$eqLogic->setConfiguration('gender', $kids[$i]['gender']);
  			$eqLogic->setConfiguration('birthdate', $kids[$i]['birthdate']);
  			$eqLogic->setConfiguration('avatar_url', $kids[$i]['avatar_url']);
  			$eqLogic->save();
      }
      ajax::success();
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
