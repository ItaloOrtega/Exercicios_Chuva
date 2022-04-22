<?php

namespace Galoa\ExerciciosPhp2022\War\GamePlay\Country;

/**
 * Defines a country that is managed by the Computer.
 */
class ComputerPlayerCountry extends BaseCountry {

  /**
   * Choose one country to attack, or none.
   *
   * The computer may choose to attack or not. If it chooses not to attack,
   * return NULL. If it chooses to attack, return a neighbor to attack.
   *
   * It must NOT be a conquered country.
   *
   * @return \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface|null
   *   The country that will be attacked, NULL if none will be.
   */
  public function chooseToAttack(): ?CountryInterface {
    $neighbors = $this->getNeighbors();
    $resu = rand(0, sizeof($neighbors));
    #Se o valor for igual a 0, não querer atacar, ou o pais tiver a qtd de tropas <= 1...
    #Não será possivel atacar
    if($this->getNumberOfTroops() <= 1 or $resu == 0){
      print " ".$this->getName()." não vai atacar\n";
      return NULL;
    }
    else
      return $neighbors[$resu-1];
  }

}
