<?php
session_start();
$_SESSION['test'] = 'ok';
echo isset($_SESSION['test']) ? 'Session working' : 'Session failed';
