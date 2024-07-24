<?php 

    namespace App;

    class ObslugaIOHtml{

        private int $rzut = 0; 
        private int $liczbaGraczy = 0;

        public function __construct(){}

        public function getRzut(): int{
            return $this->rzut;
        }

        public function getLiczbaGraczy(): int{
            return $this->liczbaGraczy;
        }

        public function getPostVariable(string $klucz): mixed{
            return filter_input(INPUT_POST, $klucz) ?? null;
        }

        public function isPostVariable(string $klucz): bool{
            return filter_input(INPUT_POST, $klucz) != null;
        }

        public function getSessionVariable(string $klucz): mixed{
            return $_SESSION[$klucz] ?? null;
        }
    
        public function isSessionVariable(string $klucz): bool {
            return isset($_SESSION[$klucz]);
        }

        public function setSessionVariable(string $klucz, mixed $value): void{
            $_SESSION[$klucz] = $value;
        }

        public function wczytajRzut(string $rzutInput): void{
            $rzut = (int)$this->getPostVariable($rzutInput);
            $this->rzut = $rzut;
        }

        public function wczytajLiczbeGraczy(string $liczbaGraczyKey): void{
            $gracze = (int)$this->getPostVariable($liczbaGraczyKey);
            $this->liczbaGraczy = $gracze;
            $this->setSessionVariable($liczbaGraczyKey, $gracze);
        }

    }
