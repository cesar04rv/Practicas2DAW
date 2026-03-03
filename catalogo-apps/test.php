<?php
$hash = password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash generado: " . $hash . "<br><br>";

// Verificar que funciona
echo "Verificación: " . (password_verify('Admin1234!', $hash) ? 'OK' : 'FALLO');