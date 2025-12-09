<?php
/**
 * Test file để kiểm tra search function
 */

require_once __DIR__ . '/src/models/movie_db.php';

// Test 1: Search với keyword "Kraven"
echo "<h2>Test 1: Search 'Kraven'</h2>";
$results = search_movies('Kraven');
echo "<pre>";
print_r($results);
echo "</pre>";

// Test 2: Search với keyword "Mufasa"
echo "<h2>Test 2: Search 'Mufasa'</h2>";
$results = search_movies('Mufasa');
echo "<pre>";
print_r($results);
echo "</pre>";

// Test 3: Search với keyword "Sonic"
echo "<h2>Test 3: Search 'Sonic'</h2>";
$results = search_movies('Sonic');
echo "<pre>";
print_r($results);
echo "</pre>";

// Test 4: Search với keyword tiếng Việt "Làm"
echo "<h2>Test 4: Search 'Làm'</h2>";
$results = search_movies('Làm');
echo "<pre>";
print_r($results);
echo "</pre>";

// Test 5: Get all now showing movies
echo "<h2>Test 5: All Now Showing Movies</h2>";
$results = get_now_showing_movies();
echo "<pre>";
print_r($results);
echo "</pre>";
?>
