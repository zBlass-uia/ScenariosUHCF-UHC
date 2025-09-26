<?php

namespace Scenarios;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

    public $events = ["AbsorptionAll", "SpeedBoost", "JumpBoost", "FireResistance", "MiningBoost"];
    public $currentEventIndex = 0;
    public $timer = 600;
    public $task;

    public function onEnable() {
        $this->getLogger()->info(TF::GREEN . "Scenarios Automático habilitado.");
        $this->startEventCycle();
    }

    public function onDisable() {
        if($this->task !== null){
            $this->getServer()->getScheduler()->cancelTask($this->task->getTaskId());
        }
        $this->getLogger()->info(TF::RED . "Scenarios Automático deshabilitado.");
    }

    private function startEventCycle() {
        $this->activateEvent($this->events[$this->currentEventIndex]);

        $plugin = $this;
        $this->task = $this->getServer()->getScheduler()->scheduleRepeatingTask(new class($plugin) extends Task {
            private $plugin;
            private $timeLeft;

            public function __construct($plugin){
                $this->plugin = $plugin;
                $this->timeLeft = $plugin->timer;
            }

            public function onRun($currentTick){
                $eventName = $this->plugin->events[$this->plugin->currentEventIndex];

                foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                    $player->sendPopup(TF::BOLD . TF::GOLD . $eventName . TF::RESET . TF::RED . ": " . gmdate("i:s", $this->timeLeft));
                }

                $this->timeLeft--;

                if($this->timeLeft <= 0){
                    $this->plugin->getServer()->broadcastMessage(TF::GOLD . "[Scenario] " . TF::RED . $eventName . " terminó! Próximo evento activándose...");
                    $this->plugin->nextEvent();
                    $this->timeLeft = $this->plugin->timer;
                }
            }
        }, 20);
    }

    private function activateEvent(string $event){
        foreach($this->getServer()->getOnlinePlayers() as $player){
            switch($event){
                case "AbsorptionAll":
                    $player->addEffect(new Effect(Effect::ABSORPTION, $this->timer*20, 0, false));
                    break;
                case "SpeedBoost":
                    $player->addEffect(new Effect(Effect::SPEED, $this->timer*20, 1, false));
                    break;
                case "JumpBoost":
                    $player->addEffect(new Effect(Effect::JUMP_BOOST, $this->timer*20, 1, false));
                    break;
                case "FireResistance":
                    $player->addEffect(new Effect(Effect::FIRE_RESISTANCE, $this->timer*20, 0, false));
                    break;
                case "MiningBoost":
                    $player->addEffect(new Effect(Effect::HASTE, $this->timer*20, 1, false));
                    break;
            }
        }
        $this->getServer()->broadcastMessage(TF::GOLD . "[Scenario] " . TF::RED . $event . " activado! Duración: 10 minutos.");
    }

    private function nextEvent(){
        $this->currentEventIndex++;
        if($this->currentEventIndex >= count($this->events)){
            $this->currentEventIndex = 0;
        }
        $this->activateEvent($this->events[$this->currentEventIndex]);
    }
}
