<?php

namespace Luthfi\Hardcore;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\lang\TranslationContainer;

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

        $banList = $this->getServer()->getNameBans();
        $banEntry = new \pocketmine\ban\BanEntry($player->getName());
        $banEntry->setReason(TextFormat::colorize($banMessage));
        $banEntry->setExpires(new \DateTime("@" . (time() + 60 * $banMinutes)));
        $banList->addBan($banEntry);

        $player->kick(new TranslationContainer("multiplayer.disconnect.banned", [$banMessage]), false);

        $this->getScheduler()->scheduleDelayedTask(new UnbanTask($this, $player->getName()), 20 * 60 * $banMinutes);
    }

    public function unbanPlayer(string $playerName): void {
        $banList = $this->getServer()->getNameBans();
        $banList->remove($playerName);
    }
}

class UnbanTask extends Task {
    private $plugin;
    private $playerName;

    public function __construct(Main $plugin, string $playerName) {
        $this->plugin = $plugin;
        $this->playerName = $playerName;
    }

    public function onRun(): void {
        $this->plugin->unbanPlayer($this->playerName);
    }
}
