<?php

    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;
    use App\GuiHtml;
    use App\PrinterHtml;
    use App\ObslugaIOHtml;
    use App\GraWieloosobowa;
    use App\StanGryManager;

    class GuiHtmlTest extends TestCase{

        private int $liczbaGraczy;

        private GuiHtml $guiHtml;

        private GraWieloosobowa $graWieloosobowa;

        private PrinterHtml $printerHtml;

        private $printerHtmlMock;
        private $stanGryManagerMock;
        private $obslugaIOHtmlMock;
        
        protected function setUp(): void{
            
            $this->printerHtmlMock = $this->createMock(PrinterHtml::class);
            $this->obslugaIOHtmlMock = $this->createMock(ObslugaIOHtml::class);
            $this->stanGryManagerMock = $this->createMock(StanGryManager::class);


            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("getLiczbaGraczy")
                ->willReturn(4);

            $this->guiHtml = new GuiHtml($this->obslugaIOHtmlMock, $this->stanGryManagerMock);
            $this->graWieloosobowa = new GraWieloosobowa($this->obslugaIOHtmlMock->getLiczbaGraczy());
            $this->printerHtml = new PrinterHtml($this->graWieloosobowa);

            $this->liczbaGraczy = $this->obslugaIOHtmlMock->getLiczbaGraczy();

        }

        #[DataProvider('odpowiedniWynikKazdegoGraczaPodKoniecGryProvider')]
        public function testOdpowiedniWynikKazdegoGraczaPodKoniecGry(int $liczbaRzutow, int $straconceKregle, int $liczbaPunktow){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($this->liczbaGraczy * $liczbaRzutow))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($this->liczbaGraczy * $liczbaRzutow))
                ->method("getRzut")
                ->willReturn($straconceKregle);

            for($i = 0; $i < $this->liczbaGraczy * $liczbaRzutow; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            }

            for($i = 0; $i < $this->liczbaGraczy; $i++){
                $this->assertEquals(
                    $liczbaPunktow, 
                    $this->graWieloosobowa->getStanyGryGraczy()[$i]->getGameScore()
                );
            }

        }

        public static function odpowiedniWynikKazdegoGraczaPodKoniecGryProvider(){
            return array(
                array(12, 10, 300),
                array(21, 5, 150),
                array(20, 4, 80),
                array(20, 0, 0)
            );
        }

        public function testPoprawnaZmianeGraczyWTrakcieRundy(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($this->liczbaGraczy * 2))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($this->liczbaGraczy * 2))
                ->method("getRzut")
                ->willReturn(4);

            $this->assertEquals(1, $this->graWieloosobowa->getNumerRundyGracza($this->graWieloosobowa->getStanyGryGraczy()));

            for($i = 1; $i <= $this->liczbaGraczy; $i++){
                $this->assertEquals($i, $this->graWieloosobowa->getWskaznikGracza());
                for($j = 0; $j < 2; $j++){
                    $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
                }
            }

            $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());

        }

        public function testPoprawnyPrzebigGryDoOstatniejRundy(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 18 * $this->liczbaGraczy; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            }

            $this->assertEquals(10, $this->graWieloosobowa->getNumerRundyGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());

        }

        #[DataProvider('poprawneZakonczenieGryGdyWszyscyGraczeWykonajaSwojeRzutyProvider')]
        public function testPoprawneZakonczenieGryGdyWszyscyGraczeWykonajaSwojeRzuty(int $liczbaRzutow, bool $expected){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < $liczbaRzutow * $this->liczbaGraczy; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            }

            $this->assertEquals(
                $expected, 
                $this->graWieloosobowa->czyGraNadalTrwa()
            );

        }

        public static function poprawneZakonczenieGryGdyWszyscyGraczeWykonajaSwojeRzutyProvider(){
            return array(
                array(19, true),
                array(20, false),
                array(21, false)
            );
        }

        // --------------------------------------------------
        
        public function testZgloszenieBleduGdyZbitoWiecejNizDziesiecKregliWDwochRzutach(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturnOnConsecutiveCalls(6, 8);

            $this->printerHtmlMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("SUMA ZBITYCH KREGLI W DWOCH RZUTACH SIE NIE ZGADZA - 14"));

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());

        }

        #[DataProvider('przejscieDoNastepnegoGraczaPoBonusowymRzucieProvider')]
        public function testPrzejscieDoNastepnegoGraczaPoBonusowymRzucie(int $liczbaRzutow, int $straconceKregle){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < $liczbaRzutow * $this->liczbaGraczy; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            }

            $this->obslugaIOHtmlMock = $this->createMock(ObslugaIOHtml::class);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(3))
                ->method("getRzut")
                ->willReturn($straconceKregle);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(3))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());

            if($this->liczbaGraczy > 1){
                $this->assertEquals(2, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());
            }elseif($this->liczbaGraczy == 1){
                $this->assertEquals(0, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());
            }

        }

        public static function przejscieDoNastepnegoGraczaPoBonusowymRzucieProvider(){
            return array(
                array(18, 10),
                array(18, 5)
            );
        }

        public function testZmianeGraczaPoRzuceniuStrike(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(10);

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());

            if($this->liczbaGraczy > 1){
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRundyGracza());
                $this->assertEquals(2, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());
            }elseif($this->liczbaGraczy == 1){
                $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());
            }

        }
        
        public function testPrzejscieDoNastepnejRundy(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2 * $this->liczbaGraczy))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 2 * $this->liczbaGraczy; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            }

            $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());

        }

        public function testPrzejscieDoNastepnegoGracza(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturn(4);

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtml, $this->graWieloosobowa->getStanyGryGraczy());

            if($this->liczbaGraczy > 1){
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRundyGracza());
                $this->assertEquals(2, $this->graWieloosobowa->getWskaznikGracza());
            }elseif($this->liczbaGraczy == 1){
                $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
            }

            $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());

        }

        public function testZgloszenieBleduJezeliLiczbaKregliJestWiekszaOdDziesieciu(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(11);

            $this->printerHtmlMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("PODANO ZLA LICZBE KREGLI - 11"));

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());

        }

        public function testZgloszenieBleduJezeliLiczbaKregliJestMniejszaOdZera(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->obslugaIOHtmlMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(-3);

            $this->printerHtmlMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("PODANO ZLA LICZBE KREGLI - -3"));

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());

        }

        public function testBonusowyRzutDlaGraczaKtoryStracilWszystkieKregleWOstatniejRundzie(){

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->obslugaIOHtmlMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 18 * $this->liczbaGraczy; $i++){
                $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());
            }

            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());

            $this->obslugaIOHtmlMock = $this->createMock(ObslugaIOHtml::class);

            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("isPostVariable")
                ->with("rollButton")
                ->willReturn(true);
            
            $this->obslugaIOHtmlMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturn(10);

            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());
            $this->guiHtml->obslugaRzutu($this->obslugaIOHtmlMock, $this->graWieloosobowa, $this->printerHtmlMock, $this->graWieloosobowa->getStanyGryGraczy());

            $this->assertEquals(3, $this->graWieloosobowa->getNumerRzutuGracza());

        }
    
    }