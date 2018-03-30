<?php

namespace xdban;
    
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener{

	public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§e".$this->getName()." §bhas been load and activated | created by §ekorado531m7");
        @mkdir($this->getDataFolder(), 0777, true);
		if(!file_exists($this->getDataFolder() . "baninfo.yml")){
			new Config($this->getDataFolder()."baninfo.yml", Config::YAML);
			$this->getServer()->getLogger()->notice("Ban information data has been created");
		}
	}

    public function onlogin(PlayerPreLoginEvent $event){
        $player = $event->getPlayer();
        $info = new Config($this->getDataFolder() . "baninfo.yml", Config::YAML);
		foreach($info->getAll() as $key => $value){
			if($key == strtolower($player->getName()) || $value[0] == $player->getAddress()){
				if($value[2] == "unlimited" || $value[1] + $value[2] > time()){
					if($value[2] == "unlimited"){
						$remain = "無期限";
					}else{
						$remain = ceil((($value[1] + $value[2]) - time()) / 3600)."時間";
					}
					$event->setKickMessage("§cあなたはBanされています\n§b理由: §d".$value[3]."\n§bBan解除まで §e".$remain." §7(§bBanされた日付: §e".date("Y/m/d H:i.s",$value[1])."§7)");
					$event->setCancelled(true);
				}else{
					$info->remove(strtolower($player->getName()));
					$info->save();
				}
			}
		}
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $params) : bool{
        if($command->getName() == "b"){
			if($sender instanceof Player && $sender->isOp() || !($sender instanceof Player)){
				if(empty($params[0])){
                    $sender->sendMessage("§9> §b/b <§eBanする名前§b> <§eBanする理由§b> <§eBanする時間(hour)(ない場合は無期限)§b>");
                }else{
					if(empty($params[1])) $params[1] = "理由なし";
					if(empty($params[2])) $params[2] = "unlimited";
					if($this->getServer()->getPlayer($params[0]) === null){
                        $name = $params[0];
						$ip = "";
                    }else{
                        $name = $this->getServer()->getPlayer($params[0])->getName();
						$ip = $this->getServer()->getPlayer($params[0])->getAddress();
						$this->getServer()->getPlayer($params[0])->kick("§cKICKED BY ADMIN",false);
                    }
					if(is_numeric($params[2])){
                        $time = $params[2] * 3600;
                    }else{
                        $time = "unlimited";
                    }
					$config = new Config($this->getDataFolder()."baninfo.yml", Config::YAML);
					$data = $config->getAll();
					if(array_key_exists($name,$data)){
                        $sender->sendMessage("§9> §dすでにBanされています");
                    }else{
						$config->set(strtolower($name),array($ip,time(),$time,$params[1]));
                        $sender->sendMessage("§9> §e".$name." §7(".$ip."§7) §bを §e".$params[2]."時間 §bBanしました");
                        $config->save();
                    }
				}
			}else{
				$sender->sendMessage("§cYou don't have permission to perform this command");
			}
			return true;
		}
	}
}