<?php

declare(strict_types=1);

require 'includes/auth.php';
require 'includes/functions.php';

logoutUser();
redirect('login.php');
