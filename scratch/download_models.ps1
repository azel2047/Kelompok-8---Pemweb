$models = @(
    "ssd_mobilenetv1_model-weights_manifest.json",
    "ssd_mobilenetv1_model-shard1",
    "face_landmark_68_model-weights_manifest.json",
    "face_landmark_68_model-shard1",
    "face_recognition_model-weights_manifest.json",
    "face_recognition_model-shard1",
    "tiny_face_detector_model-weights_manifest.json",
    "tiny_face_detector_model-shard1",
    "face_landmark_68_tiny_model-weights_manifest.json",
    "face_landmark_68_tiny_model-shard1"
)

$destDir = "public/models"
if (!(Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
}

foreach ($model in $models) {
    Write-Host "Downloading $model..."
    $url = "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/$model"
    $out = Join-Path $destDir $model
    curl.exe -s -L $url -o $out
}

Write-Host "All downloads complete!"
