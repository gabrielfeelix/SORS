<?php
// Colocar em public_html/ e acessar via browser

$storage_path = __DIR__ . '/storage/app/public';
$symlink_path = __DIR__ . '/public/storage';

echo "<h2>Diagnóstico de Storage - Kitamo</h2>";
echo "<pre>";

echo "📁 Storage Directory:\n";
echo "Path: $storage_path\n";
echo "Exists: " . (is_dir($storage_path) ? "✅ YES\n" : "❌ NO\n");
echo "Writable: " . (is_writable($storage_path) ? "✅ YES\n" : "❌ NO\n");
echo "Permissions: " . decoct(fileperms($storage_path) & 0777) . "\n\n";

echo "📎 Symlink:\n";
echo "Path: $symlink_path\n";
echo "Exists: " . (is_link($symlink_path) ? "✅ YES (Symlink)\n" : (file_exists($symlink_path) ? "⚠️ YES (But NOT a symlink)\n" : "❌ NO\n"));

if (is_link($symlink_path)) {
    echo "Points to: " . readlink($symlink_path) . "\n";
    echo "Valid: " . (is_dir($symlink_path) ? "✅ YES\n" : "❌ NO\n");
}

echo "\n📂 receipts folder:\n";
$receipts = $storage_path . '/receipts';
echo "Path: $receipts\n";
echo "Exists: " . (is_dir($receipts) ? "✅ YES\n" : "❌ NO\n");
if (is_dir($receipts)) {
    echo "Writable: " . (is_writable($receipts) ? "✅ YES\n" : "❌ NO\n");
    echo "Files: " . count(glob($receipts . '/*')) . "\n";
}

echo "\n🧪 Write Test:\n";
$test_file = $storage_path . '/test-' . time() . '.txt';
if (@file_put_contents($test_file, 'test')) {
    echo "✅ Can write to storage/app/public/\n";
    @unlink($test_file);
} else {
    echo "❌ CANNOT write to storage/app/public/ - Permission Denied\n";
}

echo "</pre>";
?>
