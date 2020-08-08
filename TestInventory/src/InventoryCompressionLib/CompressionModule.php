<?php


namespace InventoryCompressionLib;


use pocketmine\item\Item;
use pocketmine\Player;

use function explode;
use function file_get_contents;
use function file_put_contents;
use function unlink;

class CompressionModule
{
    private const TYPE_ARMOR = 0;
    private const TYPE_ITEMS = 1;

    /*
     * Place the inventory back to the player
     */
    public function put(Player $player): void
    {
        if (!file_exists(InventoryCompression::getPlugin()->getServer()->getDataPath() . "/inventories_cache/{$player->getName()}.dat")) {
            if (InventoryCompression::getSettings()['logger']) {
                InventoryCompression::getPlugin()->getLogger()->warning('Are you sure the player has an inventory saved?');
            }
            return;
        }

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $handle = explode('/', $this->onDecode($player, self::TYPE_ITEMS));
        for ($i = 0; $i < $player->getInventory()->getSize(); $i++) {
            $item_handle = explode(':', $handle[$i]);
            $item = Item::get($item_handle[0], $item_handle[1], $item_handle[2]);
            $player->getInventory()->setItem($i, $item);
        }

        $handle = explode('/',  $this->onDecode($player, self::TYPE_ARMOR));
        $helmet = explode(':', $handle[0]);
        $player->getArmorInventory()->setHelmet(Item::get($helmet[0], $helmet[1], $helmet[2]));
        $chestplate = explode(':', $handle[1]);
        $player->getArmorInventory()->setChestplate(Item::get($chestplate[0], $chestplate[1], $chestplate[2]));
        $leggings = explode(':', $handle[2]);
        $player->getArmorInventory()->setLeggings(Item::get($leggings[0], $leggings[1], $leggings[2]));
        $boots = explode(':', $handle[3]);
        $player->getArmorInventory()->setBoots(Item::get($boots[0], $boots[1], $boots[2]));
    }

    /*
     * Save the inventory so that we can read it later
     */
    public function save(Player $player): void
    {
        $items = $this->onEncode($player, self::TYPE_ITEMS);
        $armor = $this->onEncode($player, self::TYPE_ARMOR);
        $format = "{$items}&{$armor}";
        file_put_contents(
            InventoryCompression::getPlugin()->getServer()->getDataPath() . "/inventories_cache/{$player->getName()}.dat",
            $format
        );
        if (InventoryCompression::getSettings()['logger']) {
            InventoryCompression::getPlugin()->getLogger()->info("New inventory saved, owned by {$player->getName()}");
        }
    }

    /*
     * Delete a player's saved inventory
     */
    public function pop(string $name): void
    {
        $cache = InventoryCompression::getPlugin()->getServer()->getDataPath() . "/inventories_cache/{$name}.dat";
        if (file_exists($cache)) {
            unlink($cache);

            if (InventoryCompression::getSettings()['logger']) {
                InventoryCompression::getPlugin()->getLogger()->info("{$name} Inventory was removed!");
            }
        }
    }

    private function onEncode(Player $player, int $type): string
    {
        $compression = '';
        if ($type == self::TYPE_ARMOR) {
            $helmet = $player->getArmorInventory()->getHelmet();
            $compression .= "{$helmet->getId()}:{$helmet->getDamage()}:{$helmet->getCount()}/";
            $chestplate = $player->getArmorInventory()->getChestplate();
            $compression .= "{$chestplate->getId()}:{$chestplate->getDamage()}:{$chestplate->getCount()}/";
            $leggings = $player->getArmorInventory()->getLeggings();
            $compression .= "{$leggings->getId()}:{$leggings->getDamage()}:{$leggings->getCount()}/";
            $boots = $player->getArmorInventory()->getBoots();
            $compression .= "{$boots->getId()}:{$boots->getDamage()}:{$boots->getCount()}";
            return $compression;
        }
        if ($type === self::TYPE_ITEMS) {
            for ($i = 0; $i < $player->getInventory()->getSize(); $i++) {
                $item = $player->getInventory()->getItem($i);
                if ($i != $player->getInventory()->getSize()) {
                    $compression .= "{$item->getId()}:{$item->getDamage()}:{$item->getCount()}/";
                } else {
                    $compression .= "{$item->getId()}:{$item->getDamage()}:{$item->getCount()}";
                }
            }
            return $compression;
        }
    }

    public function onDecode(Player $player, int $type): string
    {
        $decompress = '';
        $cache = file_get_contents(InventoryCompression::getPlugin()->getServer()->getDataPath() . "/inventories_cache/{$player->getName()}.dat");
        if ($type == self::TYPE_ARMOR) {
            $decompress = explode('&', $cache)[1];// items&[armor]
        }
        if ($type == self::TYPE_ITEMS) {
            $decompress = explode('&', $cache)[0];// [items]&armor
        }
        return $decompress;
    }
}