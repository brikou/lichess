<?php

use Bundle\LichessBundle\Persistence\FilePersistence;
use Bundle\LichessBundle\Chess\Generator;
use Bundle\LichessBundle\Chess\Manipulator;
use Bundle\LichessBundle\Chess\Synchronizer;
use Bundle\LichessBundle\Entities\Game;

require_once(__DIR__.'/../../../../autoload.php');
require_once(__DIR__.'/../../../../../lichess/LichessKernel.php');

$kernel = new LichessKernel('test', true);
$kernel->boot();
$client = $kernel->getContainer()->getTest_ClientService();

$game = $kernel->getContainer()->getLichessGeneratorService()->createGame();
$gameHash = $game->getHash();
$player = $game->getPlayer('white');
$game->setCreator($player);
$game->setStatus(Game::STARTED);
for($it=0; $it<50; $it++) {
    $game->getRoom()->addMessage($it%2 ? 'white' : 'black', str_repeat('blah blah ', rand(1, 10)));
}
$playerHash = $player->getFullHash();
$opponent = $player->getOpponent();
$opponentHash = $opponent->getFullHash();
$dir = sys_get_temp_dir();
$persistence = $kernel->getContainer()->getLichessPersistenceService();
$persistence->save($game);

$iterations = 100;

$start = microtime(true);
for($it=0; $it<$iterations; $it++) {
    $client->request('POST', '/sync/'.$playerHash);
}
$time = 1000 * (microtime(true) - $start);
printf('%d syncs in %01.2f ms'."\n", $iterations, $time);
printf('%01.2f ms per sync'."\n", $time/$iterations);
