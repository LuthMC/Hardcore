<?php

namespace Luthfi\Hardcore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class ConfigChecker {
    private $plugin;
    private $config;

    public function __construct(PluginBase $plugin, Config $config) {
        $this->plugin = $plugin;
        $this->config = $config;
    }

    public function checkConfig(): void {
        if (!is_int($this->config->get("ban_minutes")) || $this->config->get("ban_minutes") <= 0) {
            $this->plugin->getLogger()->warning("Invalid value for 'ban_minutes' in config.yml. Using default value (5).");
            $this->config->set("ban_minutes", 5);
            $this->config->save();
        }

        if (!is_string($this->config->get("ban_message")) || empty($this->config->get("ban_message"))) {
            $this->plugin->getLogger()->warning("Invalid value for 'ban_message' in config.yml. Using default value.");
            $this->config->set("ban_message", "&cYou have been banned for dying! Please wait {minutes} minutes.");
            $this->config->save();
        }

        if (!is_int($this->config->get("hearts")) || $this->config->get("hearts") <= 0) {
            $this->plugin->getLogger()->warning("Invalid value for 'hearts' in config.yml. Using default value (1).");
            $this->config->set("hearts", 1);
            $this->config->save();
        }
    }
}
