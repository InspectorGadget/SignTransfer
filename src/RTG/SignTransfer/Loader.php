<?php

/* 
 * Copyright (C) 2017 RTG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace RTG\SignTransfer;

/* Essentials */
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\network\protocol\TransferPacket;
use pocketmine\utils\Config;

class Loader extends PluginBase implements Listener {
    
    public $cfg;
    
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $cfg = new Config($this->getDataFolder() . "signs.yml", Config::YAML);
    }
    
    public function onChange(SignChangeEvent $e) {
        
        $p = $e->getPlayer();
        $name = $e->getBlock()->getLevel()->getName();
        
            if(strtolower($e->getLine(0) === "[Transfer]" or $e->getLine(0) === "Transfer")) {
                if($p->hasPermission("transfersign.create") or $p->isOp()) {
                    
                    if(strtolower($e->getLine(1) === "")) {
                        
                        $p->sendMessage("Please add your IP");
                        $e->setCancelled();
                        
                    }
                    
                    if(strtolower($e->getLine(2) === "")) {
                        
                        $p->sendMessage("Add your PORT!");
                        $e->setCancelled();
                        
                    }
                    
                    if(!is_numeric($e->getLine(2))) {
                        $e->setCancelled();
                        $p->sendMessage("Your PORT has to be in numeric form!");
                    }
                    
                    $p->sendMessage("Sign created!");
                    
                    
                    /* Transfer Packets */
                    
                    $pk = new \pocketmine\network\protocol\TransferPacket();
                    $pk->port = (int) $e->getLine(2);
                    $pk->address = $e->getLine(1);
                    $p->dataPacket($pk);
                    $p->sendMessage("Executing...");
                    
                    $def = [
                        "name" => $name,
                        "x" => "0",
                        "y" => "0",
                        "z" => "0",
                        "ip" => $e->getLine(1),
                        "port" => $e->getLine(2),
                        "enabled" => "true"
                    ];
                    
                    $cfg = new Config($this->getDataFolder() . "signs.yml", Config::YAML);
                    $cfg->setAll($def);
                    $cfg->save();
                    
                }
                else {
                    $p->sendMessage(TF::RED . "You shouldn't be using this when you've no permission to use so");
                }
                  
            }
        
    }
    
}