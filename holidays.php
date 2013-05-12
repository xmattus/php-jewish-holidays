<?php

function sorted_holidays() {

  $holidays = array(
    array('name' => 'Rosh Hashanah', 'month' => 1, 'day' => 1, 'duration' => 2),  // Begins 1 Tishrei
    array('name' => 'Yom Kippur', 'month' => 1, 'day' => 10, 'duration' => 1),  // 10 Tishrei
    array('name' => 'Sukkot', 'month' => 1, 'day' => 15, 'duration' => 7),  // Begins 15 Tishrei
    array('name' => 'Shemini Atzeret', 'month' => 1, 'day' => 22, 'duration' => 1),  // 22 Tishrei (follows Sukkot)
    array('name' => 'Simchat Torah', 'month' => 1, 'day' => 23, 'duration' => 1),  // 23 Tishrei (in Israel, same day as Shemini Atzeret; diaspora rules used here)
    array('name' => 'Hanukkah', 'month' => 3, 'day' => 25, 'duration' => 8),  // Begins 25 Kislev
    array('name' => 'Tu B\'Shevat', 'month' => 5, 'day' => 15, 'duration' => 1), // 15 Shevat
    array('name' => 'Purim', 'month' => is_heb_leap_year($jyear) ? 7 : 6, 'day' => 14, 'duration' => 1), // 14 Adar II in leap years, 14 Adar in non-leap years
    array('name' => 'Passover', 'month' => 8, 'day' => 15, 'duration' => 8), // Begins 15 Nisan
    array('name' => 'Shavuot', 'month' => 10, 'day' => 6, 'duration' => 2), // 6 Sivan
    array('name' => 'Tisha B\'Av', 'month' => 12, 'day' => 9, 'duration' => 1), // 9 Av
  );

  // This avoids PHP warnings; remove if set properly elsewhere.
  date_default_timezone_set('America/New_York');
  $today_jd = unixtojd();
  $jewish = jdtojewish($today_jd);
  list($jmonth, $jday, $jyear) = explode('/', $jewish);

  $return = array();
  foreach ($holidays as $holiday) {
    // The nature of the JewishToJD() function is such that the JD returned corresponds to the Gregorian day on which the Jewish date begins at sundown,
    // e.g. 1 Tishrei, 5774 = September 4, 2013; Rosh Hashanah beings at sundown on September 4, 2013.
    // Note that many (Gregorian) calendars will note the date of Rosh Hashanah in this case as "September 5, 2013" since that's the first full day of it.
    $jd = JewishToJD($holiday['month'], $holiday['day'], $jyear);
    $end_jd = $jd + $holiday['duration'];
    if ($end_jd < $today_jd) {
      // This holiday has already passed in the current Jewish year. Advance to the next one.
      if ($holiday['name'] == 'Purim') {
        // Small hack to reset the Purim month if we've advanced into next year, and it's leap year status is different than the previous year's status.
        $holiday['month'] = is_heb_leap_year($jyear+1) ? 7 : 6;
      }
      $jd = JewishToJD($holiday['month'], $holiday['day'], $jyear+1);
      $end_jd = $jd + $holiday['duration'];
    }
    $until = ($end_jd - $today_jd);
    if ( $until == 0) {
      $until = 365; // There aren't necessarily 365 days in a given Jewish year, but this will be enough to get the sort order right.
    }

    //
    $start = jdtounix($jd+1);
    $end = jdtounix($end_jd);

    // This string calculation won't work if Hanukkah ever spans the Gregorian new year.
    // This is next scheduled to happen across 2016-2017, so hopefully I'll have time to fix it before then!
    $startmonth = date('F', $start);
    $endmonth = date('F', $end);
    if ($startmonth == $endmonth) {
      $holiday['string'] = $startmonth . ' ' . date('j', $start) . '-' . date('j', $end) . ', ' . date('Y', $start);
    }
    else {
      $holiday['string'] = date('F j, Y', $start) . ' - ' . date('F j, Y', $end);
    }

    if ($start == $end) {
      $holiday['string'] = date('F j, Y', $start);
    }

    $return[$until] = $holiday;
  }
  ksort($return);
  return $return;
}

// Returns true if this is one of the leap years of the metonic cycle (in which a second month of Adar is added).
function is_heb_leap_year($year) {
  $year = (int)$year;
  if ($year % 19 == 0 || $year % 19 == 3 || $year % 19 == 6 || $year % 19 == 8 || $year % 19 == 11 || $year % 19 == 14 || $year % 19 == 17) {
    return TRUE;
  }
  return FALSE;
}


// For example: prints the holidays in order of next upcoming along with (Gregorian) dates of them, i.e. the first full day of them, not the date on which they begin at sundown.
$holidays = sorted_holidays();
foreach ($holidays as $until => $holiday) {
  $until_ts = jdtounix(unixtojd() + $until);
  $date = date('F j, Y', $until_ts);
  print $holiday['name'] . ": " . $holiday['string'] . "\n";
}