<?php
/**
 * Geo Utilities
 * Common geographic calculation functions.
 */

/**
 * Calculate the distance (km) between two lat/lon points using the Haversine formula.
 *
 * @param float $lat1 Latitude of point 1
 * @param float $lon1 Longitude of point 1
 * @param float $lat2 Latitude of point 2
 * @param float $lon2 Longitude of point 2
 * @return float Distance in kilometers
 */
function haversine_distance_km(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earth_radius = 6371.0; // Earth radius in km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * asin(min(1, sqrt($a)));

    return $earth_radius * $c;
}

/**
 * Calculate the ride price based on distance.
 *
 * @param float $distance_km Distance in kilometers
 * @param float $rate_per_km Rate per kilometer (default: 0.10€)
 * @param float $minimum_price Minimum price per seat (default: 2.00€)
 * @return float Calculated price rounded to 2 decimals
 */
function calculate_ride_price(float $distance_km, float $rate_per_km = 0.10, float $minimum_price = 2.00): float
{
    $calculated = $distance_km * $rate_per_km;
    return round(max($minimum_price, $calculated), 2);
}

/**
 * Validate latitude value.
 *
 * @param mixed $lat The latitude to validate
 * @return bool True if valid latitude (-90 to 90)
 */
function is_valid_latitude($lat): bool
{
    if (!is_numeric($lat)) {
        return false;
    }
    $lat = (float) $lat;
    return $lat >= -90 && $lat <= 90;
}

/**
 * Validate longitude value.
 *
 * @param mixed $lon The longitude to validate
 * @return bool True if valid longitude (-180 to 180)
 */
function is_valid_longitude($lon): bool
{
    if (!is_numeric($lon)) {
        return false;
    }
    $lon = (float) $lon;
    return $lon >= -180 && $lon <= 180;
}

/**
 * Validate both latitude and longitude.
 *
 * @param mixed $lat Latitude
 * @param mixed $lon Longitude
 * @return bool True if both are valid
 */
function is_valid_coordinates($lat, $lon): bool
{
    return is_valid_latitude($lat) && is_valid_longitude($lon);
}
