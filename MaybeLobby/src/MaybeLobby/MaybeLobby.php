<?php

declare(strict_types=1);

namespace MaybeLobby;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class MaybeLobby extends PluginBase{

    protected EventListener $listener;

    public static array $config;

    public Config $name;

    public function returnToWorld(Player $player){
        $lobby_Name
            = $this->name->get("lobby_Name");

        $lobby
            = $this->getServer()->getWorldManager()->getWorldByName($lobby_Name);

        $default_World
            = $this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        if ($this->getServer()->getWorldManager()->isWorldGenerated($lobby_Name)){
            $player->teleport($lobby->getSafeSpawn());
        }else{
            $player->teleport($default_World);
        }

        $this->sendMenuToPlayer($player);
    }

    public function sendMenuToPlayer(Player $player){
        $config
            = $this->getConfig()->getAll();

        $player->getInventory()->clearAll();
        for($i = 1; $i <= 9; $i++){
            if(isset($config[$i]) && isset($config[$i]["item"])){
                    $id
                        = $config[$i]["item"]["id"];
                    $meta
                        = $config[$i]["item"]["meta"];
                    $name
                        = $config[$i]["name"];
                    $player->getInventory()->setItem($i - 1, (new Item(new ItemIdentifier($id, $meta)))->setCustomName($name));
            }
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if ($command->getName() == "hub" && $sender instanceof Player){
            $this->returnToWorld($sender);

            return true;
        }else{
            $sender->sendMessage("You are not player");
        }
        return true;
    }

    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->reloadConfig();

        $this->listener
            = new EventListener($this);
        $this->getServer()->getPluginManager()->registerEvents($this->listener, $this);

        $this->saveResource("name.yml");
        $this->name
            = new Config($this->getDataFolder() . "name.yml", Config::YAML);

        $lobby
            = $this->name->get("lobby_Name");

        if (!($this->getServer()->getWorldManager()->isWorldGenerated($lobby))){
            $this->getServer()->getWorldManager()->getWorldByName($lobby)->setTime(1000);
            $this->getServer()->getWorldManager()->getWorldByName($lobby)->stopTime();
        }else{
            $this->getServer()->getWorldManager()->getDefaultWorld()->setTime(1000);
            $this->getServer()->getWorldManager()->getDefaultWorld()->stopTime();
        }
    }
}