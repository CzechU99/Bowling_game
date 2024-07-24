<?php 

  namespace App;
  
  use Exceptions\EndException;
  use Exceptions\LogicException;

  class Game{

    private int $frame = 1;
    private int $frameRoll = 1;
    private int $gameScore = 0;
    private int $currentFrameScore = 0;
    private int $globalRollsCounter = 1;

    private bool $statusStrike = false;
    private bool $statusEndGame = false;
    private bool $statusBonusRoll = false;

    private string $exceptionMessage = "";

    private array $rollsStates = [];
    private array $framesScores = [];
    private array $refFramesToPins = [];
    private array $historyOfKnockedPins = [];


    public function __construct(){
      $this->historyOfKnockedPins = array_fill(1, 21, 0);
    }

    public function isEndGame(): bool{
      return $this->statusEndGame;
    }

    public function getExceptionMessage(): string{
      return $this->exceptionMessage;
    }

    public function isStatusBonusRoll(): bool{
      return $this->statusBonusRoll;
    }

    public function getRollNumber(): int{
      return $this->frameRoll;
    }

    public function getFrameNumber(): int{
      return $this->frame;
    }

    public function getGameScore(): int{
      return $this->gameScore;
    }

    public function getCurrentFrameScore(): int{
      return $this->currentFrameScore;
    }

    public function getFramesScores(): array{
      return $this->framesScores;
    }

    public function roll(int $pins): void{
        try{
          $this->tryRollValid($pins);
          $this->processRoll($pins);
          $this->skipSecondRollDuringStrike();
          $this->setGameScore();
          $this->saveScoreInfoToRollsState();
          $this->updateStatusBonusInTenthFrame();
          $this->updateStatusEndGame();
          $this->prepareNextRoll();
          $this->updateGameForNextFrame();
        }catch(EndException $end){
          $this->exceptionMessage = $end->getMessage();
        }
    }

    private function prepareNextRoll(): void{
      $this->globalRollsCounter++;
      $this->frameRoll++;
    }

    private function processRoll(int $pins){
      $this->setCurrentFrameScore($pins);
      $this->setHistoryOfKnockedPins($pins);
      $this->createRefToKnockedPinsForFrame();
      $this->saveRollInfo($pins);
    }

    private function saveRollInfo(int $pins): void{
      $this->rollsStates[$this->globalRollsCounter]["frameNumber"] = $this->frame;
      $this->rollsStates[$this->globalRollsCounter]["frameRoll"] = $this->frameRoll;
      $this->rollsStates[$this->globalRollsCounter]["knockedPins"] = $pins;
    }

    private function saveScoreInfoToRollsState(): void{
      $this->rollsStates[$this->globalRollsCounter]["gameScore"] = $this->gameScore;
    }

    public function getRollsStates(): array{
      return $this->rollsStates;
    }

    private function updateGameForNextFrame(): void{
      if($this->frameRoll > 2 && !$this->statusBonusRoll){
        $this->currentFrameScore = 0;
        $this->frameRoll = 1;
        $this->frame++;
      }
    }

    public function tryRollValid(int $roll): bool{
      $firstValidation = ($roll >= 0 && $roll <= 10);
      if(!$firstValidation){
        throw new LogicException("PODANO ZLA LICZBE KREGLI - " . $roll);
      }

      if($this->isSecondValidation()){
        $sumOfKnockedPins = $roll + $this->currentFrameScore;
        $secondValidation = $sumOfKnockedPins >= 0 && $sumOfKnockedPins <= 10;
        if(!$secondValidation){
          throw new LogicException("SUMA ZBITYCH KREGLI W DWOCH RZUTACH SIE NIE ZGADZA - " . $sumOfKnockedPins);
        }
      }
      return true;
  }

  private function isSecondValidation(): bool{
      $twoRollsNotLastFrame = $this->frameRoll == 2 && $this->frame != 10; 
      $lastFrameNoBonus = $this->frameRoll == 2 && $this->frame == 10 && $this->currentFrameScore < 10;
      $strikeNotLastFrame = $this->frame != 10 && $this->currentFrameScore == 10;

      return $twoRollsNotLastFrame || $lastFrameNoBonus || $strikeNotLastFrame;
  }

    public function calculateFramesScores(): void{
      $sum = 0;
      $index = count(array_keys($this->refFramesToPins));
      for($i = 1; $i <= $index; $i++){
        foreach($this->refFramesToPins[$i] as $score){
          $sum += $score;
        }
        $this->framesScores[$i] = $sum;
      }
    }

    private function setHistoryOfKnockedPins($pins): void{
      $this->historyOfKnockedPins[$this->globalRollsCounter] = $pins;
    }

    private function setCurrentFrameScore($pins): void{
      $this->currentFrameScore += $pins;
    }

    private function createRefToKnockedPinsForFrame(): void{
      $this->refFramesToPins[$this->frame][$this->frameRoll] = & $this->historyOfKnockedPins[$this->globalRollsCounter];

      $conditionForSpare = $this->currentFrameScore == 10 && $this->frameRoll == 2;
      $conditionForStrike = $this->currentFrameScore == 10 && $this->frameRoll == 1 && !$this->statusBonusRoll;

      if($conditionForSpare){
        $this->refFramesToPins[$this->frame][$this->frameRoll + 1] = & $this->historyOfKnockedPins[$this->globalRollsCounter + 1];
      }elseif($conditionForStrike){
        $this->refFramesToPins[$this->frame][$this->frameRoll + 1] = & $this->historyOfKnockedPins[$this->globalRollsCounter + 1];
        $this->refFramesToPins[$this->frame][$this->frameRoll + 2] = & $this->historyOfKnockedPins[$this->globalRollsCounter + 2];
        $this->statusStrike = true;
      }
    }

    private function skipSecondRollDuringStrike(): void{
      if($this->frame < 10 && $this->statusStrike){
        $this->frameRoll++;
        $this->statusStrike = false;
      }
    }
    
    private function setGameScore(): void{
      $this->calculateFramesScores();
      $this->gameScore = 0;
      for($i = 1; $i <= $this->frame; $i++){
        foreach($this->refFramesToPins[$i] as $score){
          $this->gameScore += $score;
        }
      }
    }

    private function updateStatusBonusInTenthFrame(): void{
      $conditionBonusRoll = $this->frame == 10 && $this->currentFrameScore == 10;
      if($conditionBonusRoll){
        $this->statusBonusRoll = true;
      }
      
      if($this->frameRoll == 3){
        $this->statusBonusRoll = false;
      }
    }

    private function updateStatusEndGame(): void{
      if($this->frameRoll >= 2 && $this->frame >= 10 && !$this->statusBonusRoll){
        $this->statusEndGame = true;
        throw new EndException("KONIEC GRY!");
      }
    }

  }