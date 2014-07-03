<?php
// Default to a failing response status
header('Internal Server Error', true, 500);
require "../vendor/autoload.php";

$pusher = new pusher\Pusher("https://4d2c3d146b1b662605b7:2063025205ec9774f5e1@api.pusherapp.com/apps/79075");
