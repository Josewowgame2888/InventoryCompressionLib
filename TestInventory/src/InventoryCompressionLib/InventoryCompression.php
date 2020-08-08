<?php


namespace InventoryCompressionLib;


use pocketmine\plugin\PluginBase;

use function mkdir;

class InventoryCompression
{
    /** @var PluginBase */
    private static $plugin;
    /** @var CompressionModule */
    private static $module;
    /** @var array */
    private static $settings;

    public static function initialize(PluginBase $plugin, array $setting): void
    {
        self::$plugin = $plugin;
        self::$module = new CompressionModule();

        if (!file_exists($plugin->getServer()->getDataPath() . '/inventories_cache')) {
            @mkdir($plugin->getServer()->getDataPath() . '/inventories_cache');
        }

        if (isset($setting['auto-workers'], $setting['logger'])) {
            self::$settings = $setting;

            if ($setting['auto-workers']) {
                $plugin->getServer()->getPluginManager()->registerEvents(new InventoryCompressionEvent(), $plugin);
            }

            if ($setting['logger']) {
                $plugin->getLogger()->info('Library loaded! created by Josewowgame');
            }

        } else throw new \RuntimeException('Invalid arguments when trying to initialize the library configuration');
    }

    public static function getPlugin(): PluginBase
    {
        return self::$plugin;
    }

    public static function getCompression(): CompressionModule
    {
        return self::$module;
    }

    public static function getSettings(): array
    {
        return self::$settings;
    }
}