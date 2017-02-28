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

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\utils\TextFormat as TF;

use pocketmine\item\Item;

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
                    else {
                        
                        $def = [
                            "worldname" => $name,
                            "ip" => $e->getLine(1),
                            "port" => $e->getLine(2)
                        ];
                    
                        $cfg = new Config($this->getDataFolder() . "signs.yml", Config::YAML, array());
                        $cfg->setAll($def);
                        $cfg->save();
                        $p->sendMessage("Sign created!");
                        
                    }
                    
                }
                else {
                    $p->sendMessage(TF::RED . "You shouldn't be using this when you've no permission to use so");
                }
                  
            }
        
    }
    
    public function onTap(PlayerInteractEvent $ev) {
        
        $p = $ev->getPlayer();
        $item = $ev->getBlock()->getId();
        
            if($item === Item::SIGN or $item === Item::WALL_SIGN or $item === Item::SIGN_POST) {
                
                $sign = $ev->getBlock();
                
                    if($tile = $sign->getLevel()->getTile($sign)) {
                        
                        if($tile instanceof \pocketmine\tile\Sign) {
                            
                            if($ev->getBlock()->getX() === "0" && $ev->getBlock()->getY() === "0" && $ev->getBlock()->getZ() === "0") {
                            
                                if($tile->getText()[0] === strtolower("[Transfer]") or $tile->getText()[0] === strtolower("Transfer")) {
                                
                                        $pk = new \pocketmine\network\protocol\TransferPacket();
                                        $pk->port = (int) $tile->getText()[2];
                                        $pk->address = $tile->getText()[1];
                                        $p->dataPacket($pk);
                                        $p->sendMessage("Executing...");
                                
                                }
                            }
                        }
                        
                        
                    }
                
            }
        
    }
    
}