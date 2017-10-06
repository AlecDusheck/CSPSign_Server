<?php

//Normal User Routes (logged in and logged out)
$app->get("/", "home:index");
$app->post("/", "home:scheduleAnimation");

$app->get("/history", "history:index");

$app->get("/credit", "credit:index");

$app->get("/api/time", "api:getTime");
$app->get("/api/currentVersion", "api:getLatestVersion");
$app->get("/api/download", "api:download");
$app->get("/api/getAnimation", "api:getCurrentAnimation");
$app->get("/api/private/checkIn", "api:checkIn");
$app->get("/api/private/clearDb", "api:clearDb");