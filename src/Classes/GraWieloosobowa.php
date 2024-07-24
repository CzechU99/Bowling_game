<?php 

    namespace App;

    use App\Game;

    class GraWieloosobowa{

        public static $maxLiczbaGraczy = 6;

        private int $liczbaGraczy = 0;
        private int $wskaznikGracza = 1;

        private array $stanyGryGraczy = [];

        public function __construct(int $liczbaGraczy){
            $this->liczbaGraczy = $liczbaGraczy;

            for($i = 0; $i < $this->liczbaGraczy; $i++){
                array_push($this->stanyGryGraczy, new Game());
            }
        }

        public function getStanyGryGraczy(): array{
            return $this->stanyGryGraczy;
        }

        public function getNumerRzutuGracza(): int{
            return $this->stanyGryGraczy[$this->wskaznikGracza - 1]->getRollNumber();
        }

        public function getGraczZakonczylGre(): bool{
            return $this->stanyGryGraczy[$this->wskaznikGracza - 1]->isEndGame();
        }

        public function getWskaznikGracza(): int{
            return $this->wskaznikGracza;
        }

        public function czyPierwszyGraczZakonczylGre(): bool{
            return $this->stanyGryGraczy[0]->isEndGame();
        }

        public function getNumerRundyGracza(): int{
            return $this->stanyGryGraczy[$this->wskaznikGracza - 1]->getFrameNumber();
        }






        

        public function czyGraNadalTrwa(): bool {
            foreach($this->stanyGryGraczy as $index => $stanGryGracza){
                if($index == array_key_last($this->stanyGryGraczy) && $stanGryGracza->isEndGame()){
                    return false;
                }
            }
            return true;
        }

        public function obsluzRzut(int $rzut): void{
            if($this->czyGraNadalTrwa()){
                $this->wykonajRzutNaGraczu($rzut);
                $this->aktualizujWskaznikGracza();
            }
        }

        private function wykonajRzutNaGraczu(int $rzut): void{
            $this->stanyGryGraczy[$this->wskaznikGracza - 1]->roll($rzut);
        }

        private function aktualizujWskaznikGracza(){
            if($this->czyWszyscyGraczeMajaTaSamaRunde() && !$this->czyPierwszyGraczZakonczylGre()){
                $this->wskaznikGracza = 1;
            }

            if(!$this->czyWszyscyGraczeMajaTaSamaRunde() && $this->getNumerRzutuGracza() == 1){
                $this->wskaznikGracza++;
            }elseif($this->getNumerRundyGracza() == 10 && $this->getGraczZakonczylGre() && $this->czyGraNadalTrwa()){
                $this->wskaznikGracza++;
            }
        }

        public function czyWszyscyGraczeMajaTaSamaRunde(): bool {
            $rundaPierwszegoGracza = $this->stanyGryGraczy[0]->getFrameNumber();
            foreach ($this->stanyGryGraczy as $stanGryGracza) {
                if ($stanGryGracza->getFrameNumber() != $rundaPierwszegoGracza) {
                    return false;
                }
            }
            return true;
        }















        public function wylaczWskaznikGracza(): void{
            if(!$this->czyGraNadalTrwa()){
                $this->wskaznikGracza = 0;
            }
        }

        public function getInfoORundzie(): array{
            return array(
                "Runda -" . $this->stanyGryGraczy[$this->wskaznikGracza - 1]->getFrameNumber(), 
                "Rzut -" . $this->stanyGryGraczy[$this->wskaznikGracza - 1]->getRollNumber()
            );
        }

        public function obliczKtoryGraczZwyciezyl(): array {
            $zwyciezcy = [];
            $wynikiGraczy = [];
            $maksymalnyWynik = null;

            $this->zapiszWynikKazdegoGracza($wynikiGraczy);
            $this->znajdzNajwiekszyWynik($wynikiGraczy, $maksymalnyWynik);
            $this->zapiszIndeksyZwyciezcow($wynikiGraczy, $maksymalnyWynik, $zwyciezcy);
        
            return $zwyciezcy;
        }

        private function zapiszWynikKazdegoGracza(&$wynikiGraczy){
            foreach($this->stanyGryGraczy as $stanGryGracza){
                array_push($wynikiGraczy, $stanGryGracza->getGameScore());
            }
        }

        private function znajdzNajwiekszyWynik($wynikiGraczy, &$maksymalnyWynik){
            foreach ($wynikiGraczy as $wynik) {
                if ($maksymalnyWynik == null || $wynik > $maksymalnyWynik) {
                    $maksymalnyWynik = $wynik;
                }
            }
        }

        private function zapiszIndeksyZwyciezcow($wynikiGraczy, $maksymalnyWynik, &$zwyciezcy){
            foreach ($wynikiGraczy as $klucz => $wynik) {
                if ($wynik == $maksymalnyWynik) {
                    $zwyciezcy[] = $klucz;
                }
            }
        }

        public function getWynikiKazdejRundyGraczy(): array{
            $wynikiGraczyZRund = [];
            foreach($this->stanyGryGraczy as $stanGryGracza){
                array_push($wynikiGraczyZRund, $stanGryGracza->getFramesScores());
            }
            return $wynikiGraczyZRund;
        }

        public function getBledyGraczy(): array{
            $bledyGraczy = [];
            foreach($this->stanyGryGraczy as $stanGryGracza){
                array_push($bledyGraczy, $stanGryGracza->getExceptionMessage());
            }
            return $bledyGraczy;
        }

        public function getInfoOWyniku(): string{
            if($this->getNumerRzutuGracza() == 1 && $this->wskaznikGracza == 1 && $this->getNumerRundyGracza() != 1){
                return $this->stanyGryGraczy[$this->liczbaGraczy - 1]->getGameScore();
            }elseif($this->getNumerRzutuGracza() == 1 && $this->wskaznikGracza != 1){
                return $this->stanyGryGraczy[$this->wskaznikGracza - 2]->getGameScore();
            }
            return $this->stanyGryGraczy[$this->wskaznikGracza - 1]->getGameScore();
        }

        public function getStanyRzutowGraczy(): array{
            $stanyRzutow = [];
            foreach($this->stanyGryGraczy as $stanGryGracza){
                array_push($stanyRzutow, $stanGryGracza->getRollsStates());
            }
            return $stanyRzutow;
        }

    }