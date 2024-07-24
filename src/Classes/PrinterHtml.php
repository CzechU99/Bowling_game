<?php 

    namespace App;

    use App\GraWieloosobowa;

    class PrinterHtml{

        private GraWieloosobowa $graWieloosobowa;

        public function __construct(GraWieloosobowa $gra = null){
            $gra ? $this->graWieloosobowa = $gra : null;
        }

        public function wyswietlHeadHtml(){
            echo '<!DOCTYPE html>';
            echo '<html>';

            echo '<head>';
                echo '<meta charset="UTF-8" />';
                echo '<title>Kregle</title>';
            
                echo '<link rel="stylesheet" href="css/style.css">';
        
            echo '</head>';
        }

        public function wyswietlInfoORundzie(): void{
            echo '<body>';

                echo '<div id="inputy">';
                    echo '<h1>GRA W KREGLE</h1>';

                    echo '<form method="POST">';
                        echo '<input name="rzutInput"><br><br>';
                        $this->wyswietlPrzyciskRzutu($this->graWieloosobowa);
                        echo '<input type="submit" value="RESET" name="resetGame"><br><br>';
                    echo '</form>';
                echo '</div>';

                echo '<div id="wynik">';
                    $this->wyswietlKtoWygral($this->graWieloosobowa);
                echo '</div>';
        }

        public function printBladLogiczny(string $blad): void{
            echo '<div id="blad">';
                echo $blad;
            echo '</div>';
        }

        public function printGracz($numerGracza): void{
            echo '<div class="game">';
                echo '<div class="gracz" ' . $this->aktualizujStylRzucajacegoGracza($numerGracza + 1) . '>';
                    echo '---------------------------<br>';
                    echo "GRACZ " . $numerGracza + 1 . ":<br>";
                    $this->wyswietlKomunikatBleduGraczy($numerGracza);
                    echo '---------------------------<br>';
                echo '</div>';
            
                $this->wyswietlWynikKazdejRundy($numerGracza);
                $this->wyswietlStanyRzutowGracza($numerGracza);
            
            echo '</div>';
        }

        function printCloseHtml(): void{
            echo '</body>';
            echo '</html>';
        }

        private function wyswietlKtoWygral(): void{
            if(!$this->graWieloosobowa->czyGraNadalTrwa()){
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
        }

        public function wyswietlGraczy($liczbaGraczy){
            for($i = 0; $i < $liczbaGraczy; $i++){
                $this->printGracz($i);
            }
        }

        private function wyswietlPrzyciskRzutu(): void{
            if($this->graWieloosobowa->czyGraNadalTrwa()){
                echo "<input type='submit' value='RZUT' name='rollButton'>";
            }
        }

        private function wyswietlWynikKazdejRundy(int $ktoryGracz): void{
            foreach($this->graWieloosobowa->getWynikiKazdejRundyGraczy() as $index => $rundyZPunktami){
                if($index == $ktoryGracz){
                    foreach($rundyZPunktami as $indexRundy => $punktyZRundy){
                        echo $indexRundy . ". " . $punktyZRundy . "<br>";
                    }
                }
            }
        }

        private function aktualizujStylRzucajacegoGracza($ktoryGracz): string{
            if($this->graWieloosobowa->getWskaznikGracza() == $ktoryGracz) {
                return "style='background-color: lightgreen'";
            }
            return "";
        }

        private function wyswietlStanyRzutowGracza(int $ktoryGracz): void{
        
            foreach($this->graWieloosobowa->getStanyRzutowGraczy() as $index => $stanyRzutowGracza){
                if($index == $ktoryGracz && !empty($stanyRzutowGracza)){
                    for($i = array_key_last($stanyRzutowGracza); $i > 0; $i--){
                        echo "---------------------------<br>";
                        echo "Runda -" . $stanyRzutowGracza[$i]["frameNumber"] . "-<br>";
                        echo "Rzut -" . $stanyRzutowGracza[$i]["frameRoll"] . "-<br>";
                        echo "Stracone kregle: " . $stanyRzutowGracza[$i]["knockedPins"] . "<br>";
                        echo "Wynik: " . $stanyRzutowGracza[$i]["gameScore"] . "<br>";
                        echo "---------------------------<br>";
                    }
                }
            }

        }

        private function wyswietlKomunikatBleduGraczy($ktoryGracz): void{
            foreach($this->graWieloosobowa->getBledyGraczy() as $index => $bladGracza){
                if($index == $ktoryGracz && $bladGracza != ""){
                    echo $bladGracza . "<br>";
                }
            }
        }

        public function printIndexHead(){
            echo '<!DOCTYPE html>';
            echo '<html>';
                echo '<head>';
                    echo '<meta charset="UTF-8" />';
                    echo '<title>Kregle</title>';
                    echo '<link rel="stylesheet" href="css/style.css">';
                echo '</head>';
            echo '<body>';
        }

        public function printIndexHtml(){
            echo '<div id="inputy">';
                echo '<h1>GRA W KREGLE</h1>';
                echo '<form method="POST">';
                    echo 'PODAJ LICZBE GRACZY:<br>';
                    echo '<input type="number" name="liczbaGraczy" required min="1" max="'. GraWieloosobowa::$maxLiczbaGraczy .'"><br><br>';
                    echo '<input type="submit" value="ROZPOCZNIJ GRE" name="startGame"><br><br>';
                echo '</form>';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        }

    }
