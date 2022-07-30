<?php

namespace Polaris\command\groups;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use Polaris\forms\CustomForm;
use Polaris\forms\CustomFormResponse;
use Polaris\forms\element\Input;
use Polaris\forms\menu\Button;
use Polaris\forms\MenuForm;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\PlayerUtils;

class GroupsCommand extends Command{

    public function __construct(){
        parent::__construct("groups", "Groups Command", "/groups", ["groups"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender instanceof PolarisPlayer){
            $this->sendUi($sender);
        }
    }

    public function sendUi(PolarisPlayer $player){
        if ($player->getTeamManager()->hasTeam()){
            $team = $player->getTeamManager()->getTeam();
            $form = MenuForm::withOptions("§l§a".$team?->getName(), count($team->getMembers()) . "GroupsCommand.php/" .$team->getMaxMembers()." §c§aMembres" , ["Quitter l'équipe", "Envoyer un message"],
            function (PolarisPlayer $player, Button $selected) use ($team) {
                switch ($selected->text){
                    case "Invitez un membre":
                        $form = new CustomForm("§l§aInvitez un membre",[new Input("Pseudo", "")], function (PolarisPlayer $player, CustomFormResponse $response){
                            $value = $response->getInput()->getValue();
                            if(!is_null(($target = Server::getInstance()->getPlayerByPrefix($value))) && $target instanceof PolarisPlayer){
                                if($player->getTeamManager()->getTeam()?->isMember($target)){
                                    $player->sendMessage("§cCe joueur est déjà dans votre équipe");
                                }else{
                                    $player->sendMessage("§l§eVous avez invité §a".$target->getName()."§r à rejoindre votre équipe");
                                    $target->getTeamManager()->sendInvite($player, $player->getTeamManager()->getTeam());
                                }
                            }else{
                                $player->sendMessage("§cCe joueur n'est pas connecté");
                            }
                        });
                        $player->sendForm($form);
                        break;
                    case "Expulser un membre":
                        $options = [];
                        foreach ($team->getMembers() as $member){
                            $options[] = $member->getName();
                        }
                        $form = MenuForm::withOptions("§l§aExpulser un membre", "Quel joueur souhaitez vous expulsez?", $options,
                            function (PolarisPlayer $player, Button $selected) use ($team){
                            PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                                $team->kick($player);
                            }, " de vouloir expulser ce membre");
                        });
                        $player->sendForm($form);
                        break;
                    case  "Modifier le nom de l'équipe":
                        $form = new CustomForm("§l§aModifier le nom de l'équipe",[new Input("Nom de l'équipe", $team->getName())], function (PolarisPlayer $player, CustomFormResponse $response) use ($team) {
                            $value = $response->getInput()->getValue();
                            $team->setName($value);
                            $player->sendMessage("§l§aLe nom de l'équipe a bien été modifié");
                        });
                        $player->sendForm($form);
                        break;
                    case "Supprimer l'équipe":
                        PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                            $team->delete();
                            $player->sendMessage("Vous avez supprimé votre équipe");
                        }, " de vouloir supprimer l'équipe");
                        break;
                    case "Quitter l'équipe":
                        PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                            $team->leave($player);
                        }, " de vouloir quitter l'équipe");
                        break;
                    case "Envoyer un message":
                        $form = new CustomForm("§l§aEnvoyer un message",[new Input("Message", "")], function (PolarisPlayer $player, CustomFormResponse $response) use ($team) {
                            $value = $response->getInput()->getValue();
                            $team->sendMessage($value, $player->getName());
                        });
                        $player->sendForm($form);
                        break;
                    case "Passer en mode Premium":
                        if($team->isPremium()){
                            $player->sendMessage("§cVous êtes déjà en mode Premium");
                        }else{
                            PlayerUtils::sendVerification($player, function (PolarisPlayer $player) use ($team) {
                                $team->setPremium(true);
                                $player->sendMessage("Vous avez passé en mode Premium");
                            }, " de vouloir passer en mode Premium");
                        }
                        break;
                }
            });
            if($team->isOwner($player)){
                $form->appendOptions("Invitez un membre", "Expulser un membre", "Modifier le nom de l'équipe", "Supprimer l'équipe", "Passer en mode Premium");
            }
            $player->sendForm($form);
        }else{
            $form = MenuForm::withOptions("§l§bPolaris", "Créer ou Rejoindre une équipe", ["Créer une équipe", "Rejoindre une équipe"], function (PolarisPlayer $player, Button $selected) {
                switch ($selected->text){
                    case "Créer une équipe":
                        $form = new CustomForm("§l§aCréer une équipe",[new Input("Nom de l'équipe", "")], function (PolarisPlayer $player, CustomFormResponse $response) {
                            $value = $response->getInput()->getValue();
                            $player->getTeamManager()->createTeam($value);
                            $player->sendMessage("§l§aVous avez créé l'équipe §e$value §a!");
                        });
                        $player->sendForm($form);
                        break;
                    case "Rejoindre une équipe":
                        if (!is_null($player->getRequest("team"))){
                            $form = MenuForm::withOptions("§l§aRejoindre une équipe", "", array_merge($player->request["team"], "Retour"), function (PolarisPlayer $player, Button $selected) {
                                $team = Polaris::getTeam($selected->text);
                                if ($team?->isFull()){
                                    $player->sendMessage("§l§cL'équipe est pleine");
                                    return;
                                }
                                $player->sendMessage("§l§aVous avez rejoint l'équipe §e{$team?->getName()} §a!");
                                $team?->addMember($player);
                            });
                            $player->sendForm($form);
                        }else{
                            $player->sendMessage("§l§cVous n'avez pas de requête d'équipe");
                            $this->sendUi($player);
                        }
                        break;
                }
            });
            $player->sendForm($form);
        }
    }
}