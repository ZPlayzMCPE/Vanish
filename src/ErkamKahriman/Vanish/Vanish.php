<?php

namespace ErkamKahriman\Vanish;

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Vanish extends PluginBase implements Listener {

    const PREFIX = C::BLUE."Vanish".C::DARK_GRAY." > ".C::RESET;

    public $vanish = array();

    /** @var Vanish */
    public static $instance;

    public function onEnable(){
        self::$instance = $this;
        $this->getScheduler()->scheduleRepeatingTask(new VanishTask(), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function getInstance() : Vanish{
        return self::$instance;
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        $name = $sender->getName();
        if($cmd->getName() == "supervanish"){
            if($sender instanceof Player){
                if($sender->hasPermission("supervanish.spectate")){
                    if($this->vanish[$name] == false){
                        $this->vanish[$name] = true;
                        $sender->sendMessage(self::PREFIX.C::GREEN."§dYou are now vanished. §5No one can see you.");
						$sender->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), (99999999*20), (1), (false)));
                        $sender->getPlayer()->addTitle("§6§lVanish Mode", "§5§lis enabled!", 40, 100, 40);
                        $this->getServer()->broadcastMessage(C::GREEN . "§c$name §ehas left the game.");
                    } else{
                        $this->vanish[$name] = false;
                        foreach($this->getServer()->getOnlinePlayers() as $players){
                            $players->showPlayer($sender);
                        }
						$sender->sendMessage(self::PREFIX . C::RED . "§dYou are no longer vanished! §bEveryone can now see you!");
                        $sender->removeEffect(Effect::NIGHT_VISION);
                        $sender->getPlayer()->addTitle("§6§lVanish mode", "§c§lis Disabled", 40, 100, 40);
                        $this->getServer()->broadcastMessage(C::RED . "§a$name §ehas joined the game");
                    }
                }
            }
        }
        return true;
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!isset($this->vanish[$name])) $this->vanish[$name] = false;
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if($this->vanish[$name] == true) $this->vanish[$name] = false;
    }

    public function onDisable(){
        $this->getLogger()->info(C::RED."Deaktiviert.");
    }
}