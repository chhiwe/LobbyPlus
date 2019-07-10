<?php

declare(strict_types=1);

namespace LobbyPlus\commands;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\entity\Skin;
use pocketmine\Player;
use LobbyPlus\Main;

class hub extends Command
{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("hub", "Return to the lobby spawn", "hub", ["Lobby"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $level = $this->plugin->getServer()->getLevelByName($this->plugin->getConfig()->get("lobby-level"));
        $sender->teleport($level->getSafeSpawn());
}
}