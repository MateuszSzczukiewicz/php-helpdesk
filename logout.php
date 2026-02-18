<?php

declare(strict_types=1);

require 'includes/auth.php';

logoutUser();
redirect('login.php');
