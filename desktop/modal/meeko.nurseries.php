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

$nursery = meeko::pull('nurseries');
$staff = meeko::pull('staffs');
?>

<div class="row text-center">

  <div class="col-sm-4">
    <img src="<?= $nursery[0]['logo_url'] ?>" class="img-responsive img-thumbnail pull-right">
  </div>

  <div class="col-sm-4">
    <address>
  <h2 style="font-family:grandHotel;font-weight:bold;"><?= $nursery[0]['name'] ?></h2>
  <span><i class="fas fa-map-marker-alt"></i> <?= $nursery[0]['address'] ?><br>
  <?= $nursery[0]['zipcode'] ?> <?= $nursery[0]['city'] ?></span><br><br>
  <a href="tel:<?= $nursery[0]['phone'] ?>"><i class="fas fa-phone-alt"></i> <?= $nursery[0]['phone'] ?></a><br>
  <a href="mailto:<?= $nursery[0]['email'] ?>"><i class="fas fa-envelope-open-text"></i> <?= $nursery[0]['email'] ?></a><br>
</address>
</div>

</div>
<hr>
<div class="row text-center">
  <div class="col-xs-12 col-md-10 col-md-offset-1">
  <table style="margin-left:auto; margin-right:auto;margin-bottom:0;" class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th style="font-family:grandHotel;font-weight:bold;" class="col-xs-12 col-sm-4 text-center"><h2>{{Horaires}}</h2></th>
          <th style="font-family:grandHotel;font-weight:bold;" class="col-xs-12 col-sm-4 text-center"><h2>{{Fermetures}}</h2></th>
          <th style="font-family:grandHotel;font-weight:bold;" class="col-xs-12 col-sm-4 text-center"><h2>{{Fériés}}</h2></th>
        </tr>
      </thead>
  </table>
        <table class="col-xs-12 col-sm-4">
    <?php $english_day = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
          $french_day = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
          for ($i = 0; $i < count($nursery[0]['openingHours']); $i++)
          {
          echo '<tr><td>'.str_replace($english_day, $french_day, $nursery[0]['openingHours'][$i]['day']) . ' : ' . substr($nursery[0]['openingHours'][$i]['started_at'],0,-3) . ' - ' . substr($nursery[0]['openingHours'][$i]['ended_at'],0,-3) . '</td></tr>';
          }
          ?>
        </table>
        <table class="col-xs-12 col-sm-4">
          <?php
          for ($j = 0; $j < count($nursery[0]['closedPeriods']); $j++)
          {
          echo '<tr><td>'.$nursery[0]['closedPeriods'][$j]['name'] . ' : ' . date('d/m/Y', $nursery[0]['closedPeriods'][$j]['started_at']) . ' au ' . date('d/m/Y', $nursery[0]['closedPeriods'][$j]['ended_at']) . '</td></tr>';
          }
          ?>
        </table>
          <table class="col-xs-12 col-sm-4">
            <?php
          for ($k = 0; $k < count($nursery[0]['holidays']); $k++)
          {
            echo '<tr><td>'.$nursery[0]['holidays'][$k]['name'] . ' </td></tr>';
          }
        ?>
    </table>
  </div>

<div class="row text-center">
  <div class="col-xs-12 col-md-10 col-md-offset-1">
  <h2 style="font-family:grandHotel;font-weight:bold;">{{L'équipe}}</h2>
    <?php for ($i = 0; $i < count($staff); $i++)
    {
      echo '<div class="col-xs-6 col-md-3">';
      echo '<img src="' . $staff[$i]['avatar_url'] . '" height=80 width=80 class="img-circle">';
      echo '<h3 style="font-family:grandHotel;"> ' . $staff[$i]['first_name'] . ' ' . $staff[$i]['last_name'] . ' </h3>';
      echo '<span>' . $staff[$i]['job'] . '</span>';
      echo '<span>' . $staff[$i]['biography'] . '</span>';
      echo '</div>';
    }
    ?>
    </div>
</div>
