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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$nursery = file_get_contents('plugins/meeko/data/nurseries.json');
$nursery = json_decode($nursery, true);

meeko::getData('staffs');
$staff = file_get_contents('plugins/meeko/data/staffs.json');
$staff = json_decode($staff, true);
?>

<div class="row text-center">

  <div class="col-sm-4">
  <img src="<?= $nursery[0]['logo_url'] ?>" class="img-thumbnail">
</div>

  <div class="col-sm-4">
    <address>
  <h3 style="font-family:grandHotel;font-weight:bold;"><?= $nursery[0]['name'] ?></h3>
  <span><i class="fas fa-map-marker-alt"></i> <?= $nursery[0]['address'] ?><br>
  <?= $nursery[0]['zipcode'] ?> <?= $nursery[0]['city'] ?></span><br><br>
  <a href="tel:<?= $nursery[0]['phone'] ?>"><i class="fas fa-phone-alt"></i> <?= $nursery[0]['phone'] ?></a><br>
  <a href="mailto:<?= $nursery[0]['email'] ?>"><i class="fas fa-envelope-open-text"></i> <?= $nursery[0]['email'] ?></a><br>
</address>
</div>

</div>

<div class="row text-center">

  <div class="col-sm-3">
  <h3 style="font-family:grandHotel;font-weight:bold;">{{Horaires}}</h3>
  <table style="margin-left:auto; margin-right:auto;">
  <?php $english_day = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
    $french_day = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
    for ($i = 0; $i < count($nursery[0]['openingHours']); $i++)
  {
    echo '<tr><th>'.str_replace($english_day, $french_day, $nursery[0]['openingHours'][$i]['day']) . ' : </th><td>' . substr($nursery[0]['openingHours'][$i]['started_at'],0,-3) . ' - ' . substr($nursery[0]['openingHours'][$i]['ended_at'],0,-3) . '</td></tr>';
  }
  ?>
  </table>
  </div>

  <div class="col-sm-4">
    <h3 style="font-family:grandHotel;font-weight:bold;">{{Fermetures}}</h3>
    <table style="margin-left:auto; margin-right:auto;">
      <?php for ($i = 0; $i < count($nursery[0]['closedPeriods']); $i++)
      {
        echo '<tr><th>'.$nursery[0]['closedPeriods'][$i]['name'] . ' : </th><td> du ' . date('d/m/Y', $nursery[0]['closedPeriods'][$i]['started_at']) . ' au ' . date('d/m/Y', $nursery[0]['closedPeriods'][$i]['ended_at']) . '</td></tr>';
      }
      ?>
    </table>
  </div>

  <div class="col-sm-3">
    <h3 style="font-family:grandHotel;font-weight:bold;">{{Fériés}}</h3>
    <table style="margin-left:auto; margin-right:auto;">
      <?php for ($i = 0; $i < count($nursery[0]['holidays']); $i++)
      {
        echo '<tr><th>'.$nursery[0]['holidays'][$i]['name'] . ' </th></tr>';
      }
      ?>
    </table>
  </div>
</div>

<div class="row text-center">
  <div class="col-xs-12 col-md-10">
  <h3 style="font-family:grandHotel;font-weight:bold;">{{L'équipe}}</h3>
    <?php for ($i = 0; $i < count($staff); $i++)
    {
      echo '<div class="col-md-3">';
      echo '<img src="' . $staff[$i]['avatar_url'] . '" height=80 width=80 class="img-circle">';
      echo '<h4> ' . $staff[$i]['first_name'] . ' ' . $staff[$i]['last_name'] . ' </h4>';
      echo '<span>' . $staff[$i]['job'] . '</span>';
      echo '<span>' . $staff[$i]['biography'] . '</span>';
      echo '</div>';
    }
    ?>
    </div>
</div>
