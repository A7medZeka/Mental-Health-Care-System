<?php
/**
 * Create Upload Directories
 * Creates the necessary directory structure for file uploads
 */

// Create uploads directory structure
$directories = [
    'uploads',
    'uploads/intake',
    'uploads/credentials',
    'uploads/avatars',
    'uploads/documents'
];

$baseDir = __DIR__;

echo "Creating upload directories...\n";

foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "✅ Created: $dir\n";
        } else {
            echo "❌ Failed to create: $dir\n";
        }
    } else {
        echo "✅ Already exists: $dir\n";
    }
    
    // Set proper permissions
    if (is_dir($fullPath)) {
        chmod($fullPath, 0755);
    }
}

echo "\nUpload directory structure created successfully!\n";
echo "Files will be uploaded to:\n";
echo "- Intake forms: uploads/intake/\n";
echo "- Therapist credentials: uploads/credentials/\n";
echo "- User avatars: uploads/avatars/\n";
echo "- General documents: uploads/documents/\n";
?>
