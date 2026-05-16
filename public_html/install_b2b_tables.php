<?php
require_once 'db.php';

try {
    // Create b2b_agents table
    $sql = "CREATE TABLE IF NOT EXISTS b2b_agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'b2b_agents' created or already exists.<br>";

    // Try to add agent_id to existing visa_orders table
    try {
        $sql = "ALTER TABLE visa_orders ADD COLUMN agent_id INT DEFAULT NULL";
        $pdo->exec($sql);
        echo "Column 'agent_id' added to visa_orders table.<br>";
        
        $sql = "ALTER TABLE visa_orders ADD CONSTRAINT fk_agent_id FOREIGN KEY (agent_id) REFERENCES b2b_agents(id) ON DELETE SET NULL";
        $pdo->exec($sql);
        echo "Foreign key constraint added to visa_orders table.<br>";
    } catch (\PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
             echo "Column 'agent_id' already exists in visa_orders table.<br>";
        } else {
             echo "Notice: Could not alter visa_orders table: " . $e->getMessage() . "<br>";
        }
    }

    echo "<h3>Setup Complete</h3>";

} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
