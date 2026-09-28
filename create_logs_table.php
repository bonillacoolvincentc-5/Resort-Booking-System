<?php
require 'c:/xampp/htdocs/YP4K_Resort/phpAuthentication/db_config.php';
$conn->query('CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT, 
    role VARCHAR(50), 
    action_type VARCHAR(50), 
    description TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');
echo $conn->error;
?>
