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
      $kids = meeko::getData('kids');
      foreach ($kids as $key => $value)
  		{
  			$eqLogic = eqLogic::byLogicalId('kid' . $kids[$key]->id, 'meeko');
  			if (!is_object($eqLogic))
  			{
  				log::add('meeko', 'debug', 'Création de l\'Enfant n°' . $kids[$key]->id . ' : ' . $kids[$key]->first_name . ' ' . $return[$key]->last_name);
  				$eqLogic = new meeko();
    			$eqLogic->setName($kids[$key]->first_name . ' (Crèche)');
    			$eqLogic->setIsEnable(1);
    			$eqLogic->setIsVisible(1);
          $eqLogic->setDisplay('height','450px');
          $eqLogic->setDisplay('width', '390px');
  			}
  			$eqLogic->setLogicalId('kid' . $kids[$key]->id);
  			$eqLogic->setEqType_name('meeko');
  			$eqLogic->setConfiguration('id', $kids[$key]->id);
  			$eqLogic->setConfiguration('first_name', $kids[$key]->first_name);
  			$eqLogic->setConfiguration('last_name', $kids[$key]->last_name);
  			$eqLogic->setConfiguration('gender', $kids[$key]->gender);
  			$eqLogic->setConfiguration('birthdate', $kids[$key]->birthdate);
  			$eqLogic->setConfiguration('avatar_url', $kids[$key]->avatar_url);
  			$eqLogic->save();
      }
      ajax::success();
    }



    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
