<?php


namespace test;


use InventoryCompressionLib\InventoryCompression;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    public function onEnable(): void
    {
        InventoryCompression::initialize($this, [
            'logger' => true,
            'auto-workers' => false
        ]);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        if ($message === 'save') {//guardar
            InventoryCompression::getCompression()->save($player);
        }

        if ($message === 'load') {//colocar inventorio
            InventoryCompression::getCompression()->put($player);
        }

        if ($message === 'delete') {//eliminar inventorio
            InventoryCompression::getCompression()->pop($player->getName());
        }
    }
}