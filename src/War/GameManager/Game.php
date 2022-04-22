<?php

namespace Galoa\ExerciciosPhp2022\War\GameManager;

use Galoa\ExerciciosPhp2022\War\GamePlay\Battlefield;
use Galoa\ExerciciosPhp2022\War\GamePlay\BattlefieldInterface;
use Galoa\ExerciciosPhp2022\War\GamePlay\Country\ComputerPlayerCountry;
use Galoa\ExerciciosPhp2022\War\GamePlay\Country\HumanPlayerCountry;

/**
 * Defines a Game, it holds the players and interacts with the UI.
 */
class Game {

  /**
   * The battlefield.
   *
   * @var \Galoa\ExerciciosPhp2022\War\GamePlay\BattlefieldInterface
   */
  protected $battlefield;

  /**
   * The countries in the game, including conquered ones, indexed by name.
   *
   * @var \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface[]
   */
  protected $countries;

  /**
   * Instantiates a new game.
   */
  public static function create(): Game {
    return new static(new Battlefield(), CountryList::createWorld());
  }

  /**
   * Builder.
   *
   * @param \Galoa\ExerciciosPhp2022\War\GamePlay\BattlefieldInterface $battlefield
   *   The battle field.
   * @param \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface[] $countries
   *   A list of countries.
   */
  public function __construct(BattlefieldInterface $battlefield, array $countries) {
    $this->battlefield = $battlefield;
    $this->countries = $countries;
  }

  /**
   * Plays the game.
   */
  public function play(): void {
    $i = 0;
    while (!$this->gameOver()) {
      $i++;
      print "===== Rodada # $i =====\n";
      $this->stats($i);
      $this->playRound();
    }
  }

  /**
   * Display stats.
   */
  public function stats(int $i): void {
    foreach ($this->countries as $country) {
      #Adiciona tropas apos cada rodada 3 para todos não-conquistados e 1 a mais para cada pais conquistado
      if($i > 1 and $country->isConquered()==FALSE){
        $country->numberOfTroops = $country->numberOfTroops+3;
        if(property_exists($country, "numberOfConquered") == TRUE)
          $country->numberOfTroops = $country->numberOfTroops+$country->numberOfConquered;
      }
      print "  " . $country->getName() . ": " . ($country->isConquered() ? "DERROTADO" : $country->getNumberOfTroops() . " tropas") . "\n";
    }
  }

  /**
   * Displays the game results.
   */
  public function results(): void {
    $countries = $this->getUnconqueredCountries();
    // Should have only one.
    if ($winner = reset($countries)) {
      print "~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~\n";
      print "~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~\n";
      print "*~*~*". $winner->getName() . " conquistou toda a Terra-Média!!!*~*~\n";
      print "~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~\n";
      print "~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~\n";
    }

    $this->stats(1);
  }

  /**
   * Plays one round.
   */
  protected function playRound(): void {
    foreach ($this->getUnconqueredCountries() as $attackingCountry) {
      print "----- Vez de " . $attackingCountry->getName() . "\n";
      $defendingCountry = NULL;
      if ($attackingCountry instanceof ComputerPlayerCountry) {
        print "Países Vizinhos:\n";
        foreach($attackingCountry->getNeighbors() as $element){
          print " ".$element->getName()."\n";
        }
        $defendingCountry = $attackingCountry->chooseToAttack();
      }
      elseif ($attackingCountry instanceof HumanPlayerCountry) {
        $neighbors = $attackingCountry->getNeighbors();
        $defendingCountryName = NULL;
        #Mostra os paises vizinhos disponiveis para o usuario
        print "Países Vizinhos:\n";
        foreach($neighbors as $element){
          print " ".$element->getName()."\n";
        }
        do {#While até o usuario atacar um pais valido ou nenhum
          $flag = FALSE;
          $typedName = readline("Digite o nome de um país para atacar ou deixe em branco para não atacar ninguém:");
          $defendingCountryName = trim($typedName);
          #A resposta sendo somente espaços em brancos, vale como que o usuario não quer atacar
          if(strlen($defendingCountryName) == 0){
            print " ".$attackingCountry->getName()." não vai atacar\n";
            $flag = TRUE;
          }
          foreach($neighbors as $element){ #Procura o nome digitado nos vizinhos
            #Existindo um vizinho com esse nome, a flag é verdadeira e sai do While
            if($defendingCountryName == $element->getName()){
              $flag = TRUE;
              break;
            }
          }
      }
        while ($flag == FALSE);
        #Existindo um pais a ser atacado
        if (strlen($defendingCountryName) > 0 )
          $defendingCountry = $this->countries[$defendingCountryName];

      }

      // If there is an attack, let's do battle.
      if ($defendingCountry) {
        print "  vai atacar " . $defendingCountry->getName() . "\n";

        $attackingDice = $this->battlefield->rollDice($attackingCountry, TRUE);
        $defendingDice = $this->battlefield->rollDice($defendingCountry, FALSE);

        print "  dados de " . $attackingCountry->getName() . ": " . implode("-", $attackingDice) . "\n";
        print "  dados de " . $defendingCountry->getName() . ": " . implode("-", $defendingDice) . "\n";

        $this->battlefield->computeBattle($attackingCountry, $attackingDice, $defendingCountry, $defendingDice);

        if ($defendingCountry->isConquered()) {
          $attackingCountry->conquer($defendingCountry);
          print "  " . $defendingCountry->getName() . " foi anexado por " . $attackingCountry->getName() . "!\n";
          
          #Percorre todos os paises não conquistados e troca o nome do pais que acabou de ser conquistado...
          #pelo nome do pais que o conquistou
          foreach ($this->getUnconqueredCountries() as $unconqueredCountries) {
            for($i=0;$i<sizeof($unconqueredCountries->neighbors);$i++){

              if($unconqueredCountries->neighbors[$i]->getName() == $defendingCountry->getName() and 
              $unconqueredCountries->getName() != $attackingCountry->getName())
                $unconqueredCountries->neighbors[$i] = $attackingCountry;
            }
          }
        }
        else {
          print "  " . $defendingCountry->getName() . " conseguiu se defender!\n";
        }
      }
      sleep(1);
    }
  }

  /**
   * Checks is the game is complete.
   *
   * @return bool
   *   TRUE if the game is over, FALSE otherwise.
   */
  protected function gameOver(): bool {
    // If there is only one remaining country, the game is over.
    return count($this->getUnconqueredCountries()) <= 1;
  }

  /**
   * Lists countries that have not been conquered.
   *
   * @return \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface[]
   *   An array of countries.
   */
  protected function getUnconqueredCountries(): array {
    return array_filter($this->countries, function($country) {
      return !$country->isConquered();
    });
  }

}
