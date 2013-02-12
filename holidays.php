<?php

function sorted_holidays() {
	// This avoids PHP warnings; remove if set properly elsewhere.
	date_default_timezone_set('America/New_York');
	$today_jd = unixtojd();
	$jewish = jdtojewish($today_jd);
	list($jmonth, $jday, $jyear) = explode('/', $jewish);

	$holidays = array(
    array('name' => 'Rosh Hashanah', 'month' => 1, 'day' => 1),  // Begins 1 Tishrei
    array('name' => 'Yom Kippur', 'month' => 1, 'day' => 10),  // 10 Tishrei
    array('name' => 'Sukkot', 'month' => 1, 'day' => 15),  // Begins 15 Tishrei
    array('name' => 'Shemini Atzeret', 'month' => 1, 'day' => 22),  // 22 Tishrei (follows Sukkot)
    array('name' => 'Simchat Torah', 'month' => 1, 'day' => 23),  // 23 Tishrei (in Israel, same day as Shemini Atzeret; diaspora rules used here)
    array('name' => 'Hanukkah', 'month' => 3, 'day' => 25),  // Begins 25 Kislev
    array('name' => 'Tu B\'Shevat', 'month' => 5, 'day' => 15), // 15 Shevat
    array('name' => 'Purim', 'month' => is_heb_leap_year($jyear) ? 7 : 6, 'day' => 14), // 14 Adar II in leap years, 14 Adar in non-leap years
    array('name' => 'Passover', 'month' => 8, 'day' => 15), // Begins 15 Nisan
    array('name' => 'Shavuot', 'month' => 10, 'day' => 6), // 6 Sivan
    array('name' => 'Tisha B\'Av', 'month' => 12, 'day' => 9), // 9 Av
	);

	$return = array();
	foreach ($holidays as $holiday) {
    // The nature of the JewishToJD() function is such that the JD returned corresponds to the Gregorian day on which the Jewish date begins at sundown,
    // e.g. 1 Tishrei, 5774 = September 4, 2013; Rosh Hashanah beings at sundown on September 4, 2013.
    // Note that many (Gregorian) calendars will note the date of Rosh Hashanah in this case as "September 5, 2013" since that's the first full day of it.
    $jd = JewishToJD($holiday['month'], $holiday['day'], $jyear);
    if ($jd < $today_jd) {
    	$jd = JewishToJD($holiday['month'], $holiday['day'], $jyear+1);
    }
    $until = $jd - $today_jd;
    $return[$until] = $holiday['name'];
	}
	ksort($return);
	return $return;
}

function is_heb_leap_year($year) {
	$year = (int)$year;
	if ($year % 19 == 0 || $year % 19 == 3 || $year % 19 == 6 || $year % 19 == 8 || $year % 19 == 11 || $year % 19 == 14 || $year % 19 == 17) {
		return TRUE;
	}
	return FALSE;
}


// For example: prints the holidays in order of next upcoming along with (Gregorian) dates of them (at sundown).
$holidays = sorted_holidays();
foreach ($holidays as $until => $name) {
  $until_ts = jdtounix(unixtojd() + $until);
  $date = date('F j, Y', $until_ts);
  print "$name: $date\n";
}