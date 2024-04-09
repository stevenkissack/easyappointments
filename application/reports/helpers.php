<?php

function getWeekDateRangeFromString($yearWeekString) {
    // Split the string into year and week number
    list($year, $weekNumber) = explode('-', $yearWeekString);

    // Create date object for first day of the year
    $date = new DateTime("{$year}-01-01");

    // Adjust to the first day of the given week number
    $weekInterval = new DateInterval('P' . ($weekNumber - 1) . 'W');
    $date->add($weekInterval);

    // Find the day of the week for the first day of the year
    $dayOfWeek = (int) $date->format('N');

    // Calculate the date of the Monday of the target week
    $monday = clone $date;
    if ($dayOfWeek > 1) {
        // Subtract necessary days to get back to the first day of the week (Monday)
        $daysToMonday = $dayOfWeek - 1;
        $monday->sub(new DateInterval('P' . $daysToMonday . 'D'));
    }

    // Sunday of the target week is 6 days after Monday
    $sunday = clone $monday;
    $sunday->add(new DateInterval('P6D'));

    // Format the dates
    $start = $monday->format('M j');
    $end = $sunday->format('M j');

    return "{$start} - {$end}";
}

?>