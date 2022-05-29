<?php

namespace UnknowL\Command\Groups;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\forms\CustomForm;
use UnknowL\forms\CustomFormResponse;
use UnknowL\forms\element\Input;
use UnknowL\forms\menu\Button;
use UnknowL\forms\MenuForm;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\PlayerUtils;

class GroupsCommand extends Command{

    public function __construct(){
        parent::__construct("groups", "Groups Command", "/groups", ["g"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender instanceof PolarisPlayer){
            $this->sendUi($sender);
        }
    }

    public function sendUi(PolarisPlayer $player){
        if ($player->getTeamManager()->hasTeam()){
            $team = $player->getTeamManager()->getTeam();
            $form = MenuForm::withOptions("§l§aPolaris", $team->getName()." Team", ["Quitter l'équipe", "Envoyer un message"],
            function (PolarisPlayer $player, Button $selected) use ($team) {
                switch ($selected->text){
                    case "Invitez un membre":
                        $form = new CustomForm("§l§aInvitez un membre",[new Input("Pseudo", "")], function (PolarisPlayer $player, CustomFormResponse $response){
                            $value = $response->getInput()->getValue();
                            if(!is_null(($target = Server::getInstance()->getPlayerByPrefix($value))) && $target instanceof PolarisPlayer){
                                $target->getTeamManager()->sendInvite($player, $player->getTeamManager()->getTeam());
                            }
                        });
                        $player->sendForm($form);
                        break;
                    case "Expulser un membre":
                        $form = MenuForm::withOptions("§l§aExpulser un membre", "Quel joueur souhaitez vous expulsez?", $team->getMembers(),
                            function (PolarisPlayer $player, Button $selected) use ($team){
                            PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                                $team->kick($player);
                            }, " de vouloir expulser ce membre");
                        });
                        break;
                    case  "Modifier le nom de l'équipe":
                        $form = new CustomForm("§l§aModifier le nom de l'équipe",[new Input("Nom de l'équipe", $team->getName())], function (PolarisPlayer $player, CustomFormResponse $response) use ($team) {
                            $value = $response->getInput()->getValue();
                            $team->setName($value);
                        });
                        $player->sendForm($form);
                        break;
                    case "Supprimer l'équipe":
                        PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                            $team->delete();
                        }, " de vouloir supprimer l'équipe");
                        break;
                    case "Quitter l'équipe":
                        PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                            $team->kick($player);
                        }, " de vouloir quitter l'équipe");
                        break;
                    case "Envoyer un message":
                        $form = new CustomForm("§l§aEnvoyer un message",[new Input("Message", "")], function (PolarisPlayer $player, CustomFormResponse $response) use ($team) {
                            $value = $response->getInput()->getValue();
                            $team->sendMessage($value, $player->getName());
                        });
                        $player->sendForm($form);
                        break;
                }
            });
            if($team->isOwner($player)){
                $form->appendOptions("Invitez un membre", "Expulser un membre", "Modifier le nom de l'équipe", "Supprimer l'équipe");
            }
        }else{
            $player->sendMessage("Vous n'avez pas d'équipe");
        }
    }
}