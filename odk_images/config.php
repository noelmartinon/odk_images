<?php
/*
 * Description: Display image from blob stores in ODK Aggregate and ODK Central databases
 * Version: 1.0.0
 * Author: Noël MARTINON
 * Licence: GPLv3
 */

// PostgreSQL server ODK Aggregate
$aggregate_host= '192.168.1.10';
$aggregate_port = '5432';
$aggregate_db = 'aggregate_db';
$aggregate_user = 'aggregate_user';
$aggregate_password = 'aggregate_password';
$aggregate_schema = 'aggregate';

// PostgreSQL server ODK Central
$central_host= '192.168.1.10';
$central_port = '5432';
$central_db = 'odk';
$central_user = 'central_user';
$central_password = 'central_password';
$central_schema = 'public';

// Options
$autorotate = true;
$jpeg_quality = 90; // 0 (worse quality) to 100 (best)
$png_quality = 1; // 0 (no compression) to 9
