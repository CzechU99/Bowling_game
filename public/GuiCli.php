<?php

    namespace Gui;

    use App\PrinterCli;
    use App\ObslugaIOCli;
    use App\GraWieloosobowa;
    use Exceptions\LogicException;

    class GuiCli{

        public function obslugaRzutu($obslugaIOCli, $graWieloosobowa, $printerCli){

            $obslugaIOCli->wczytajRzut();
            try{
                $graWieloosobowa->obsluzRzut($obslugaIOCli->getRzut());
                $printerCli->printInformacjeOWyniku();
            }catch (LogicException $logic){
                $printerCli->printBladLogiczny($logic->getMessage());
            }

        }

    }

    require(realpath(__DIR__ . "/../vendor/autoload.php"));

    $guiCli = new GuiCli();

    $obslugaIOCli = new ObslugaIOCli();
    $printerCli = new PrinterCli();

    $printerCli->printZapytajOLiczbeGraczy();

    $obslugaIOCli->wczytajLiczbeGraczy();

    $graWieloosobowa = new GraWieloosobowa($obslugaIOCli->getLiczbaGraczy());
    $printerCli = new PrinterCli($graWieloosobowa);

    while($graWieloosobowa->czyGraNadalTrwa()){
        $printerCli->printInformacjeORundzie();
        $guiCli->obslugaRzutu($obslugaIOCli, $graWieloosobowa, $printerCli);
    }

    $printerCli->printKtoWygral();
    $printerCli->printWynikiKazdejRundyGraczy();