<?php
echo "admin123 => " . password_hash("admin123", PASSWORD_DEFAULT) . PHP_EOL;
echo "teacher123 => " . password_hash("teacher123", PASSWORD_DEFAULT) . PHP_EOL;
echo "student123 => " . password_hash("student123", PASSWORD_DEFAULT) . PHP_EOL;
?>