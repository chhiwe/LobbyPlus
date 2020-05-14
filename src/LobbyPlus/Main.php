<?php

namespace LobbyPlus;

use LobbyPlus\listeners\InteractListener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use LobbyPlus\commands\hub;

class main extends PluginBase implements Listener{
    public $prefix = "§r[§l§aLobbyPlus§r] ";
    public $messages;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new InteractListener($this), $this);
        $this->getServer()->getCommandMap()->register("LobbyPlus", new hub($this));
        $this->saveResource("settings.yml");
        $this->saveResource("messages.yml");
        $this->messages = $this->Config("messages.yml");
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("ver"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("tell"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("help"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("me"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("say"));
    }

    public function onDisable(){
        $this->getLogger()->info("LobbyPlus is deactivating...");
    }

    public function onJoin(PlayerJoinEvent $event){
        $one = new Item(Item::EMERALD);
        $one->setCustomName($this->Config("settings.yml")->get("players_visible"));
        $this->addItemTag($one, "playersvisibility");

        $two = new Item(Item::SKULL);
        $two->setCustomName($this->Config("settings.yml")->get("custom"));
        $this->addItemTag($two, "custom");

        $three = new Item(Item::COMPASS);
        $three->setCustomName($this->Config("settings.yml")->get("teleporter"));
        $this->addItemTag($three, "teleporter");

        $four = new Item(Item::GUNPOWDER);
        $four->setCustomName($this->Config("settings.yml")->get("perks"));
        $this->addItemTag($four, "perks");

        $five = new Item(Item::TORCH);
        $five->setCustomName($this->Config("settings.yml")->get("info"));
        $this->addItemTag($five, "info");
        $player = $event->getPlayer();
        $player->getInventory()->clearAll();
        $player->getInventory()->setItem(0, $one);
        $player->getInventory()->addItem(Item::get(Item::PAPER)->setCustomName("§r§1"));
        $player->getInventory()->setItem(2, $two);
        $player->getInventory()->addItem(Item::get(Item::PAPER)->setCustomName("§r§2"));
        $player->getInventory()->setItem(4, $three);
        $player->getInventory()->addItem(Item::get(Item::PAPER)->setCustomName("§r§3"));
        $player->getInventory()->setItem(6, $four);
        $player->getInventory()->addItem(Item::get(Item::PAPER)->setCustomName("§r§4"));
        $player->getInventory()->setItem(8, $five);
        $player->removeAllEffects();
        $message = $this->Config("messages.yml")->get("JoinMessage");
        $message = str_replace("%player%", $player->getName(), $message);
        $event->setJoinMessage($message);
        $pmessage = $this->Config("messages.yml")->get("PrivateMessage");
        $pmessage = str_replace("%player%", $player->getName(), $pmessage);
        $player->sendMessage($pmessage);
    }
    public function onDrop(PlayerDropItemEvent $event){
        if($this->Config("settings.yml")->get("allow-dropping") == false){
            $event->setCancelled(true);
            $player = $event->getPlayer();
            $player->sendMessage($this->prefix . $this->messages->get("Drop_Items"));
        }
    }
    public function onConsume(PlayerItemConsumeEvent $event){
        if($this->Config("settings.yml")->get("allow-consume") == false) {
            $event->setCancelled(true);
            $player = $event->getPlayer();
            $player->sendMessage($this->prefix . $this->messages->get("Consume_items"));
        }
    }
    public function onCrafting(CraftItemEvent $event){
        if($this->Config("settings.yml")->get("allow-crafting") == false) {
            $event->setCancelled(true);
            $player = $event->getPlayer();
            $player->sendMessage($this->prefix . $this->messages->get("Craft_items"));
        }
    }

    public function onBuild(BlockPlaceEvent $event){
        if(!$event->getPlayer()->hasPermission("lobbyplus.build")){
            $event->setCancelled();
        }
    }

    public function addItemTag(item $item, string $tag) : item{
        $nbt = $item->getNamedTag();
        $nbt->setString("lobbyplus", $tag, true);
        $item->setCompoundTag($nbt);
        return $item;
    }

    public function removeItemTag(item $item) : item{
        $nbt = $item->getNamedTag();
        $nbt->removeTag("lobbyplus");
        $item->setCompoundTag($nbt);
        return $item;
    }

    public function hasItemTag(item $item) : bool{
        $nbt = $item->getNamedTag();
        return $nbt->hasTag("lobbyplus", StringTag::class);
    }

    public function getItemTag(item $item) : string{
        $nbt = $item->getNamedTag();
        return $nbt->getString("lobbyplus");
    }

        public function onDeath(EntityDamageEvent $event)
{
    if($event->getEntity() instanceof Player) {
        if($event->getEntity()->getHealth() < $event->getFinalDamage()) {
            if ($this->Config("settings.yml")->get("allow-death") == false) {
                $event->setCancelled(true);
                $player = $event->getEntity();
                $player->sendMessage($this->prefix . $this->Config("messages.yml")->get("death"));
            }
        }
    }
}

    public function onDamage(EntityDamageEvent $event)
    {
        if($this->Config("settings.yml")->get("allow-damage") == false) {
            $event->setCancelled(true);
        }
    }

    public function onHunger(PlayerExhaustEvent $event){
        if($this->Config("settings.yml")->get("allow-hunger") == false) {
            $event->setCancelled(true);
        }
    }

    public function onTransact(InventoryTransactionEvent $event){
        if(!$event->getTransaction()->getSource()->hasPermission("lobbyplus.transact"))
        $event->setCancelled(true);
    }

    public function onBreak(BlockBreakEvent $event){
        if(!$event->getPlayer()->hasPermission("lobbyplus.break")){
            $event->setCancelled();
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        if($this->Config("settings.yml")->get("custom-quitmessage") == true) {
            $player = $event->getPlayer();
            $message = $this->Config("messages.yml")->get("LeaveMessage");
            $message = str_replace("%player%", $player->getName(), $message);
            $event->setQuitMessage($message);
        }
    }

    public function onChat(PlayerChatEvent $event){
        if($this->Config("settings.yml")->get("allow-chat") == false) {
            if (!$event->getPlayer()->isOp() && !$event->getPlayer()->hasPermission("lobbyplus.chat")) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->prefix . $this->messages->get("Chat"));
            }
        }
    }

    
    public function Config($file){
        $file = new Config($this->getDataFolder().$file, 2);
        return $file;
    }
    public function openHelpUI($player){
        $form = new SimpleForm(function (Player $player, int $data = null){

            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    break;
            }

        });

        $form->setTitle($this->Config("messages.yml")->get("InfoTitle"));
        $form->setContent($this->Config("messages.yml")->get("InfoContent"));
        $form->addButton($this->Config("messages.yml")->get("InfoButton"));
        $form->sendToPlayer($player);
        return $form;

    }
    public function openTeleUI($player){
        $form = new SimpleForm(function (Player $player, $data = null){

            $result = $data;
            if($result === null){
                return true;
            }
            $result = explode(":", $result);
            $ip = $result[0];
            $port = $result[1];
            $player->transfer($ip, $port);
            return false;
        });

        $form->setTitle($this->Config("messages.yml")->get("NavigatorTitle"));
        $form->setContent($this->Config("messages.yml")->get("NavigatorContent"));
        foreach($this->Config("settings.yml")->get("servers") as $server) {
            $form->addButton($server[0], -1, "", implode(":", [$server[1], $server[2]]));
        }
        $form->sendToPlayer($player);
        return $form;
    }

    public function openPerksUI($player){
        $form = new SimpleForm(function (Player $player, int $data = null){

            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    if ($player->hasPermission("lobby.perk.fly") or $player->hasPermission("lobby.perk.admin")){
                        if ($player->getAllowFlight() == true){
                            $player->setAllowFlight(false);
                            $player->sendMessage($this->prefix . $this->Config("messages.yml")->get("fly_off"));
                        }else{
                            $player->setAllowFlight(true);
                            $player->sendMessage($this->prefix . $this->Config("messages.yml")->get("fly_on"));
                        }
                    }
                    break;
                case 1:
                    if ($player->hasPermission("lobby.perk.speed") or $player->hasPermission("lobby.perk.speed")){
                        if($player->hasEffect(1)){
                            $player->removeEffect(1);
                            $player->sendTip($this->Config("messages.yml")->get("speed_off"));

                        }else {
                            $effect = new EffectInstance(Effect::getEffect(1), 100000, 2, false);
                            $player->addEffect($effect);
                            $player->sendTip($this->Config("messages.yml")->get("speed_on"));
                        }
                    }
                //For now we use effects, later something else
            }
            return false;
        });

        $form->setTitle($this->Config("messages.yml")->get("PerksTitle"));
        $form->setContent($this->Config("messages.yml")->get("PerksContent"));
        $form->addButton("Fly");
        $form->addButton("Speed");
        $form->sendToPlayer($player);
        return $form;

    }

}
