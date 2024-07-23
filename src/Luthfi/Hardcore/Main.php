<?php

namespace Luthfi\Hardcore;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class Main extends PluginBase implements Listener {

    private $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        
        $configChecker = new ConfigChecker($this, $this->config);
        $configChecker->checkConfig();

        $updateNotifier = new UpdateNotifier($this, "https://raw.githubusercontent.com/LuthMC/Hardcore/main/plugin.yml");
        $updateNotifier->checkForUpdates();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $hearts = $this->config->get("hearts", 1);
        $player->setMaxHealth($hearts * 2);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $banMinutes = $this->config->get("ban_minutes", 5);
        $banMessage = str_replace("{minutes}", $banMinutes, $this->config->get("ban_message", "You have been banned for dying!"));
        
        $player->setBanned(true, TextFormat::colorize($banMessage));
        $this->getScheduler()->scheduleDelayedTask(new UnbanTask($this, $player->getName()), 20 * 60 * $banMinutes);
    }

    public function unbanPlayer(string $playerName): void {
        $player = Server::getInstance()->getOfflinePlayer($playerName);
        $player->setBanned(false);
    }
}

class UnbanTask extends \pocketmine\scheduler\Task {
    private $plugin;
    private $playerName;

    public function __construct(Main $plugin, string $playerName) {
        $this->plugin = $plugin;
        $this->playerName = $playerName;
    }

    public function onRun(int $currentTick): void {
        $this->plugin->unbanPlayer($this->playerName);
    }
}
