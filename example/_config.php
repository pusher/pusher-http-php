<?php
ini_set("log_errors", true);
ini_set("display_errors", "stderr");
error_reporting(E_ALL & ~E_DEPRECATED);

// Default to a failing response status
header('Internal Server Error', true, 500);
require "../vendor/autoload.php";

$pusher = new pusher\Pusher("https://9cd6b8b5de12be68eb47:7c1c1189729651b135f1@api.pusherapp.com/apps/79075");
