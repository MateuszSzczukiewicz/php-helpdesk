<?php
require 'includes/auth.php';
require 'includes/functions.php';

logoutUser();
redirect('login.php');
