<?php

declare(strict_types=1);

namespace LobbyPlus\listeners;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use LobbyPlus\Main;
use pocketmine\event\Listener;

class InteractListener implements Listener
{
    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        if ($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        if ($this->plugin->hasItemTag($event->getItem())) {
            $tag = $this->plugin->getItemTag($event->getItem());
            if ($tag == "teleporter") {
                $event->setCancelled();
                $this->plugin->openTeleUI($player);
                return;
            }
            if ($tag == "playersvisibility") {
                $event->setCancelled();
                if ($event->getItem()->getCustomName() == $this->plugin->Config("settings.yml")->get("players_visible")) {
                    $online = $this->plugin->getServer()->getOnlinePlayers();
                    foreach ($online as $p) {
                        $player->hidePlayer($p);
                    }
                    $event->getItem()->setCustomName($this->plugin->Config("settings.yml")->get("players_invisible"));
                    $event->getPlayer()->getInventory()->setItemInHand($event->getItem());
                    $event->getPlayer()->sendTip($this->plugin->Config("messages.yml")->get("players_invisible"));
                } elseif ($event->getItem()->getCustomName() == $this->plugin->getConfig()->get("players_invisible")) {
                    $online = $this->plugin->getServer()->getOnlinePlayers();
                    foreach ($online as $p) {
                        $player->showPlayer($p);
                    }
                    $event->getItem()->setCustomName($this->plugin->Config("settings.yml")->get("players_visible"));
                    $event->getPlayer()->getInventory()->setItemInHand($event->getItem());
                    $event->getPlayer()->sendTip($this->plugin->Config("messages.yml")->get("players_visible"));
                }
                return;
            }
            if ($tag == "perks") {
                $event->setCancelled();
                if ($player->hasPermission("lobby.perk.admin") or $player->hasPermission("lobby.perk.fly") or $player->hasPermission("lobby.perk.speed")) {
                    $this->plugin->openPerksUI($player);
                } else {
                    $player->sendMessage($this->plugin->prefix . $this->plugin->Config("messages.yml")->get("cant_use_perks"));
                }
                return;
            }
            if ($tag == "info") {
                $event->setCancelled();
                $this->plugin->openHelpUI($player);
                return;
            }
            if ($tag == "custom") {
                $event->setCancelled();
                $cmd = $this->plugin->Config("settings.yml")->get("custom_command");
                if(strpos($cmd, "{asplayer}") == null){
                    $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                }else {
                    $cmd = str_replace("{asplayer}", '', $cmd);
                    $this->plugin->getServer()->dispatchCommand($player, $cmd);
                }
                return;
            }
        } else {
            if ($this->plugin->Config("settings.yml")->get("allow-interact") == false) {
                $event->setCancelled(true);
            }
        }
    }
}