<?php


namespace InventoryCompressionLib;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class InventoryCompressionEvent implements Listener
{
    public function onQuit(PlayerQuitEvent $event): void
    {
        InventoryCompression::getCompression()->save($event->getPlayer());
    }

    public function onKick(PlayerQuitEvent $event): void
    {
        InventoryCompression::getCompression()->save($event->getPlayer());
    }
}