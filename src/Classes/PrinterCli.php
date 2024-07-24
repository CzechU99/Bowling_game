<?php 

    namespace App;

    use App\GraWieloosobowa;

    class PrinterCli{

      private GraWieloosobowa $graWieloosobowa;

      public function __construct(GraWieloosobowa $gra = null){
        $gra ? $this->graWieloosobowa = $gra : null;
      }

      public function printInformacjeORundzie(): void{
        echo "\n";
        echo "---------------------------\n";
        echo "GRACZ -" . $this->graWieloosobowa->getWskaznikGracza() . "-\n";
        echo "---------------------------\n";
        foreach($this->graWieloosobowa->getInfoORundzie() as $info){
            echo $info . "-\n";
        }
      }

      public function printInformacjeOWyniku(): void{
          echo "Wynik: " . $this->graWieloosobowa->getInfoOWyniku() . "\n";
      }

      public function printBladLogiczny(string $blad): void{
            echo $blad . "\n";
      }

      public function printKtoWygral(): void{
          echo "---------------------------\n";
          $numerZwyciezcy = $this->graWieloosobowa->obliczKtoryGraczZwyciezyl();
          $ileZwyciezcow = count($numerZwyciezcy);

            if($ileZwyciezcow == 1){
                echo "ZWYCIEZA GRACZ -" . $numerZwyciezcy[0] + 1 . "-\n"; 
            }elseif($ileZwyciezcow > 1){
                $remis = "GRACZE";
                for($i = 0; $i < $ileZwyciezcow; $i++){
                    $remis .= " -" . $numerZwyciezcy[$i] + 1 . "-";
                }
                echo $remis . " ZREMISOWALI\n"; 
            }
      }

      public function printWynikiKazdejRundyGraczy(): void{
          echo "---------------------------\n";
          foreach($this->graWieloosobowa->getWynikiKazdejRundyGraczy() as $indexGracza => $rundyZPunktami){
              echo "RUNDY GRACZA -" . $indexGracza + 1 . "-\n";
              foreach($rundyZPunktami as $indexRundy => $punktyZRundy){
                  echo $indexRundy . ". " . $punktyZRundy . "\n";
              }
              echo "---------------------------\n";
          }
      }

      public function printZapytajOLiczbeGraczy(){
        echo "---------------------------\n";
        echo "PODAJ LICZBE GRACZY (1 - 6)\n";
      }

    }