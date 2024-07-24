<?php

    namespace App;

    class StanGryManager{

        private string $fileName;

        public function __construct(string $sessionId) {
            
            $this->fileName = "stanGryWieloosobowej_" . $sessionId;
        }

        public function inicjalizacjaObiektuGraWieloosobowa(int $liczbaGraczy){
            if(is_file("../src/GameStates/" . $this->fileName)){
                $graWieloosobowaState = file_get_contents('../src/GameStates/' . $this->fileName);
                $graWieloosobowa = unserialize($graWieloosobowaState);
                return $graWieloosobowa;
            }else{
                return new GraWieloosobowa($liczbaGraczy);
            }
        }
    
        public function zapiszStanGry(GraWieloosobowa $graWieloosobowa): void{
            $graWieloosobowaState = serialize($graWieloosobowa);
            file_put_contents("../src/GameStates/" . $this->fileName, $graWieloosobowaState);
        }

        public function usunStanGry(): void{
            if(is_file("../src/GameStates/" . $this->fileName)){
                unlink('../src/GameStates/' . $this->fileName);
            }
        }

        public function resetGry(): void{
            unlink('../src/GameStates/' . $this->fileName);
        }

     }