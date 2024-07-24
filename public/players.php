<?php 

    use App\GuiHtml;
    use App\PrinterHtml;
    use App\ObslugaIOHtml;
    use App\StanGryManager;

    require(realpath(__DIR__ . "/../vendor/autoload.php"));

    session_start();

    $obslugaIOHtml = new ObslugaIOHtml();
    $stanGryManager = new StanGryManager(session_id());
    $guiHtml = new GuiHtml($obslugaIOHtml, $stanGryManager);

    $guiHtml->przekierujNaIndexHtml("liczbaGraczy");

    $graWieloosobowa = $stanGryManager->inicjalizacjaObiektuGraWieloosobowa(
        $obslugaIOHtml->getSessionVariable("liczbaGraczy")
    );

    $guiHtml->zresetujGre("resetGame");

    $printerHtml = new PrinterHtml($graWieloosobowa);

    $printerHtml->wyswietlHeadHtml();
    $printerHtml->wyswietlInfoORundzie();

    $guiHtml->obslugaRzutu(
        $obslugaIOHtml, 
        $graWieloosobowa, 
        $printerHtml
    );

    $graWieloosobowa->wylaczWskaznikGracza();

    $printerHtml->wyswietlGraczy(
        $obslugaIOHtml->getSessionVariable("liczbaGraczy")
    );

    $stanGryManager->zapiszStanGry($graWieloosobowa);

    $printerHtml->printCloseHtml();