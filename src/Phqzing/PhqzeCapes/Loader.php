<?php

namespace Phqzing\PhqzeCapes;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\entity\Skin;
use pocketmine\command\{CommandSender, Command};
use pocketmine\utils\TextFormat as TE;
use dktapps\pmforms\{MenuForm, MenuOption};

class Loader extends PluginBase {


    public function onEnable():void
    {
        @mkdir($this->getDataFolder()."capes");
    }


    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool
    {
        switch($cmd->getName())
        {
            case "capes":
                if($sender instanceof Player)
                {
                    if(!isset($args[0]))
                    {
                        $sender->sendForm($this->mainCapesForm());
                        return true;
                    }
                    if($args[0] == "remove")
                    {
                        if(!isset($args[1]))
                        {
                            $sender->sendMessag(TE::RED."Please specify what cape you want to delete");
                        }
                        $file = $this->getDataFolder()."capes/".$args[1].".png";
                        if(is_file($file))
                        {
                            unlink($file);
                            $sender->sendMessage(TE::GREEN."Successfully removed the cape with the name ".TE::GRAY.$args[1]);
                        }else{
                            $sender->sendMessage(TE::RED."Cape does not exist. Please make sure you typed in the right cape name.");
                        }
                    }
                }else{
                    $sender->sendMessage("You can only use this command in-game.");
                }
            break;
        }
        return true;
    }


    public function mainCapesForm():MenuForm
    {
        foreach($this->getCapesList() as $capes)
        {
            $capeList = [$capes];
            $buttons = [
                new MenuOption($capes)
            ];
        }
        $disableButton = new MenuOption(TE::RED."Disable Cape");
        array_push($capesList, "disable");
        array_push($buttons, $disableButton);

        return new MenuForm
        (
            TE::BOLD.TE::GREEN."Capes",
            "",
            $buttons,

            function(Player $player, int $data)use($capeList):void
            {
                $clicked = $capList[$data];
                if($clicked == "disable")
                {
                    $this->equipCape($player);
                    return;
                }
                $this->equipCape($player, $clicked);
            }
        );
    }

    public function equipCape(Player $player, $cape = null):void
    {
        if(is_null($player)) return;

        $skin = $player->getSkin();

        if(!is_null($cape))
        {
            $capeData = $this->createCapeFromPNG($cape);
        }else{
            $capeData = "";
        }

        $setCape = new Skin($skin->getSkinId(), $skin->getSkinData(), $capeData, $skin->getGeometryName(), $skin->getGeometryData());
        $player->setSkin($setCape);
        $player->sendSkin();
    }


    public function createCapeFromPNG(string $cape)
    {
        $file = $this->getDataFolder()."capes/".$cape.".png";
        $img = @imagecreatefrompng($file);
        $data = '';
        $l = (int)@getimagesize($file)[1];
        for ($y = 0; $y < $l; $y++) 
        {
            for ($x = 0; $x < 64; $x++) 
            {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $data .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $data;
    }

    public function getCapesList():array
    {
        $capes = [];
        foreach(array_diff(scandir($this->getDataFolder()."capes"), ["..", "."]) as $files)
        {
            $data = explode(".", $files);
            if($data[1] == "png")
            {
                array_push($capes, $data[0]);
            }
        }
        return $capes;
    }
}
