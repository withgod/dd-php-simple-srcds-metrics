<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use DataDog\DogStatsd;

$statsd = new DogStatsd(
    array('host' => $_ENV['DOGSTATSD_HOST'], 'port' => $_ENV['DOGSTATSD_PORT'])
  );

$rcon = new srcds_rcon($_ENV['RCON_HOST'], $_ENV['RCON_PORT'], $_ENV['RCON_PASSWORD']);

$ret = $rcon->command('stats');
$lines = preg_split('/[\r\n]+/', $ret);
# cpu, in_kb, out_kb, uptime, map_changes, fps, players, connects
$stats = preg_split('/ +/', $lines[1]);

// var_dump($stats);

$statsd->gauge('srcds.cpu', (double)$stats[0]);
$statsd->gauge('srcds.uptime', (int)$stats[3]);
$statsd->gauge('srcds.fps', (double)$stats[5]);
$statsd->gauge('srcds.users', (int)$stats[6]);

echo join(", ", $stats);
echo "\n";

