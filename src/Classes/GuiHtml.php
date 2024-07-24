<?php 

    namespace App;

    use App\ObslugaIOHtml;
    use App\StanGryManager;
    use App\GraWieloosobowa;
    use Exceptions\LogicException;

    class GuiHtml{

        private ObslugaIOHtml $obslugaIOHtml;
        private StanGryManager $stanGryManager;

        public function __construct(ObslugaIOHtml $obslugaIOHtml, StanGryManager $stanGryManager){
            $this->obslugaIOHtml = $obslugaIOHtml;
            $this->stanGryManager = $stanGryManager;
        }
        
        public function obslugaRzutu($obslugaIOHtml, $graWieloosobowa, $printerHtml){
            $obslugaIOHtml->wczytajRzut("rzutInput");

            if($obslugaIOHtml->isPostVariable("rollButton")){
                try{


                    $graWieloosobowa->obsluzRzut($obslugaIOHtml->getRzut());
                
                
                }catch(LogicException $logic){
                    $printerHtml->printBladLogiczny($logic->getMessage());
                }
            }
        }

        public function przekierujNaIndexHtml(string $liczbaGraczyKey): void{
            $liczbaGraczy = $this->obslugaIOHtml->getSessionVariable($liczbaGraczyKey);
            if($liczbaGraczy == 0 || !$this->obslugaIOHtml->isSessionVariable($liczbaGraczyKey)){
                header("Location: index.php");
            }
        }

        public function przekierujNaPlayersHtml(string $liczbaGraczyKey): void{
            if($this->obslugaIOHtml->isSessionVariable($liczbaGraczyKey)){
                $liczbaGraczy = $this->obslugaIOHtml->getSessionVariable($liczbaGraczyKey);
                if($liczbaGraczy > 0 && $liczbaGraczy <= GraWieloosobowa::$maxLiczbaGraczy){
                    header("Location: players.php");
                }
            }
        }

        public function zresetujGre($resetGamePostKey){
            if($this->obslugaIOHtml->isPostVariable($resetGamePostKey)){
                $this->stanGryManager->resetGry();
                header("Location: index.php");
            }
        }

    }