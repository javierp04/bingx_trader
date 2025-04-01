<?php
// Test script to verify password functionality
// Save this as test_password.php in your project root

// The default admin password in the database
$stored_hash = '$2y$10$ZfNb6chG.25JxDiXEI9.uOhGWHrUV0XOB.9AyxXuy3QB1z3QavKIy';

// The plaintext password should be "admin123"
$passwords_to_try = [
    'admin',
    'admin123',
    'password',
    'admin2023',
    '12345678'
];

echo "PHP Version: " . phpversion() . "\n";
echo "Testing password verification with different passwords...\n\n";

foreach ($passwords_to_try as $password) {
    echo "Testing password: " . $password . "\n";
    if (password_verify($password, $stored_hash)) {
        echo "SUCCESS! This is the correct password.\n\n";
    } else {
        echo "Failed. This is not the correct password.\n\n";
    }
}

// Create a new hash for comparison
echo "Creating a new password hash for 'admin123':\n";
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo $new_hash . "\n";
?>