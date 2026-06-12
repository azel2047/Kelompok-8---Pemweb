<?php
$destDir = __DIR__ . '/../public/models/';
if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$files = [
    'ssd_mobilenetv1_model-weights_manifest.json',
    'ssd_mobilenetv1_model-shard1',
    'face_landmark_68_model-weights_manifest.json',
    'face_landmark_68_model-shard1',
    'face_recognition_model-weights_manifest.json',
    'face_recognition_model-shard1',
    'tiny_face_detector_model-weights_manifest.json',
    'tiny_face_detector_model-shard1',
    'face_landmark_68_tiny_model-weights_manifest.json',
    'face_landmark_68_tiny_model-shard1'
];

$baseUrl = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js-models/master/';

foreach ($files as $file) {
    $url = $baseUrl . $file;
    $dest = $destDir . $file;
    echo "Downloading $file... ";
    $content = @file_get_contents($url);
    if ($content === false) {
        echo "FAILED\n";
    } else {
        file_put_contents($dest, $content);
        echo "SUCCESS\n";
    }
}
echo "Download finished.\n";
