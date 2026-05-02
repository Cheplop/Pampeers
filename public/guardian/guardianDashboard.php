<?php

require_once __DIR__ . '/../../app/middleware/auth.php';
requireRole('guardian');

require_once __DIR__ . '/../../app/controllers/user/fetchDashboard.php';
require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchAvail.php';

$user = $user ?? [];
$userCity = $user['cityMunicipality'] ?? 'Cagayan de Oro';

require_once __DIR__ . '/../../app/controllers/sitter/sitterFetchNear.php';

$sitters = $sitters ?? [];
$sittersNear = $sittersNear ?? [];

?>