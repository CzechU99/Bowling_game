<?php

    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;
    use Gui\GuiCli;
    use App\PrinterCli;
    use App\ObslugaIOCli;
    use App\GraWieloosobowa;

    class GuiCliTest extends TestCase{

        private int $liczbaGraczy;

        private GuiCli $guiCli;

        private GraWieloosobowa $graWieloosobowa;

        private PrinterCli $printerCli;

        private $printerCliMock;

        private $obslugaIOCliMock;

        protected function setUp(): void{

            $this->printerCliMock = $this->createMock(PrinterCli::class);
            $this->obslugaIOCliMock = $this->createMock(ObslugaIOCli::class);

            $this->obslugaIOCliMock
                ->expects($this->exactly(2))
                ->method("getLiczbaGraczy")
                ->willReturn(4);

            $this->graWieloosobowa = new GraWieloosobowa($this->obslugaIOCliMock->getLiczbaGraczy());
            $this->printerCli = new PrinterCli($this->graWieloosobowa);

            $this->guiCli = new GuiCli();

            $this->liczbaGraczy = $this->obslugaIOCliMock->getLiczbaGraczy();

        }

        #[DataProvider('odpowiedniWynikKazdegoGraczaPodKoniecGryProvider')]
        public function testOdpowiedniWynikKazdegoGraczaPodKoniecGry(int $liczbaRzutow, int $straconceKregle, int $liczbaPunktow){

            $this->obslugaIOCliMock
                ->expects($this->exactly($this->liczbaGraczy * $liczbaRzutow))
                ->method("getRzut")
                ->willReturn($straconceKregle);

            for($i = 0; $i < $this->liczbaGraczy * $liczbaRzutow; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
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

            $this->obslugaIOCliMock
                ->expects($this->exactly($this->liczbaGraczy * 2))
                ->method("getRzut")
                ->willReturn(4);

            $this->assertEquals(1, $this->graWieloosobowa->getNumerRundyGracza());

            for($i = 1; $i <= $this->liczbaGraczy; $i++){
                $this->assertEquals($i, $this->graWieloosobowa->getWskaznikGracza());
                for($j = 0; $j < 2; $j++){
                    $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
                }
            }

            $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());

        }

        public function testPoprawnyPrzebigGryDoOstatniejRundy(){

            $this->obslugaIOCliMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 18 * $this->liczbaGraczy; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            }

            $this->assertEquals(10, $this->graWieloosobowa->getNumerRundyGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());

        }

        #[DataProvider('poprawneZakonczenieGryGdyWszyscyGraczeWykonajaSwojeRzutyProvider')]
        public function testPoprawneZakonczenieGryGdyWszyscyGraczeWykonajaSwojeRzuty(int $liczbaRzutow, bool $expected){

            $this->obslugaIOCliMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < $liczbaRzutow * $this->liczbaGraczy; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
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
            
            $this->obslugaIOCliMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturnOnConsecutiveCalls(6, 8);

            $this->printerCliMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("SUMA ZBITYCH KREGLI W DWOCH RZUTACH SIE NIE ZGADZA - 14"));

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);

        }

        #[DataProvider('przejscieDoNastepnegoGraczaPoBonusowymRzucieProvider')]
        public function testPrzejscieDoNastepnegoGraczaPoBonusowymRzucie(int $liczbaRzutow, int $straconceKregle){

            $this->obslugaIOCliMock
                ->expects($this->exactly($liczbaRzutow * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < $liczbaRzutow * $this->liczbaGraczy; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            }

            $this->obslugaIOCliMock = $this->createMock(ObslugaIOCli::class);

            $this->obslugaIOCliMock
                ->expects($this->exactly(3))
                ->method("getRzut")
                ->willReturn($straconceKregle);
            
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);

            if($this->liczbaGraczy > 1){
                $this->assertEquals(2, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());
            }elseif($this->liczbaGraczy == 1){
                $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
                $this->assertEquals(3, $this->graWieloosobowa->getNumerRzutuGracza());
            }

        }

        public static function przejscieDoNastepnegoGraczaPoBonusowymRzucieProvider(){
            return array(
                array(18, 10),
                array(18, 5)
            );
        }

        public function testZmianeGraczaPoRzuceniuStrike(){

            $this->obslugaIOCliMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(10);

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);

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

            $this->obslugaIOCliMock
                ->expects($this->exactly(2 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 2 * $this->liczbaGraczy; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            }

            $this->assertEquals(2, $this->graWieloosobowa->getNumerRundyGracza());

        }

        public function testPrzejscieDoNastepnegoGracza(){

            $this->obslugaIOCliMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturn(4);

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCli);

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

            $this->obslugaIOCliMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(11);

            $this->printerCliMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("PODANO ZLA LICZBE KREGLI - 11"));

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);

        }

        public function testZgloszenieBleduJezeliLiczbaKregliJestMniejszaOdZera(){
            
            $this->obslugaIOCliMock
                ->expects($this->exactly(1))
                ->method("getRzut")
                ->willReturn(-3);

            $this->printerCliMock
                ->expects($this->once())
                ->method("printBladLogiczny")
                ->with($this->equalTo("PODANO ZLA LICZBE KREGLI - -3"));

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);

        }

        public function testBonusowyRzutDlaGraczaKtoryStracilWszystkieKregleWOstatniejRundzie(){
            
            $this->obslugaIOCliMock
                ->expects($this->exactly(18 * $this->liczbaGraczy))
                ->method("getRzut")
                ->willReturn(4);

            for($i = 0; $i < 18 * $this->liczbaGraczy; $i++){
                $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);
            }

            $this->assertEquals(1, $this->graWieloosobowa->getWskaznikGracza());
            $this->assertEquals(1, $this->graWieloosobowa->getNumerRzutuGracza());

            $this->obslugaIOCliMock = $this->createMock(ObslugaIOCli::class);
            
            $this->obslugaIOCliMock
                ->expects($this->exactly(2))
                ->method("getRzut")
                ->willReturn(10);

            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);
            $this->guiCli->obslugaRzutu($this->obslugaIOCliMock, $this->graWieloosobowa, $this->printerCliMock);

            $this->assertEquals(3, $this->graWieloosobowa->getNumerRzutuGracza());

        }
    
    }