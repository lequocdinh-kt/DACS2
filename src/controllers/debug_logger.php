<?php
/**
 * Debug Logger
 * Receives debug logs from JavaScript and writes to debug_schedule.txt
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $logEntry = json_decode($input, true);
    
    if (!$logEntry) {
        throw new Exception('Invalid JSON input');
    }
    
    // Prepare log message
    $timestamp = $logEntry['timestamp'] ?? date('Y-m-d H:i:s');
    $message = $logEntry['message'] ?? 'No message';
    $data = $logEntry['data'] ?? null;
    
    $logText = "[$timestamp] $message\n";
    
    if ($data) {
        $logText .= "Data: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    $logText .= str_repeat('-', 80) . "\n";
    
    // Write to debug file
    $debugFile = __DIR__ . '/../../debug_schedule.txt';
    $result = file_put_contents($debugFile, $logText, FILE_APPEND);
    
    if ($result === false) {
        throw new Exception('Failed to write to debug file');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Log written successfully',
        'file' => $debugFile
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
