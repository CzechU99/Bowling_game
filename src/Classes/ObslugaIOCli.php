<?php 
    
    namespace App;

    class ObslugaIOCli{

        private int $rzut = 0; 
        private int $liczbaGraczy = 0;

        public function getRzut(): int{
            return $this->rzut;
        }

        public function getLiczbaGraczy(): int{
            return $this->liczbaGraczy;
        }

        public function wczytajRzut(): void{
            $rzut = (int)readline();
            $this->rzut = $rzut;
        }

        public function wczytajLiczbeGraczy(): void{
            $liczbaGraczy = (int)readline();
            $this->liczbaGraczy = $liczbaGraczy;
        }

    }