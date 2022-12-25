<?php

declare(strict_types=1);

namespace MaybeLobby;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{

    public function __construct(private MaybeLobby $plugin){ }

    /**
     * @param PlayerJoinEvent $event
     *
     * @priority NORMAL
     */
    public function onJoinPlayer(PlayerJoinEvent $event){
        $player
            = $event->getPlayer();
        $owner_Name
            = $this->plugin->name->get("owner_Name");

        if ($player->getName() == $owner_Name){
            $player->setNameTag(TextFormat::BLUE . $player->getName());
        } elseif ($event->getPlayer()->getServer()->isOp($event->getPlayer()->getName())){
            $player->setNameTag(TextFormat::GOLD . $player->getName());
        } else{
           $player->setNameTag(TextFormat::BLUE . $player->getName());
        }

        $event->setJoinMessage(TextFormat::GREEN . "+ " . $player->getName());
        $this->plugin->sendMenuToPlayer($player);
        $this->plugin->returnToWorld($player);
    }

    /**
     * @param PlayerQuitEvent $event
     *
     * @priority NORMAL
     */
    public function onQuitPlayer(PlayerQuitEvent $event){
        $event->setQuitMessage(TextFormat::RED . "- " . $event->getPlayer()->getName());
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @priority NORMAL
     */
    public function onEntityDamage(EntityDamageEvent $event){
        $lobby
            = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->name->get("lobby_Name"))->getId();

        $entity_World
            = $event->getEntity()->getWorld()->getId();

        if ($lobby === $entity_World){
                $event->cancel();
        }
    }

    /**
     * @param PlayerMoveEvent $event
     *
     * @priority NORMAL
     */
    public function onPlayerMove(PlayerMoveEvent $event){
        $player
            = $event->getPlayer();

        $lobby
            = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->name->get("lobby_Name"))->getId();

        $posY
            = $event->getPlayer()->getPosition()->getY();

        $default_World
            = $event->getPlayer()->getWorld()->getId();

        if ($lobby === $default_World && $posY < 0){
                $this->plugin->returnToWorld($player);
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     *
     * @priority NORMAL
     */
    public function onPlayerUseItem(PlayerItemUseEvent $event){
        $default_World
            = $event->getPlayer()->getWorld()->getId();

        $lobby
            = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->name->get("lobby_Name"))->getId();

        $player
            = $event->getPlayer();

        $config
            = $this->plugin->getConfig()->getAll();

        if ($lobby === $default_World){
            foreach($config as $value){
                if(isset($value["item"]["id"]) && isset($value["name"])){
                    $itemInHand = $player->getInventory()->getItemInHand();
                    if($value["item"]["id"] === $itemInHand->getId() && $value["name"] === $itemInHand->getCustomName()){
                        $event->cancel();

                        if(!isset($value["commands"])){
                            return;
                        }

                        foreach($value["commands"] as $command){
                            $event->getPlayer()->getServer()->dispatchCommand($player, $command);
                        }
                    }
                }
            }
        }
    }
}