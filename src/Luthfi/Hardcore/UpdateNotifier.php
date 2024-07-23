<?php

namespace Luthfi\Hardcore;

use pocketmine\plugin\PluginBase;

class UpdateNotifier {
    private $plugin;
    private $currentVersion;
    private $updateURL;

    public function __construct(PluginBase $plugin, string $updateURL) {
        $this->plugin = $plugin;
        $this->currentVersion = $plugin->getDescription()->getVersion();
        $this->updateURL = $updateURL;
    }

    public function checkForUpdates(): void {
        $this->plugin->getServer()->getAsyncPool()->submitTask(new class($this->updateURL, $this->currentVersion, $this->plugin) extends \pocketmine\scheduler\AsyncTask {
            private $url;
            private $currentVersion;
            private $plugin;

            public function __construct(string $url, string $currentVersion, PluginBase $plugin) {
                $this->url = $url;
                $this->currentVersion = $currentVersion;
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                $data = file_get_contents($this->url);
                if ($data === false) {
                    return;
                }

                $pluginYML = yaml_parse($data);
                if ($pluginYML === false || !isset($pluginYML["version"])) {
                    return;
                }

                $latestVersion = $pluginYML["version"];
                if (version_compare($latestVersion, $this->currentVersion, '>')) {
                    $this->setResult(true);
                } else {
                    $this->setResult(false);
                }
            }

            public function onCompletion(): void {
                if ($this->getResult()) {
                    $this->plugin->getLogger()->info("A new version of the plugin is available!");
                } else {
                    $this->plugin->getLogger()->info("You are running the latest version of the plugin.");
                }
            }
        });
    }
}
