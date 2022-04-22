<?php

namespace Galoa\ExerciciosPhp2022\War\GamePlay\Country;

/**
 * Defines a country, that is also a player.
 */
class BaseCountry implements CountryInterface {

  /**
   * The name of the country.
   *
   * @var string
   */
  protected $name;

  /**
   * Builder.
   *
   * @param string $name
   *   The name of the country.
   */
  public function __construct(string $name) {
    $this->name = $name;
  }

  public function getName(): string{
    return $this->name;
  }

  /**
   * Sets the neighbors of this country.
   *
   * This method is run ONLY ONCE on the game creation. You must handle the
   * addition of additional neighbors in the conquer() method.
   *
   * @param \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface[] $neighbors
   *   An array of countries that neighbor this country, indexed by their names.
   */
  public function setNeighbors(array $neighbors): void{
    $this->neighbors = $neighbors;
  }

  /**
   * Lists the neighbors of a country.
   *
   * When the country is initialized, it receives an array of neighbours by the
   * game manager. Before any round, this array is exactly what getNeighbors()
   * should return.
   *
   * When a country conquers another, it should add this country neighbors to
   * its own. You should make sure, however, that you do NOT duplicated
   * countries in the array, nor return the current country as itself.
   *
   * @return \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface[]
   *   The country's neighbors.
   */
  public function getNeighbors(): array{
    return $this->neighbors;
  }

  /**
   * Returns how many troops there currently are in this country.
   *
   * @return int
   *   The number of troops this country has.
   */
  public function getNumberOfTroops(): int{
    if(property_exists($this, "numberOfTroops") == FALSE){
      $this->numberOfTroops = 3;
    }
    return $this->numberOfTroops;
  }

  /**
   * Determines whether the player has been conquered.
   *
   * When a country is conquered, its object is not destroyed but it will be
   * flagged as "conquered", so that the game manager knows it will no longer be
   * playing. Your code should handle this flag and return the information
   * properly.
   *
   * @return bool
   *   If this country has been conquered by someone else, this method will
   *   return TRUE.
   */
  public function isConquered(): bool{
    if ($this->getNumberOfTroops() == 0)
      return TRUE;
    else
      return FALSE;
  }

  /**
   * Called when, after a battle, the defending country end up with 0 troops.
   *
   * Here, you must register the neighbors of the conquered country as your own.
   *
   * @param \Galoa\ExerciciosPhp2022\War\GamePlay\Country\CountryInterface $conqueredCountry
   *   The country that has just been conquered.
   */
  public function conquer(CountryInterface $conqueredCountry): void{
    #Percorre os vizinhos do conquistador e do conquistado
    $aux = [];
    #Pega todos os vizinhos do conquistado, não contendo o pais conquistador
    foreach($conqueredCountry->getNeighbors() as $neighbors){
      if($neighbors->getName() != $this->getName())
        $aux[] = $neighbors;
    }
    #Pega todos os vizinhos do conquistador, não contendo o pais conquistado
    foreach($this->getNeighbors() as $neighbors){
      if($neighbors->getName() != $conqueredCountry->getName())
        $aux[] = $neighbors;
    }
    #É alocado os novos vizinhos no objeto
    $this->neighbors = $aux;
    #Adicionar/Cria numeros de paises conquistados
    if(property_exists($this, "numberOfConquered") == FALSE){
      $this->numberOfConquered = 1;
    }
    else
      $this->numberOfConquered++;
  }

  /**
   * Decreases the number of troops in this country by a given number.
   *
   * @param int $killedTroops
   *   The number of troops killed in battle.
   */
  public function killTroops(int $killedTroops): void{
     $this->numberOfTroops = $this->numberOfTroops - $killedTroops;
  }

}
