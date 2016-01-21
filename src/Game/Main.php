<?php

namespace SuperSpleef;
//必須
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
//イベント関連
use pocketmine\event\player\PlayerJoinEvent as PJE;
use pocketmine\event\player\PlayerQuitEvent as PQE;
use pocketmine\event\block\BlockPlaceEvent as BPE;
use pocketmine\event\block\BlockBreakEvent as BBE;
use pocketmine\event\entity\EntityDamageEvent as EDE;
use pocketmine\event\entity\EntityDamageByEntityEvent as EDBEE;
use pocketmine\scheduler\PluginTask;
//音・アイテム
use pocketmine\item\Item;
//Level関連
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Effect;
//cmd
use pocketmine\command\Command as cmd;
use pocketmine\command\CommandSender as cs;
class Main extends PluginBase implements Listener{
        public $T=10;
        public $G=false;
        public $T_T=10;
        public $T_G=300;
        public $M_G="SuperSpleef";
        public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getServer()->getScheduler()->scheduleRepeatingTask(new OneSecondGameTask($this),20);
	        Server::getInstance()->getNetwork()->setName("§l§o§cSuperSpleef!!§r§aPE");
	        $this->getServer()->loadLevel("Area1");
        }
      	public function onDisable(){
		      $this->SaiseiArea();
	      }
        /*入室イベント*/
        public function onPJE(PJE$e){
          $p=$e->getPlayer();
          $n=$p->getName();
          $e->setJoinMessage("§7入室>>".$n);
	        $p->setDisplayName("§f[§d待機§f]".$n);$p->setNameTag("§7[§d待機§7]".$n);
	        $effect = Effect::getEffect(16);//effectID
		      $effect->setDuration(3000*20);
      		$effect->setAmplifier(0);
      		$effect->setVisible(false);
      		$p->addEffect($effect);
      	}
        /*退室イベント*/
        public function onPQE(PQE$e){
	        p=$e->getPlayer();
          $e->setQuitMessage("§7退室>>".$p->getName());$p->setSpawn(new Position(128,12,128,Server::getInstance()->getLevelByName("Area1")));
	      }
        public function onEDE(EDE$e){$e->setCancelled();}
	      public function onBPE(BPE$e){if($e->getPlayer()->isOp()){return true;}$e->setCancelled();}
	      public function onBBE(BBE$e){if($e->getPlayer()->isOp()){return true;}$e->setCancelled();}
        /*人数取得関数 1秒に1回実行*/
        public function getPCs(){
          $a=0;$b=0;$c=0;$d=0;
          foreach(Server::getInstance()->getOnlinePlayers() as $p){
            $e=$p->getDisplayName();
            if(preg_match("/生存/",$e)){$a++;
            }elseif(preg_match("/死亡/",$e)){$b++;
            }elseif(preg_match("/待機/",$e)){$c++;}$d++;
          }return array($a,$b,$c,$d);/*0=生存 1=死亡 2=待機 3=全員*/}
        /*ゲーム関数 1秒に一回実行*/
        public function Game(){
          $pcs=$this->getPCs();$h=$pcs[0];$d=$pcs[1];$t=$pcs[2];$z=$pcs[3];
          if($this->G){
            if($z>=2){
              if($h>=2){
                if($this->T>0){
                  $this->T--;
                  Server::getInstance()->broadcastTip("§l§o§c".$this->M_G."!!§r\n§1残り時間".$this->T."秒§a生存§f:§0".$h."§f人  §c死亡§f:§0".$d."§f人");
		  $pl=Server::getInstance()->getOnlinePlayers();
		  $item=Item::get(275,0,1);	
		  $pos=new Position(128,12,128,Server::getInstance()->getLevelByName("Lobby"));
        	  foreach($pl as $p){
		    $food = $p->getFood();
                    if(preg_match("/生存/",$p->getDisplayName())){
		      if($food==0){
			$ph=$p->getHealth();
			if($ph<=1){
			  $n=$p->getName();
			  $p->setDisplayName("§f[§d死亡§f]".$n);
			  $p->setNameTag("§7[§d死亡§7]".$n);
			  Server::getInstance()->broadcastMessage("§c死亡>>".$n);
			  $p->teleport($pos);$p->setFood(20);$p->setHealth(20);
			}else{
			  $p->setHealth($ph-1);
			}
		      }elseif($food==20){
			$p->setHealth($p->getHealth()+1);
			$p->setFood($food-1);
		      }else{
		        $p->setFood($food-1);
		      }
                    }
		  }
                }else{
                  $this->StopGame("TimeOver");
                }
              }elseif($h==1){
                $this->StopGame("Win");
              }elseif($h==0){
		$this->StopGame("NoHuman");
	      }
            }else{
              $this->StopGame("NoPlayer");
            }
          }else{
            if($z>=2){
              $this->T--;
              $T=$this->T;
              if($T>=0){
                Server::getInstance()->broadcastTip("§l§o§c".$this->M_G."!!§r \n §0ゲーム§c開始§fまであと§a".$T."§f秒です。");
              }else{
                $this->StartGame();
              }
            }else{
              Server::getInstance()->broadcastTip("§l§o§c".$this->M_G."!!§r \n §0ゲーム§fには§c最低§02人§e必要§fです。");
              $this->T=$this->T_T;
            }
          }
      }
      public function StartGame(){
	Server::getInstance()->broadcastMessage("§9ゲーム説明§a------------------------");
	Server::getInstance()->broadcastMessage("§a----------------------------------");
        $this->G=true;
        $this->T=$this->T_G;
	$pl=Server::getInstance()->getOnlinePlayers();
	$item=Item::get(275,0,1);
	$pos=new Position(128,22,128,Server::getInstance()->getLevelByName("Area1"));
        foreach($pl as $p){$n=$p->getName();$p->setDisplayName("§f[§a生存§f]".$n);$p->setNameTag("§7[§a生存§7]".$n);$p->teleport($pos);$p->getInventory()->addItem($item);$p->setFood(20);$p->setHealth(20);}
	$this->Game();
      }
      public function StopGame($reason){
         $this->G=false;
         $this->T=$this->T_T;
         if($reason=="TimeOver"){
           Server::getInstance()->broadcastMessage("§9ゲーム終了§a------------------------");
           Server::getInstance()->broadcastMessage("§bΣ(ﾟ∀ﾟﾉ)ﾉｷｬｰ\n§c時間切れ!");
           Server::getInstance()->broadcastMessage("§a----------------------------------");
         }elseif($reason=="NoPlayer"){
           Server::getInstance()->broadcastMessage("§9ゲーム終了§a------------------------");
           Server::getInstance()->broadcastMessage("§c人数が2人未満のため終了しました。");
           Server::getInstance()->broadcastMessage("§a----------------------------------");
         }elseif($reason=="Win"){
	   $w="";
	   foreach(Server::getInstance()->getOnlinePlayers() as $p){
		$n=$p->getDisplayName();if(preg_match("/生存/",$n)){$w.=$p->getName();}
	 }
           Server::getInstance()->broadcastMessage("§9ゲーム終了§a------------------------");
           Server::getInstance()->broadcastMessage("§b勝者:".$n);
           Server::getInstance()->broadcastMessage("§a----------------------------------");
         }elseif($reason=="NoHuman"){
           Server::getInstance()->broadcastMessage("§9ゲーム終了§a------------------------");
           Server::getInstance()->broadcastMessage("§bΣ(ﾟ∀ﾟﾉ)ﾉｷｬｰエラーが発生しました");
           Server::getInstance()->broadcastMessage("§a----------------------------------");
         }
	 $pos=new Position(128,12,128,Server::getInstance()->getLevelByName("Lobby"));
	 $item=Item::get(275,0,1);
         foreach(Server::getInstance()->getOnlinePlayers() as $p){
		$n=$p->getName();$p->setDisplayName("§f[§d待機§f]".$n);$p->setNameTag("§7[§d待機§7]".$n);
		$p->teleport($pos);$p->setFood(20);$p->setHealth(20);
	 }
         $this->SaiseiArea();
       }
       	public function SaiseiArea(){
		$l=Server::getInstance()->getLevelByName("Area1");
		$l->setAutoSave(false);
		Server::getInstance()->unloadLevel($l);
		Server::getInstance()->loadLevel("Area1");
	}
}
class OneSecondGameTask extends PluginTask{
   public function __construct(PluginBase $owner) {parent::__construct($owner);$this->a=new Main();}
   public function onRun($c){$this->a->Game();}
}
